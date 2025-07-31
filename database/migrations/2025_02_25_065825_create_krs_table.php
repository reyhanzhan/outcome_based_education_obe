<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('krs', function (Blueprint $table) {
        $table->id();
        $table->string('periode');
        // Foreign Key ke `program_studi.kode_prodi`
        $table->string('kode_prodi', 50);
        $table->foreign('kode_prodi')->references('kode_prodi')->on('program_studi')->onDelete('cascade');
        // Foreign Key ke `mk.kode_mk`
        $table->string('kode_mk');
        $table->foreign('kode_mk')->references('kode_mk')->on('mk')->onDelete('cascade');
        // Tahun sebagai kolom biasa (bukan foreign key)
        $table->year('tahun'); // Sesuaikan tipe data dengan tabel kurikulum jika berbeda
        // Nama kelas
        $table->string('nama_kelas');
        // Foreign Key ke `mahasiswa.nim`
        $table->string('nim');
        $table->foreign('nim')->references('nim')->on('mahasiswa')->onDelete('cascade');
        $table->timestamps();
        // Foreign Key ke `kurikulum.id`
        $table->unsignedBigInteger('kurikulum_id')->nullable();
        $table->foreign('kurikulum_id')->references('id')->on('kurikulum')->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('krs');
    }
};
