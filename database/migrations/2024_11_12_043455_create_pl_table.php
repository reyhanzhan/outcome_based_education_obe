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
         Schema::create('pl', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('kode_pl');
            $table->string('deskripsi');
            $table->string('kategori');
            $table->string('kode_prodi', 50);
            $table->foreign('kode_prodi')->references('kode_prodi')->on('program_studi')->onDelete('cascade');
            $table->unsignedBigInteger('kurikulum_id')->nullable();
            $table->foreign('kurikulum_id')->references('id')->on('kurikulum')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pl');
    }
};
