<?php

namespace App\Support;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * What stops a student from applying to a scholarship, beyond the biodata
 * thresholds in Eligibility.
 *
 * Two rules:
 *  - at most MAX_OPEN applications may be waiting for a decision at once,
 *    counted across every scholarship; a decision (approved or rejected) or a
 *    withdrawal frees a place;
 *  - each scholarship says whether its recipients may also receive another one.
 *    A scholarship that does not allow it cannot be held alongside any other.
 *
 * Built once per student so a listing of many scholarships asks the database
 * two questions, not two per scholarship.
 */
class ApplyRules
{
    public const MAX_OPEN = 2;

    /** @param Collection<int, Application> $approved the student's approved applications, with their programme */
    private function __construct(private int $open, private Collection $approved)
    {
    }

    public static function for(User $student): self
    {
        $applications = $student->applications()->with('program')->get();

        return new self(
            $applications->filter(fn (Application $application) => $application->status->isOpen())->count(),
            $applications->filter(fn (Application $application) => $application->status === ApplicationStatus::Approved),
        );
    }

    /** Why the student cannot apply to this scholarship right now, or null if they can. */
    public function blockedReasonFor(Program $program): ?string
    {
        if ($this->open >= self::MAX_OPEN) {
            return 'Anda sudah memiliki '.self::MAX_OPEN.' pendaftaran yang sedang diproses. Tunggu hasilnya sebelum mendaftar ke beasiswa lain.';
        }

        // Applying to the scholarship already held is not a conflict with itself.
        $others = $this->approved->reject(fn (Application $application) => $application->program_id === $program->id);

        if ($held = $others->first(fn (Application $application) => ! $application->program->allows_other_scholarships)) {
            return "Anda sudah menerima {$held->program->name}, yang tidak membolehkan penerimanya menerima beasiswa lain.";
        }

        if (! $program->allows_other_scholarships && $others->isNotEmpty()) {
            return "Beasiswa ini tidak membolehkan penerimanya menerima beasiswa lain, sedangkan Anda sudah menerima {$others->first()->program->name}.";
        }

        return null;
    }

    /**
     * Why this application cannot be approved, or null if it can: approving
     * it must not leave the student holding awards that exclude each other.
     */
    public static function approvalConflict(Application $application): ?string
    {
        $others = Application::query()
            ->where('student_id', $application->student_id)
            ->where('id', '!=', $application->id)
            ->where('status', ApplicationStatus::Approved->value)
            ->with('program')
            ->get();

        if ($held = $others->first(fn (Application $other) => ! $other->program->allows_other_scholarships)) {
            return "Mahasiswa ini sudah menerima {$held->program->name}, yang tidak membolehkan penerimanya menerima beasiswa lain.";
        }

        if (! $application->program->allows_other_scholarships && $others->isNotEmpty()) {
            return "Beasiswa ini tidak membolehkan penerimanya menerima beasiswa lain, sedangkan mahasiswa ini sudah menerima {$others->first()->program->name}.";
        }

        return null;
    }
}
