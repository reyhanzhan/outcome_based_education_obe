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
        Schema::create('teknik_penilaian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mk_id');
            $table->unsignedBigInteger('cpmk_id');
            $table->unsignedBigInteger('kurikulum_id'); // Kolom kurikulum_id non-nullable
            $table->string('teknik');
            $table->decimal('bobot', 5, 2); // Bobot dalam persen (misalnya: 5.00)
            $table->timestamps();

            // Foreign keys
            $table->foreign('mk_id')->references('id')->on('mk')->onDelete('cascade');
            $table->foreign('cpmk_id')->references('id')->on('cpmk')->onDelete('cascade');
            $table->foreign('kurikulum_id')->references('id')->on('kurikulum')->onDelete('cascade');

            // Constraint unik
            $table->unique(['mk_id', 'cpmk_id', 'teknik', 'kurikulum_id'], 'unique_teknik_per_cpmk_kurikulum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teknik_penilaian');
    }
};