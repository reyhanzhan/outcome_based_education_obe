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
        Schema::create('cpmk_mk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpmk_id')->constrained('cpmk')->onDelete('cascade');
            $table->foreignId('mk_id')->constrained('mk')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpmk_mk');
    }
};
