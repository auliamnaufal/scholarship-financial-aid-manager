<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Coordinators manage student accounts and their biodata here. Students also
 * self-register and maintain their own profile; this is the administrative
 * side of the same two records.
 */
class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $show = $request->string('show')->toString();

        $students = User::role('student')
            ->withTrashed()
            ->when($show === 'active', fn ($query) => $query->whereNull('deleted_at'))
            ->when($show === 'archived', fn ($query) => $query->whereNotNull('deleted_at'))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.trim($request->string('q')).'%';

                $query->where(fn ($q) => $q
                    ->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhereHas('studentProfile', fn ($p) => $p->where('nim', 'like', $term)));
            })
            ->with('studentProfile')
            ->withCount('applications')
            ->orderByRaw('deleted_at is not null')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('coordinator.students.index', compact('students', 'show'));
    }

    public function create(): View
    {
        return view('coordinator.students.create');
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        $student = DB::transaction(function () use ($request) {
            $student = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => Hash::make($request->validated('password')),
            ]);

            $student->assignRole('student');
            $student->studentProfile()->create($request->profileAttributes());
            $student->syncGuardianPhones($request->validated('guardian_phones') ?? []);

            return $student;
        });

        return redirect()
            ->route('coordinator.students.index')
            ->with('status', __('Student ":name" created.', ['name' => $student->name]));
    }

    public function edit(User $student): View
    {
        $student->load(['studentProfile', 'guardianPhones']);

        return view('coordinator.students.edit', compact('student'));
    }

    public function update(StudentRequest $request, User $student): RedirectResponse
    {
        DB::transaction(function () use ($request, $student) {
            $student->update([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
            ]);

            // Blank means "leave the current password alone".
            if (filled($request->validated('password'))) {
                $student->update(['password' => Hash::make($request->validated('password'))]);
            }

            $student->studentProfile()->updateOrCreate(
                ['user_id' => $student->id],
                $request->profileAttributes(),
            );
            $student->syncGuardianPhones($request->validated('guardian_phones') ?? []);
        });

        return redirect()
            ->route('coordinator.students.index')
            ->with('status', __('Student ":name" updated.', ['name' => $student->name]));
    }

    /**
     * Archives the account. The student can no longer sign in, but their
     * applications, reviews and payment records stay intact — including any
     * money already disbursed, which is the whole reason this is not a delete.
     */
    public function destroy(User $student): RedirectResponse
    {
        if ($student->is(Auth::user())) {
            return back()->withErrors(['student' => __('You cannot archive your own account here.')]);
        }

        $student->delete();

        return redirect()
            ->route('coordinator.students.index')
            ->with('status', __('Student ":name" archived.', ['name' => $student->name]));
    }

    public function restore(int $student): RedirectResponse
    {
        $student = User::withTrashed()->findOrFail($student);
        $student->restore();

        return redirect()
            ->route('coordinator.students.index')
            ->with('status', __('Student ":name" restored.', ['name' => $student->name]));
    }
}
