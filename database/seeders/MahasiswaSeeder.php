<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Mahasiswa;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Mahasiswa::create([
            'nama' => 'Mahasiswa 1',
            'nim' => 'MHS001',
        ]);

        Mahasiswa::create([
            'nama' => 'Mahasiswa 2',
            'nim' => 'MHS002',
        ]);
    }
}
