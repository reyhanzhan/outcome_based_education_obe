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
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_prodi', 50);
            $table->foreign('kode_prodi')->references('kode_prodi')->on('program_studi')->onDelete('cascade');
            $table->unsignedBigInteger('kurikulum_id')->nullable();
            $table->year('tahun'); // Tambahkan kolom tahun
            $table->string('kode_mk', 255);
            $table->string('periode');
            $table->string('nip_dosen');
            $table->timestamps();

            $table->foreign('kurikulum_id')
                ->references('id')
                ->on('kurikulum')
                ->onDelete('cascade');

            $table->foreign('kode_mk')
                ->references('kode_mk')
                ->on('mk')
                ->onDelete('cascade');

            $table->foreign('nip_dosen')
                ->references('nip')
                ->on('users')
                ->onDelete('cascade');
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