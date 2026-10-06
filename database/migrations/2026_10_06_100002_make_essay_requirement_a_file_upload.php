<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The essay used to be typed into the form; applicants now upload it as a
     * document like every other requirement. The seeder keeps the row right on
     * a fresh install; this brings an existing database along.
     */
    public function up(): void
    {
        DB::table('requirement_types')->where('slug', 'essay')->update([
            'kind' => 'file',
            'description' => 'Unggah esai Anda berisi alasan mendaftar dan rencana menggunakan beasiswa ini.',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('requirement_types')->where('slug', 'essay')->update([
            'kind' => 'text',
            'description' => 'Alasan Anda mendaftar dan rencana Anda menggunakan beasiswa ini.',
            'updated_at' => now(),
        ]);
    }
};
