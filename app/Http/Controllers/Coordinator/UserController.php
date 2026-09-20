<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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
        $users = User::role(['reviewer', 'coordinator'])->orderBy('name')->get();

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

        return redirect()->route('coordinator.users.index')->with('status', 'Account created.');
    }
}
