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
        Schema::create('cpmk_cpl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpmk_id')->constrained('cpmk')->onDelete('cascade'); // Relasi ke CPMK
            $table->foreignId('cpl_id')->constrained('cpl')->onDelete('cascade');   // Relasi ke CPL
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpmk_cpl');
    }
};
