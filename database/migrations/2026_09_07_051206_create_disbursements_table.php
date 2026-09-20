<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->integer('seq_no');
            $table->decimal('amount', 12, 2);
            $table->date('disbursement_date');
            $table->string('semester');
            $table->timestamps();

            $table->unique(['application_id', 'seq_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disbursements');
    }
};
