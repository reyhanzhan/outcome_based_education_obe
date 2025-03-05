<?php

namespace App\Http\Controllers;

use App\Models\Mk;
use App\Models\Mahasiswa;
use App\Models\Cpmk;
use App\Models\NilaiCpmk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PenilaianCpmkController extends Controller
{
    public function chooseMahasiswa()
    {
        $mahasiswas = Mahasiswa::all();
        return view('penilaian_cpmk.choose_mahasiswa', compact('mahasiswas'));
    }

    public function chooseMk($mahasiswa_id)
    {
        $mahasiswa = Mahasiswa::findOrFail($mahasiswa_id);
        $mks = Mk::all();
        return view('penilaian_cpmk.choose_mk', compact('mahasiswa', 'mks'));
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

        return view('penilaian_cpmk.radar', compact('mahasiswa', 'mk', 'labels', 'data', 'cpmks', 'minStandard'));
    }

    public function index($mk_id)
    {
        $mk = Mk::findOrFail($mk_id);
        $mahasiswas = Mahasiswa::all(); // Ambil semua mahasiswa untuk tampilan tabel
        $cpmks = Cpmk::with('mks')->whereHas('mks', function ($query) use ($mk_id) {
            $query->where('mk_id', $mk_id);
        })->get();
        $minStandard = session('min_standard', 55); // Ambil standar minimum dari session atau default 55

        // Ambil mahasiswa_id dari sesi atau dari parameter (opsional, sesuaikan logika)
        $mahasiswa_id = request()->session()->get('current_mahasiswa_id'); // Contoh, gunakan sesi
        if (!$mahasiswa_id) {
            $mahasiswa_id = $mahasiswas->first()->id ?? null; // Default ke mahasiswa pertama jika tidak ada sesi
        }

        return view('penilaian_cpmk.index', compact('mk', 'mahasiswas', 'cpmks', 'minStandard', 'mahasiswa_id'));
    }

    public function calculateMkScore($mk_id, $mahasiswa_id)
    {
        $nilaiCpmks = NilaiCpmk::where('mk_id', $mk_id)
            ->where('mahasiswa_id', $mahasiswa_id)
            ->with('cpmk')
            ->get();

        $totalScore = 0;
        $maxScore = 100; // Total bobot CPMK harus 100%

        foreach ($nilaiCpmks as $nilaiCpmk) {
            $bobot = $nilaiCpmk->cpmk->mks()->where('mk_id', $mk_id)->first()->pivot->bobot ?? 0;
            if ($bobot <= 0) {
                Log::warning('Bobot CPMK belum disetel untuk CPMK: ' . $nilaiCpmk->cpmk_id . ', MK: ' . $mk_id);
                continue;
            }
            $totalScore += ($bobot * $nilaiCpmk->nilai) / 100;
        }

        return $totalScore;
    }
}