<?php

namespace App\Http\Controllers;

use App\Models\Mk;
use App\Models\Mahasiswa;
use App\Models\Cpmk;
use App\Models\Krs;
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

    public function index($mahasiswa_id, $mk_id)
    {
        Log::info('PenilaianCpmkController::index called', ['mahasiswa_id' => $mahasiswa_id, 'mk_id' => $mk_id]);

        // Ambil periode dan kelas dari session
        $periode = session('previous_periode');
        $kelasInput = session('previous_kelas');
        Log::info('Session Data:', ['periode' => $periode, 'kelas' => $kelasInput]);

        if (!$periode || !$kelasInput) {
            Log::warning('Periode or kelas not found in session');
            return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                ->with('error', 'Silakan pilih periode dan kelas terlebih dahulu.');
        }

        // Parse kelasInput (kode_mk|nama_kelas)
        [$kodeMk, $namaKelas] = explode('|', $kelasInput);

        // Ambil data mahasiswa
        $mahasiswa = Mahasiswa::findOrFail($mahasiswa_id);
        Log::info('Mahasiswa found:', ['id' => $mahasiswa->id, 'nama' => $mahasiswa->nama]);

        // Ambil data mata kuliah
        $mk = Mk::findOrFail($mk_id);
        Log::info('MK found:', ['id' => $mk->id, 'kode_mk' => $mk->kode_mk]);

        // Validasi KRS
        $krs = Krs::where('nim', $mahasiswa->nim)
            ->where('periode', $periode)
            ->where('kode_mk', $kodeMk)
            ->where('nama_kelas', $namaKelas)
            ->exists();

        if (!$krs) {
            Log::warning('KRS data not found for mahasiswa', [
                'nim' => $mahasiswa->nim,
                'periode' => $periode,
                'kode_mk' => $kodeMk,
                'nama_kelas' => $namaKelas,
            ]);
            return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                ->with('error', 'Mahasiswa tidak terdaftar pada mata kuliah ini.');
        }

        // Ambil CPMK dengan bobot dan nilai
        $cpmks = Cpmk::whereHas('mks', function ($query) use ($mk_id) {
            $query->where('mk_id', $mk_id);
        })->with([
            'mks' => function ($query) use ($mk_id) {
                $query->where('mk_id', $mk_id)->withPivot('bobot', 'min_standard');
            },
            'nilaiCpmks' => function ($query) use ($mahasiswa_id, $mk_id) {
                $query->where('mahasiswa_id', $mahasiswa_id)->where('mk_id', $mk_id);
            }
        ])->get();
        Log::info('CPMKs found:', ['count' => $cpmks->count()]);

        if ($cpmks->isEmpty()) {
            Log::warning('No CPMKs found for MK ID: ' . $mk_id);
            return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                ->with('error', 'Tidak ada CPMK yang terkait dengan mata kuliah ini.');
        }

        // Siapkan data nilai CPMK
        $nilaiCpmks = [];
        foreach ($cpmks as $cpmk) {
            $nilai = $cpmk->nilaiCpmks->first();
            $nilaiCpmks[$cpmk->id] = $nilai ? $nilai->nilai : 0;
        }

        $minStandard = $cpmks->isNotEmpty() ? ($cpmks->first()->mks->first()->pivot->min_standard ?? 55) : 55;
        Log::info('Min Standard:', ['minStandard' => $minStandard]);

        return view('penilaian_cpmk.index', compact('mahasiswa', 'mk', 'cpmks', 'minStandard', 'periode', 'kelasInput', 'nilaiCpmks'));
    }

    public function calculateMkScore($mk_id, $mahasiswa_id)
    {
        Log::info('calculateMkScore called', ['mk_id' => $mk_id, 'mahasiswa_id' => $mahasiswa_id]);

        $nilaiCpmks = NilaiCpmk::where('mk_id', $mk_id)
            ->where('mahasiswa_id', $mahasiswa_id)
            ->with(['cpmk.mks' => function ($query) use ($mk_id) {
                $query->where('mk_id', $mk_id);
            }])
            ->get();

        $totalScore = 0;

        foreach ($nilaiCpmks as $nilaiCpmk) {
            $bobot = $nilaiCpmk->cpmk->mks->first()->pivot->bobot ?? 0;
            $nilaiInput = $nilaiCpmk->nilai ?? 0;
            $kontribusi = ($nilaiInput * $bobot) / 100;
            $totalScore += $kontribusi;
            Log::info("CPMK ID {$nilaiCpmk->cpmk_id}: Nilai Input = {$nilaiInput}, Bobot = {$bobot}, Kontribusi = {$kontribusi}");
        }

        $finalScore = round($totalScore, 0);
        Log::info("Total Score untuk Mahasiswa ID {$mahasiswa_id}, MK ID {$mk_id}: {$finalScore}");

        return $finalScore;
    }
}