<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Coordinators (moderators) create reviewer/coordinator accounts here.
 * Students self-register instead — see RegisteredUserController.
 */
class UserController extends Controller
{
    public function index(): View
    {
        $users = User::role(['reviewer', 'coordinator'])
            ->withTrashed()
            ->withCount('programsManaged')
            ->orderByRaw('deleted_at is not null')
            ->orderBy('name')
            ->get();

        return view('coordinator.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('coordinator.users.create');
    }

    public function store(StoreStaffUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        $user->syncRoles($request->validated('roles'));

        return redirect()->route('coordinator.users.index')->with('status', __('Account created.'));
    }

    public function edit(User $user): View
    {
        abort_unless($user->hasAnyRole(['reviewer', 'coordinator']), 404);
        return view('coordinator.users.edit', ['staff' => $user]);
    }

    public function update(StoreStaffUserRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->hasAnyRole(['reviewer', 'coordinator']), 404);

        $roles = $request->validated('roles');

        // Dropping your own moderator role would lock you out of this page.
        if ($user->is(Auth::user()) && ! in_array('coordinator', $roles, true)) {
            return back()->withInput()->withErrors(['roles' => __('You cannot remove your own moderator role.')]);
        }

        // A scholarship needs a moderator, so one who still runs scholarships keeps the role.
        if (! in_array('coordinator', $roles, true) && $user->programsManaged()->exists()) {
            return back()->withInput()->withErrors(['roles' => __('This moderator still manages scholarships, so the moderator role cannot be removed.')]);
        }

        $user->update([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
        ]);

        // Blank means "leave the current password alone".
        if (filled($request->validated('password'))) {
            $user->update(['password' => Hash::make($request->validated('password'))]);
        }

        $user->syncRoles($roles);

        return redirect()->route('coordinator.users.index')->with('status', __('Account ":name" updated.', ['name' => $user->name]));
    }

    /**
     * Archives the account rather than deleting it: the reviews it wrote and
     * the scholarships it ran must keep pointing at someone.
     */
    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->hasAnyRole(['reviewer', 'coordinator']), 404);

        if ($user->is(Auth::user())) {
            return back()->withErrors(['user' => __('You cannot archive your own account here.')]);
        }

        if ($user->programsManaged()->exists()) {
            return back()->withErrors(['user' => __('":name" still manages scholarships, so the account cannot be archived.', ['name' => $user->name])]);
        }

        $user->delete();

        return redirect()->route('coordinator.users.index')->with('status', __('Account ":name" archived.', ['name' => $user->name]));
    }

    public function restore(int $user): RedirectResponse
    {
        $user = User::withTrashed()->role(['reviewer', 'coordinator'])->findOrFail($user);
        $user->restore();

        return redirect()->route('coordinator.users.index')->with('status', __('Account ":name" restored.', ['name' => $user->name]));
    }
}
