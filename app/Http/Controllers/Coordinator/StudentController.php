<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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
    public function index(): View
    {
        $students = User::role('student')
            ->withTrashed()
            ->with('studentProfile')
            ->withCount('applications')
            ->orderByRaw('deleted_at is not null')
            ->orderBy('name')
            ->get();

        return view('coordinator.students.index', compact('students'));
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

            return $student;
        });

        return redirect()
            ->route('coordinator.students.index')
            ->with('status', "Student \"{$student->name}\" created.");
    }

    public function edit(User $student): View
    {
        $student->load('studentProfile');

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
        });

        return redirect()
            ->route('coordinator.students.index')
            ->with('status', "Student \"{$student->name}\" updated.");
    }

    /**
     * Archives the account. The student can no longer sign in, but their
     * applications, reviews and payment records stay intact — including any
     * money already disbursed, which is the whole reason this is not a delete.
     */
    public function destroy(User $student): RedirectResponse
    {
        if ($student->is(Auth::user())) {
            return back()->withErrors(['student' => 'You cannot archive your own account here.']);
        }

        $student->delete();

        return redirect()
            ->route('coordinator.students.index')
            ->with('status', "Student \"{$student->name}\" archived.");
    }

    public function restore(int $student): RedirectResponse
    {
        $student = User::withTrashed()->findOrFail($student);
        $student->restore();

        return redirect()
            ->route('coordinator.students.index')
            ->with('status', "Student \"{$student->name}\" restored.");
    }
}
