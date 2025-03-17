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
        Schema::create('mk_dosen', function (Blueprint $table) {
            $table->id();
            // $table->unsignedBigInteger('dosen_id');
            // $table->unsignedBigInteger('mk_id');
            // $table->timestamps();
        
            // $table->foreign('dosen_id')->references('id')->on('dosen')->onDelete('cascade');
            // $table->foreign('mk_id')->references('id')->on('mk')->onDelete('cascade');
            // $table->unique(['dosen_id', 'mk_id']); // Pastikan kombinasi unik
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mk_dosen');
    }
};
