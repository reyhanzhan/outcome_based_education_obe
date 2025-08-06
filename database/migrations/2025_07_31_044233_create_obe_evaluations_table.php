<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObeEvaluationsTable extends Migration
{
    public function up()
    {
        Schema::create('obe_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->foreignId('mk_id')->constrained('mk')->onDelete('cascade');
            $table->json('nilai_cpmk')->nullable();
            $table->decimal('total_score', 5, 2);
            $table->string('periode');
            $table->string('tahun');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('obe_evaluations');
    }
}
;