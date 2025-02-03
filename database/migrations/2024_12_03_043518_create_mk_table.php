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
        Schema::create('mk', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('kode_mk');
            $table->string('deskripsi');
            $table->integer('sks');
            $table->enum('wptwp', ['TWP', 'WP'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mk');
    }
};
