<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What the applicant actually supplied against each requirement: an uploaded
 * file for a 'file' requirement, or typed prose for a 'text' one. Exactly one
 * of the two is filled — see ApplicationDocument.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requirement_type_id')->constrained()->cascadeOnDelete();

            // Filled for a 'file' requirement. The path is on the private disk;
            // transcripts and recommendation letters are never served publicly.
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('size_bytes')->nullable();

            // Filled for a 'text' requirement, e.g. the essay.
            $table->text('body')->nullable();

            $table->timestamps();

            $table->unique(['application_id', 'requirement_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_documents');
    }
};
