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
            $table->string('kode_prodi', 50);
            $table->foreign('kode_prodi')->references('kode_prodi')->on('program_studi')->onDelete('cascade');
            // 🔥 Foreign Key ke `mk.kode_mk`
            $table->string('kode_mk');
            // 🔥 Foreign Key ke `kurikulum.tahun`
            $table->year('tahun'); // Pastikan tipe datanya sama dengan di tabel kurikulum
            // 🔥 Nama kelas
            $table->string('nama_kelas');
            // 🔥 Foreign Key ke `mahasiswa.nim`
            $table->string('nim');
            $table->timestamps();

            // Foreign Key ke mk.kode_mk
        $table->foreign('kode_mk')
            ->references('kode_mk')
            ->on('mk')
            ->onDelete('cascade');

        // Foreign Key ke kurikulum.tahun
        $table->foreign('tahun')
            ->references('tahun')
            ->on('kurikulum')
            ->onDelete('cascade');

        // Foreign Key ke mahasiswa.nim
        $table->foreign('nim')
            ->references('nim')
            ->on('mahasiswa')
            ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('krs');
    }
};
