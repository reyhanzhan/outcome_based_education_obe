<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_cpl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->foreignId('cpl_id')->constrained('cpl')->onDelete('cascade');
            $table->decimal('nilai', 5, 2)->default(0); // Nilai CPL (0-100 dengan 2 desimal)
            $table->timestamps();
            $table->unique(['mahasiswa_id', 'cpl_id']); // Pastikan kombinasi unik
            $table->string('kode_prodi', 50);
            $table->foreign('kode_prodi')->references('kode_prodi')->on('program_studi')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_cpl');
    }
};