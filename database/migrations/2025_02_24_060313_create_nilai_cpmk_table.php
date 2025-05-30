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
        Schema::create('nilai_cpmk', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mahasiswa_id');
            $table->unsignedBigInteger('mk_id');
            $table->unsignedBigInteger('cpmk_id');
            $table->integer('penilaian_ke')->default(1); // Sudah ada default value
            $table->decimal('nilai', 5, 2)->default(0); // Nilai CPMK (0-100)
            $table->timestamps();
        
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswa')->onDelete('cascade');
            $table->foreign('mk_id')->references('id')->on('mk')->onDelete('cascade');
            $table->foreign('cpmk_id')->references('id')->on('cpmk')->onDelete('cascade');
            $table->string('kode_prodi', 50);
            $table->foreign('kode_prodi')->references('kode_prodi')->on('program_studi')->onDelete('cascade');
            // Perbarui constraint unik untuk menyertakan penilaian_ke
            $table->unique(['mahasiswa_id', 'mk_id', 'cpmk_id', 'penilaian_ke'], 'nilai_cpmk_unique_with_penilaian_ke');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_cpmk');
    }
};