<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProgramStudiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programStudi = [
            [
                'kode_prodi' => '1708',
                'nama_prodi' => 'Teknik Informatika',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_prodi' => '1707',
                'nama_prodi' => 'Teknik Industri',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_prodi' => '1706',
                'nama_prodi' => 'Teknik Mesin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('program_studi')->insert($programStudi);
    }
}