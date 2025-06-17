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
            $table->string('kode_prodi', 50);
            $table->foreign('kode_prodi')->references('kode_prodi')->on('program_studi')->onDelete('cascade');
            $table->unsignedBigInteger('tahun_kurikulum');
            $table->string('kode_mk', 255);
            $table->string('periode');
            $table->string('nip_dosen');
            $table->timestamps();

            $table->foreign('tahun_kurikulum')
                ->references('tahun')
                ->on('kurikulum')
                ->onDelete('cascade');

            $table->foreign('kode_mk')
                ->references('kode_mk')
                ->on('mk')
                ->onDelete('cascade');

            // Foreign Key ke dosen.nip
            $table->foreign('nip_dosen')
                ->references('nip')
                ->on('dosen')
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
