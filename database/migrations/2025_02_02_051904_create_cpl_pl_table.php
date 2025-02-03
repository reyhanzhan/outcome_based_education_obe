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
        Schema::create('cpl_pl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpl_id')->constrained('cpl')->onDelete('cascade'); // Relasi ke CPL
            $table->foreignId('pl_id')->constrained('pl')->onDelete('cascade');   // Relasi ke PL
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cpl_pl');
    }
};
