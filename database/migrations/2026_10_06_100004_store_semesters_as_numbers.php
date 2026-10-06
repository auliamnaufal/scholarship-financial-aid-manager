<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Semesters used to be typed as "2026-1" (a year and a term). They are now
     * the student's own semester number, so old rows are converted: the number
     * of terms since the student enrolled, counting the enrolment term as 1.
     */
    public function up(): void
    {
        $convert = function (string $year, string $term, ?int $enrolled): string {
            if (! $enrolled) {
                return $term === '1' ? '1' : '2';
            }

            return (string) max(1, ((int) $year - $enrolled) * 2 + (int) $term);
        };

        $applications = DB::table('applications')
            ->leftJoin('student_profiles', 'student_profiles.user_id', '=', 'applications.student_id')
            ->whereRaw("applications.semester like '____-_'")
            ->get(['applications.id', 'applications.semester', 'student_profiles.year_enrolled']);

        foreach ($applications as $row) {
            [$year, $term] = explode('-', $row->semester);

            DB::table('applications')->where('id', $row->id)
                ->update(['semester' => $convert($year, $term, $row->year_enrolled ? (int) $row->year_enrolled : null)]);
        }

        // A payment is for the same semester its application was for, unless it names another.
        $disbursements = DB::table('disbursements')
            ->join('applications', 'applications.id', '=', 'disbursements.application_id')
            ->leftJoin('student_profiles', 'student_profiles.user_id', '=', 'applications.student_id')
            ->whereRaw("disbursements.semester like '____-_'")
            ->get(['disbursements.id', 'disbursements.semester', 'student_profiles.year_enrolled']);

        foreach ($disbursements as $row) {
            [$year, $term] = explode('-', $row->semester);

            DB::table('disbursements')->where('id', $row->id)
                ->update(['semester' => $convert($year, $term, $row->year_enrolled ? (int) $row->year_enrolled : null)]);
        }
    }

    public function down(): void
    {
        // The year cannot be recovered from a plain semester number.
    }
};
