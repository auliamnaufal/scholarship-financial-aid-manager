<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('semester');
            $table->date('submission_date');
            $table->string('status')->default('submitted');
            $table->timestamps();

            $table->unique(['student_id', 'program_id', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
