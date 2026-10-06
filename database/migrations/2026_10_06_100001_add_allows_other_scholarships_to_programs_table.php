<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            // Whether someone who receives this scholarship may also receive
            // another one. Most scholarships do not allow it, so the default is
            // false; an exclusive scholarship and a student's other awards
            // cannot coexist.
            $table->boolean('allows_other_scholarships')->default(false)->after('min_gpa');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('allows_other_scholarships');
        });
    }
};
