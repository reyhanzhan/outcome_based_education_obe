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
            ['nip' => '0703129001', 'nama' => 'Ully Asfari', 'role' => 'dosen', 'email' => 'ully.asfari@example.com'],
            ['nip' => '0705028504', 'nama' => 'Mamik Usniyah Sari, S.Kom.,M.Kor', 'role' => 'dosen', 'email' => 'mamik.sari@example.com'],
            ['nip' => '0717107201', 'nama' => 'Bilal Luqman Bayasut', 'role' => 'dosen', 'email' => 'bilal.bayasut@example.com'],
            ['nip' => '0724067103', 'nama' => 'Alven Safik Ritonga, S.Si., M.Si.', 'role' => 'kps', 'email' => 'alven.ritonga@example.com'], // KPS
            ['nip' => '0731078504', 'nama' => 'M. Harist Murdani, S.Kom.,M.Sc.', 'role' => 'dosen', 'email' => 'harist.murdani@example.com'],
            ['nip' => '0709018901', 'nama' => 'Suryo Atmojo, S.Kom.,M.Kom.', 'role' => 'dosen', 'email' => 'suryo.atmojo@example.com'],
            ['nip' => '0716118803', 'nama' => 'Isnaini Muhandhis, M.Kom.', 'role' => 'dosen', 'email' => 'isnaini.muhandhis@example.com'],
            ['nip' => '1117048302', 'nama' => 'Suzana, M.Kom.', 'role' => 'dosen', 'email' => 'suzana.mkom@example.com'],
            ['nip' => '1117107201', 'nama' => 'Nurwahyudi, M.MT.', 'role' => 'dosen', 'email' => 'nurwahyudi.mmt@example.com'],
            ['nip' => '241105301', 'nama' => 'Anisa Nur Azizah, S. Mat., M. kom.', 'role' => 'dosen', 'email' => 'anisa.azizah@example.com'],
            ['nip' => '0712097301', 'nama' => 'Ronny Prasetyo, ST.,MT.', 'role' => 'dosen', 'email' => 'ronny.prasetyo@example.com'],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['nip' => $userData['nip']],
                [
                    'name' => $userData['nama'],
                    'email' => $userData['email'],
                    'password' => Hash::make('password'), // Password default: 'password'
                    'nip' => $userData['nip'],
                    'role' => $userData['role'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}