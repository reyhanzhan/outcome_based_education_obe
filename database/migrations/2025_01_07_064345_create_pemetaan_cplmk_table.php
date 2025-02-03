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
        Schema::create('pemetaan_cplmk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpl_id')->constrained('cpl')->onDelete('cascade'); // Relasi ke tabel CPMK
            $table->foreignId('mk_id')->constrained('mk')->onDelete('cascade');   // Relasi ke tabel CPL
            $table->boolean('checked')->default(false); // Status centang
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemetaan_cplmk');
    }
};
