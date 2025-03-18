<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('krs', function (Blueprint $table) {
            $table->id();
            $table->string('periode');
            // 🔥 Foreign Key ke `program_studi.kode_prodi`
            $table->string('kode_prodi');
            // 🔥 Foreign Key ke `mk.kode_mk`
            $table->string('kode_mk');
            // 🔥 Foreign Key ke `kurikulum.tahun`
            $table->year('tahun'); // Pastikan tipe datanya sama dengan di tabel kurikulum
            // 🔥 Nama kelas
            $table->string('nama_kelas');
            // 🔥 Foreign Key ke `mahasiswa.nim`
            $table->string('nim');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('krs');
    }
};
