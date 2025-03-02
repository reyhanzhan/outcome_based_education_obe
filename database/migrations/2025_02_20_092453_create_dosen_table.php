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
        Schema::create('dosen', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->nullable(false); // Nama dosen, wajib diisi
            $table->string('email', 100)->unique()->nullable(false); // Email unik, wajib diisi
            $table->string('role', 20)->default('dosen')->nullable(false); // Peran (role): 'kps' atau 'dosen'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen');
    }
};
