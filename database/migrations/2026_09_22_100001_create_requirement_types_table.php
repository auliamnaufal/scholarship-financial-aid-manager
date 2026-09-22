<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The kinds of thing a scholarship can ask an applicant for. A lookup table
 * rather than a column per requirement on `programs`, so adding "portfolio"
 * later is a row, not a migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requirement_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            // 'file' is uploaded; 'text' is typed into the form.
            $table->string('kind')->default('file');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requirement_types');
    }
};
