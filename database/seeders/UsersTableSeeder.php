<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Alven Safik Ritonga',
            'email' => 'kps@gmail.com',
            'password' => bcrypt('1234567'), // Pastikan password di-hash
            'jabatan' => 'kps',
            'prodi' => 'Teknik Informatika'
        ]);
    }
}
