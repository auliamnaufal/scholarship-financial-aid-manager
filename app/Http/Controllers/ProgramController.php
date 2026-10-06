<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\Program;
use App\Models\User;
use App\Support\Eligibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\View\View;

/**
 * Public scholarship browsing (listing + detail). Open to guests; a signed-in
 * student additionally sees how well each scholarship fits their biodata.
 */
class ProgramController extends Controller
{
    /**
     * Slots in the visitor's grid. When the catalogue is bigger than this the
     * last slot becomes a "see more" card, so a visitor sees one fewer scholarship.
     */
    private const GUEST_SLOTS = 6;

    public function index(Request $request): View
    {
        $all = Program::query()
            ->where('application_deadline', '>=', Date::today())
            ->withCount('requirements')
            ->orderBy('application_deadline')
            ->get();

        // Visitors get a taste; the full catalogue is for signed-in users.
        $limited = $request->user() === null && $all->count() > self::GUEST_SLOTS;
        $programs = $limited ? $all->take(self::GUEST_SLOTS - 1) : $all;
        $hiddenCount = $all->count() - $programs->count();

        // The headline figures describe the whole catalogue either way.
        $stats = [
            'count' => $all->count(),
            'budget' => $all->sum('budget'),
            'closing' => $all->first()?->application_deadline,
            'featured' => $all->first(),
        ];

        $student = $this->student($request);

        $matches = $student
            ? $programs->mapWithKeys(fn (Program $program) => [
                $program->id => Eligibility::check(
                    $program,
                    $student->studentProfile,
                    $this->hasApplied($student, $program),
                ),
            ])
            : collect();

        $sources = $programs->pluck('funding_source')->unique()->sort()->values();

        return view('programs.index', compact('programs', 'matches', 'sources', 'stats', 'hiddenCount'));
    }

    public function show(Request $request, Program $program): View
    {
        $program->load('requirements.requirementType');

        $student = $this->student($request);

        $match = $student
            ? Eligibility::check($program, $student->studentProfile, $this->hasApplied($student, $program))
            : null;

        return view('programs.show', compact('program', 'match'));
    }

    /** The signed-in user, only if they are a student. */
    private function student(Request $request): ?User
    {
        $user = $request->user();

        return $user?->hasRole('student') ? $user->loadMissing('studentProfile') : null;
    }

    private function hasApplied(User $student, Program $program): bool
    {
        // A withdrawn application does not count: the student may apply again.
        return $student->applications->contains(
            fn ($application) => $application->program_id === $program->id
                && $application->status !== ApplicationStatus::Cancelled,
        );
    }
}
