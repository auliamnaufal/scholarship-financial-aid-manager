<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('funding_source');
            $table->decimal('budget', 12, 2);
            $table->date('application_deadline');
            $table->decimal('max_family_income', 12, 2)->nullable();
            $table->decimal('min_gpa', 3, 2)->nullable();
            $table->foreignId('coordinator_id')->constrained('users')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
