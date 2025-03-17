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
        Schema::create('kurikulum', function (Blueprint $table) {
            $table->id();
            $table->year('tahun'); // Kolom tahun (format YYYY)
            $table->string('kode_prodi', 50);
            $table->string('kode_mk', 255);
            $table->integer('semester')->nullable();
            $table->timestamps();
        
            // Foreign Key ke program_studi
            $table->foreign('kode_prodi')
                ->references('kode_prodi')
                ->on('program_studi')
                ->onDelete('cascade');
        
            // Foreign Key ke mk (mata kuliah)
            $table->foreign('kode_mk')
                ->references('kode_mk')
                ->on('mk')
                ->onDelete('cascade');
        
            // 🔥 Pastikan kombinasi tahun, prodi, dan mk unik
            $table->unique(['tahun', 'kode_prodi', 'kode_mk']);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kurikulum');
    }
};
