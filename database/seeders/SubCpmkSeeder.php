<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubCpmkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Tambahkan data dummy Sub-CPMK
        DB::table('sub_cpmk')->insert([
            ['kode_subcpmk' => 'Sub-CPMK0111', 'uraian' => 'Kemampuan untuk bertingkah laku menghargai nilai-nilai kemanusiaan berdasarkan agama, moral, dan etika.'],
            ['kode_subcpmk' => 'Sub-CPMK0121', 'uraian' => 'Kemampuan menjalankan kehidupan sosial masyarakat.'],
            ['kode_subcpmk' => 'Sub-CPMK0122', 'uraian' => 'Kemampuan memahami aturan dan norma hukum.'],
            ['kode_subcpmk' => 'Sub-CPMK0123', 'uraian' => 'Kemampuan menjalankan aturan dan norma hukum.'],
            ['kode_subcpmk' => 'Sub-CPMK0131', 'uraian' => 'Kemampuan memahami kehidupan bermasyarakat dan bernegara.'],
        ]);

        // Hubungkan dengan CPMK
        DB::table('cpmk_subcpmk')->insert([
            ['cpmk_id' => 1, 'subcpmk_id' => 1],
            ['cpmk_id' => 2, 'subcpmk_id' => 2],
            ['cpmk_id' => 2, 'subcpmk_id' => 3],
            ['cpmk_id' => 2, 'subcpmk_id' => 4],
            ['cpmk_id' => 3, 'subcpmk_id' => 5],
        ]);
    }
}