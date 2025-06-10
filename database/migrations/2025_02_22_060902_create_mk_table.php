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
        Schema::create('mk', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mk');
            $table->string('deskripsi');
            $table->integer('sks');
            $table->string('jenis_mk');
            $table->string('kode_prodi', 50)->nullable();
            $table->decimal('nilai_lulus', 20, 6)->nullable();
        
            // Foreign key ke tabel program_studi dengan eksplisit karakter set
            $table->foreign('kode_prodi')
                  ->references('kode_prodi')
                  ->on('program_studi')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        
            // Tambahkan constraint unik untuk kombinasi kode_mk dan kode_prodi
            $table->unique(['kode_mk', 'kode_prodi'], 'mk_kode_mk_kode_prodi_unique');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mk');
    }
};