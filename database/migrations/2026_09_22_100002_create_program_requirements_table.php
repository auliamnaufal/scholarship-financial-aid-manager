<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What a given scholarship asks for. Not every scholarship wants a
 * recommendation letter, so the coordinator picks per programme and says
 * whether each one is compulsory.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requirement_type_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_required')->default(true);
            $table->text('instructions')->nullable();
            $table->timestamps();

            $table->unique(['program_id', 'requirement_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_requirements');
    }
};
