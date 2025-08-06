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
            ['nip' => '151105301', 'nama' => 'Alven Safik Ritonga, S.Si., M.Si.', 'role' => 'kps', 'kode_prodi' => '1708'], // KPS
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