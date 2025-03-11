<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dosen', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 20)->unique()->nullable(false); // Nomor Induk Pegawai (NIP)
            $table->string('nama', 150)->nullable(false); // Nama lengkap
            $table->string('nidn', 20)->unique()->nullable()->default(null); // Nomor Induk Dosen Nasional (NIDN)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen');
    }
};
