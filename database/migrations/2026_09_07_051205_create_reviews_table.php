<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->integer('score');
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->unique(['reviewer_id', 'application_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
