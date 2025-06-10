<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            ['nip' => '0703129001', 'nama' => 'Ully Asfari', 'role' => 'dosen', 'kode_prodi' => '1708'],
            ['nip' => '0705028504', 'nama' => 'Mamik Usniyah Sari, S.Kom.,M.Kor', 'role' => 'dosen' ,'kode_prodi' => '1708'],
            ['nip' => '0717107201', 'nama' => 'Bilal Luqman Bayasut', 'role' => 'dosen' ,'kode_prodi' => '1708'],
            ['nip' => '0724067103', 'nama' => 'Alven Safik Ritonga, S.Si., M.Si.', 'role' => 'kps', 'kode_prodi' => '1708'], // KPS
            ['nip' => '0731078504', 'nama' => 'M. Harist Murdani, S.Kom.,M.Sc.', 'role' => 'dosen', 'kode_prodi' => '1708'],
            ['nip' => '0709018901', 'nama' => 'Suryo Atmojo, S.Kom.,M.Kom.', 'role' => 'dosen', 'kode_prodi' => '1708'],
            ['nip' => '0716118803', 'nama' => 'Isnaini Muhandhis, M.Kom.', 'role' => 'dosen', 'kode_prodi' => '1708'],
            ['nip' => '1117048302', 'nama' => 'Suzana, M.Kom.', 'role' => 'dosen', 'kode_prodi' => '1708'],
            ['nip' => '1117107201', 'nama' => 'Nurwahyudi, M.MT.', 'role' => 'dosen' ,'kode_prodi' => '1708'],
            ['nip' => '241105301', 'nama' => 'Anisa Nur Azizah, S. Mat., M. kom.', 'role' => 'dosen', 'kode_prodi' => '1708'],
            ['nip' => '0712097301', 'nama' => 'Ronny Prasetyo, ST.,MT.', 'role' => 'dosen', 'kode_prodi' => '1708'],
            ['nip' => '123', 'nama' => 'dosen industri', 'role' => 'kps', 'kode_prodi' => '1707'],
            ['nip' => '456', 'nama' => 'dosen mesin', 'role' => 'kps', 'kode_prodi' => '1706'],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['nip' => $userData['nip']],
                [
                    'name' => $userData['nama'],
                    'password' => Hash::make('password'), // Password default: 'password'
                    'nip' => $userData['nip'],
                    'role' => $userData['role'],
                    'kode_prodi' => $userData['kode_prodi'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}