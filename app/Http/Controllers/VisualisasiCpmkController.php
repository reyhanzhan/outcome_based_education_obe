<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Mk;
use App\Models\Cpmk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VisualisasiCpmkController extends Controller
{
    public function chooseMahasiswa()
    {
        $mahasiswas = Mahasiswa::all();
        return view('visualisasi_cpmk.choose_mahasiswa', compact('mahasiswas'));
    }

    public function chooseMk($mahasiswa_id)
    {
        $mahasiswa = Mahasiswa::findOrFail($mahasiswa_id);
        $mks = Mk::all();
        return view('visualisasi_cpmk.choose_mk', compact('mahasiswa', 'mks'));
    }

    public function showRadar($mahasiswa_id, $mk_id)
    {
        $mahasiswa = Mahasiswa::findOrFail($mahasiswa_id);
        $mk = Mk::findOrFail($mk_id);
        $cpmks = Cpmk::whereHas('mks', function ($query) use ($mk_id) {
            $query->where('mk_id', $mk_id);
        })->with([
            'mks' => function ($query) use ($mk_id) {
                $query->where('mk_id', $mk_id)->first();
            }
        ])->get();

        $labels = [];
        $data = [];
        $minStandard = session('min_standard', 55); // Ambil standar minimum dari session atau default 55

        foreach ($cpmks as $cpmk) {
            $labels[] = $cpmk->kode_cpmk;
            $nilaiInput = $mahasiswa->nilaiCpmks()->where('mk_id', $mk_id)->where('cpmk_id', $cpmk->id)->first()->nilai ?? 0;
            $data[] = $nilaiInput; // Gunakan nilai input, bukan nilai akhir setelah bobot
        }

        return view('visualisasi_cpmk.radar', compact('mahasiswa', 'mk', 'labels', 'data', 'cpmks', 'minStandard'));
    }
}