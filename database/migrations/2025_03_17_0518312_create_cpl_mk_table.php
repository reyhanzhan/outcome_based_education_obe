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
        Schema::create('cpl_mk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpl_id')->constrained('cpl')->onDelete('cascade'); // Relasi ke CPL
            $table->foreignId('mk_id')->constrained('mk')->onDelete('cascade');   // Relasi ke MK
            $table->integer('bobot')->default(0); // Bobot CPL-MK, total harus 100% per CPL
            $table->timestamps();
            $table->unsignedBigInteger('kurikulum_id')->nullable();
            $table->foreign('kurikulum_id')->references('id')->on('kurikulum')->onDelete('set null');
            $table->unique(['cpl_id', 'mk_id', 'kurikulum_id'], 'cpl_mk_cpl_id_mk_id_kurikulum_id_unique'); // Constraint unik yang mencakup kurikulum_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpl_mk');
    }
};