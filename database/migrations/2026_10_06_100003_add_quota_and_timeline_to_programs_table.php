<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            // How many people the budget is meant for. Every recipient gets the
            // same share: budget / quota.
            $table->unsignedInteger('quota')->default(10)->after('budget');

            // The timeline: registration closes on application_deadline, review
            // runs until review_deadline, and recipients are announced on
            // announcement_date.
            $table->date('review_deadline')->nullable()->after('application_deadline');
            $table->date('announcement_date')->nullable()->after('review_deadline');
        });

        // Programmes that already exist get a timeline following their deadline:
        // two weeks of review, then the announcement a week later.
        DB::table('programs')->orderBy('id')->each(function ($program) {
            $reviewEnds = Carbon::parse($program->application_deadline)->addDays(14);

            DB::table('programs')->where('id', $program->id)->update([
                'review_deadline' => $reviewEnds->toDateString(),
                'announcement_date' => $reviewEnds->copy()->addDays(7)->toDateString(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['quota', 'review_deadline', 'announcement_date']);
        });
    }
};
