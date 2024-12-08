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
            $table->foreignId('pl_id')->constrained('pl')->onDelete('cascade');   // Relasi ke tabel PL
            $table->boolean('checked')->default(false); // Status centang
            $table->timestamps();
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
