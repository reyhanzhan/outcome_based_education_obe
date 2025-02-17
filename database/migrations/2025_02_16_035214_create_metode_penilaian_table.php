<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('metode_penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpmk_cpl_mk_id')->constrained('cpmk_cpl_mk')->onDelete('cascade');
            $table->boolean('partisipasi')->default(false);
            $table->boolean('observasi')->default(false);
            $table->boolean('unjuk_kerja')->default(false);
            $table->boolean('tes_tulis_uts')->default(false);
            $table->boolean('tes_tulis_uas')->default(false);
            $table->boolean('tes_lisan')->default(false);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metode_penilaian');
    }
};
