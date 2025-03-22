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
        $mahasiswa_id = (int) $mahasiswa_id;
        $mk_id = (int) $mk_id;

        $mahasiswa = Mahasiswa::findOrFail($mahasiswa_id);
        $mk = Mk::findOrFail($mk_id);
        $cpmks = Cpmk::whereHas('mks', function ($query) use ($mk_id) {
            $query->where('mk_id', $mk_id);
        })->with([
                    'mks' => function ($query) use ($mk_id) {
                        $query->where('mk_id', $mk_id);
                    }
                ])->get();

        $labels = [];
        $data = [];
        $minStandard = session('min_standard', 55);

        // Ambil semua nilai CPMK sekaligus
        $nilaiCpmks = $mahasiswa->nilaiCpmks()->where('mk_id', $mk_id)->get()->keyBy('cpmk_id');

        // Hitung nilai total MK
        $totalScore = 0;
        $totalBobot = 0;

        foreach ($cpmks as $cpmk) {
            $labels[] = $cpmk->kode_cpmk;
            $nilaiInput = $nilaiCpmks[$cpmk->id]->nilai ?? 0;
            $bobot = $cpmk->mks->isNotEmpty() ? ($cpmk->mks->first()->pivot->bobot ?? 0) : 0;
            $nilaiAkhir = ($nilaiInput * $bobot) / 100;
            $data[] = $nilaiInput; // Gunakan nilai asli untuk grafik

            // Hitung kontribusi untuk nilai total MK
            $totalScore += $nilaiAkhir;
            $totalBobot += $bobot;

            Log::info('CPMK Data', [
                'cpmk_id' => $cpmk->id,
                'kode_cpmk' => $cpmk->kode_cpmk,
                'nilaiInput' => $nilaiInput,
                'bobot' => $bobot,
                'nilaiAkhir' => $nilaiAkhir,
            ]);
        }

        // Hitung nilai total MK
        $finalScore = $totalBobot > 0 ? ($totalScore / ($totalBobot / 100)) : 0;

        Log::info('Radar Data', [
            'mahasiswa_id' => $mahasiswa_id,
            'mk_id' => $mk_id,
            'labels' => $labels,
            'data' => $data,
            'finalScore' => $finalScore,
        ]);

        return view('visualisasi_cpmk.radar', compact('mahasiswa', 'mk', 'labels', 'data', 'cpmks', 'minStandard', 'finalScore'));
    }
}