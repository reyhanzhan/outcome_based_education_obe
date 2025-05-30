<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('teknik_penilaian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mk_id');
            $table->unsignedBigInteger('cpmk_id');
            $table->string('teknik');
            $table->decimal('bobot', 5, 2); // Bobot dalam persen (misalnya: 5.00)
            $table->timestamps();
            $table->string('kode_prodi', 50);
            $table->foreign('kode_prodi')->references('kode_prodi')->on('program_studi')->onDelete('cascade');

            // Foreign keys
            $table->foreign('mk_id')->references('id')->on('mk')->onDelete('cascade');
            $table->foreign('cpmk_id')->references('id')->on('cpmk')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teknik_penilaian');
    }
};
