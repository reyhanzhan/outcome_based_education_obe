<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('periode_masuk')->index(); // 🔥 Pastikan kolom ini di-index

            // Foreign key ke program_studi
            $table->string('kode_prodi');
            $table->foreign('kode_prodi')->references('kode_prodi')->on('program_studi')->onDelete('cascade');

            $table->string('nim', 20)->unique()->nullable(false); // Nomor Induk Mahasiswa
            $table->string('nama', 100)->nullable(false);

            // Sistem Kuliah: Reguler Pagi atau Reguler Sore
            $table->enum('sistem_kuliah', ['Reguler Pagi', 'Reguler Sore']);

            // Jalur Penerimaan: SNMPTN, SBMPTN, Mandiri, dll.
            $table->string('jalur_penerimaan');

            // Gelombang Daftar
            $table->string('gelombang_daftar');

            // Agama
            $table->string('agama');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
