<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Mk;
use App\Models\Cpmk;
use App\Models\NilaiCpmk;
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

        // Ambil CPMK yang terkait dengan mata kuliah
        $cpmks = Cpmk::whereHas('mks', function ($query) use ($mk_id) {
            $query->where('mk_id', $mk_id);
        })->with([
            'mks' => function ($query) use ($mk_id) {
                $query->where('mk_id', $mk_id);
            }
        ])->get();

        // Ambil jumlah penilaian maksimum
        $jumlahPenilaian = NilaiCpmk::where('mahasiswa_id', $mahasiswa_id)
            ->where('mk_id', $mk_id)
            ->max('penilaian_ke') ?? 1; // Default ke 1 jika tidak ada data
        Log::info('Jumlah Penilaian:', ['jumlahPenilaian' => $jumlahPenilaian]);

        // Ambil nilai CPMK dan kelompokkan berdasarkan penilaian_ke
        $nilaiPerPenilaian = [];
        $radarDataPerPenilaian = [];
        $finalScores = [];
        $minStandard = session('min_standard', 55);

        // Siapkan labels (kode CPMK) untuk grafik
        $labels = $cpmks->pluck('kode_cpmk')->toArray();

        for ($i = 1; $i <= $jumlahPenilaian; $i++) {
            // Ambil nilai untuk penilaian_ke tertentu
            $nilaiCpmks = $mahasiswa->nilaiCpmks()
                ->where('mk_id', $mk_id)
                ->where('penilaian_ke', $i)
                ->get()
                ->keyBy('cpmk_id');

            // Siapkan data untuk grafik radar
            $data = [];
            $totalScore = 0;
            $totalBobot = 0;

            foreach ($cpmks as $cpmk) {
                $nilaiInput = $nilaiCpmks[$cpmk->id]->nilai ?? 0;
                $bobot = $cpmk->mks->isNotEmpty() ? ($cpmk->mks->first()->pivot->bobot ?? 0) : 0;
                $nilaiAkhir = ($nilaiInput * $bobot) / 100;
                $data[] = $nilaiInput; // Gunakan nilai asli untuk grafik

                // Hitung kontribusi untuk nilai total MK
                $totalScore += $nilaiAkhir;
                $totalBobot += $bobot;

                Log::info('CPMK Data for Penilaian Ke ' . $i, [
                    'cpmk_id' => $cpmk->id,
                    'kode_cpmk' => $cpmk->kode_cpmk,
                    'nilaiInput' => $nilaiInput,
                    'bobot' => $bobot,
                    'nilaiAkhir' => $nilaiAkhir,
                ]);
            }

            // Hitung nilai total MK untuk penilaian ini
            $finalScore = $totalBobot > 0 ? ($totalScore / ($totalBobot / 100)) : 0;
            $finalScores[$i] = $finalScore;

            // Simpan data untuk grafik radar
            $radarDataPerPenilaian[$i] = [
                'labels' => $labels,
                'data' => $data,
            ];

            // Simpan nilai per penilaian
            $nilaiPerPenilaian[$i] = $nilaiCpmks;

            Log::info('Radar Data for Penilaian Ke ' . $i, [
                'mahasiswa_id' => $mahasiswa_id,
                'mk_id' => $mk_id,
                'labels' => $labels,
                'data' => $data,
                'finalScore' => $finalScore,
            ]);
        }

        return view('visualisasi_cpmk.radar', compact('mahasiswa', 'mk', 'cpmks', 'minStandard', 'jumlahPenilaian', 'radarDataPerPenilaian', 'nilaiPerPenilaian', 'finalScores'));
    }
}