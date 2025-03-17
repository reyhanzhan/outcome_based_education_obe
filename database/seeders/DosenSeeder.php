<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dosen;
use App\Models\Mk;

class DosenSeeder extends Seeder
{
    public function run()
    {

        // Tambahkan Dosen
        $dosen1 = Dosen::create([
            'nama' => 'Dosen 1',
            'email' => 'dosen1@example.com',
            'role' => 'dosen',
            'nip' => 'DOS001',
            'telepon' => '08123456780',
            'password' => bcrypt('password123'), // Ganti dengan password yang aman
        ]);

        $dosen2 = Dosen::create([
            'nama' => 'Dosen 2',
            'email' => 'dosen2@example.com',
            'role' => 'dosen',
            // 'nip' => 'DOS002',
            // 'telepon' => '08123456781',
            // 'password' => bcrypt('password123'), // Ganti dengan password yang aman
        ]);

        // Tambahkan penugasan MK untuk Dosen (via tabel mk_dosen)
        $mk1 = Mk::where('kode_mk', 'MK04')->first(); // Asumsi MK04 ada
        $mk2 = Mk::where('kode_mk', 'MK34')->first(); // Asumsi MK34 ada

        if ($mk1 && $dosen1) {
            $dosen1->mks()->attach($mk1->id); // Dosen 1 mengajar MK04
        }

        if ($mk2 && $dosen2) {
            $dosen2->mks()->attach($mk2->id); // Dosen 2 mengajar MK34
        }
    }
}