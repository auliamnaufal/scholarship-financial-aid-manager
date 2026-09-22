<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Biodata the student maintains themselves.
 *
 * `family_income` closes a real hole: `programs.max_family_income` already
 * existed but there was nothing on the student to compare it against, so
 * need-based eligibility could not be checked at all.
 *
 * Columns are nullable because the profiles already in the database predate
 * them, and because a student fills this in over time rather than at once.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('nim')->nullable()->unique()->after('user_id');
            $table->string('faculty')->nullable()->after('nim');
            $table->string('study_program')->nullable()->after('faculty');

            $table->string('phone')->nullable()->after('year_enrolled');
            $table->text('address')->nullable()->after('phone');

            $table->decimal('family_income', 12, 2)->nullable()->after('address');
            $table->string('parent_occupation')->nullable()->after('family_income');
            $table->unsignedSmallInteger('dependents_count')->nullable()->after('parent_occupation');

            $table->string('bank_name')->nullable()->after('dependents_count');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_holder')->nullable()->after('bank_account_number');
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropUnique(['nim']);

            $table->dropColumn([
                'nim',
                'faculty',
                'study_program',
                'phone',
                'address',
                'family_income',
                'parent_occupation',
                'dependents_count',
                'bank_name',
                'bank_account_number',
                'bank_account_holder',
            ]);
        });
    }
};
