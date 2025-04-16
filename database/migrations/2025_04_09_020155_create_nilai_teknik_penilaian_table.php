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
        Schema::create('nilai_teknik_penilaian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mahasiswa_id');
            $table->unsignedBigInteger('mk_id');
            $table->unsignedBigInteger('cpmk_id');
            $table->string('teknik'); // Nama teknik: Kehadiran, Kuis, dll.
            $table->float('nilai', 5, 2); // Nilai teknik (0-100)
            $table->timestamps();

            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswa')->onDelete('cascade');
            $table->foreign('mk_id')->references('id')->on('mk')->onDelete('cascade');
            $table->foreign('cpmk_id')->references('id')->on('cpmk')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_teknik_penilaian');
    }
};
