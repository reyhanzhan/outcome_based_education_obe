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
            $table->string('kode_mk')->unique();
            $table->string('deskripsi');
            $table->integer('sks');
            $table->string('jenis_mk');
            $table->string('kode_prodi', 50)->nullable(); // Sesuai panjang di tabel program_studi
            $table->decimal('nilai_lulus', 20, 6)->nullable(); // Sesuai format di database
        
            // Foreign key ke tabel program_studi
            $table->foreign('kode_prodi')->references('kode_prodi')->on('program_studi')->onDelete('set null');
        
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
