<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();

            // Perbaikan: gunakan kode_prodi sebagai foreign key, bukan nama_prodi
            $table->string('kode_prodi', 50);
            $table->foreign('kode_prodi')->references('kode_prodi')->on('program_studi')->onDelete('cascade');

            $table->unsignedBigInteger('tahun_kurikulum');
            

            $table->string('kode_mk', 255);
            
            $table->string('periode');
            $table->string('dosen');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
