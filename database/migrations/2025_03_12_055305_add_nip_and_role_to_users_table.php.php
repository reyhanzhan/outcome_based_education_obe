<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Schema::table('users', function (Blueprint $table) {
        //     $table->string('nip')->unique()->nullable()->after('email');
        //     $table->enum('role', ['dosen', 'kps'])->default('dosen')->after('nip');
        // });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'role']);
        });
    }
};