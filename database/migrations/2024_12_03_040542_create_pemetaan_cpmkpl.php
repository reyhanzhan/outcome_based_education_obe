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
        Schema::create('pemetaan_cpmkpl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpmk_id')->constrained('cpmk')->onDelete('cascade'); // Relasi ke tabel CPMK
            $table->foreignId('cpl_id')->constrained('cpl')->onDelete('cascade');   // Relasi ke tabel CPL
            $table->boolean('checked')->default(false); // Status centang
            $table->timestamps();


            $table->foreign('cpmk_id')->references('id')->on('cpmk')->onDelete('cascade');
            $table->foreign('cpl_id')->references('id')->on('cpl')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemetaan_cpmkpl');
    }
};
