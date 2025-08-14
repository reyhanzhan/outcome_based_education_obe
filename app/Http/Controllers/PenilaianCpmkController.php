<?php

namespace App\Http\Controllers;

use App\Models\Mk;
use App\Models\Mahasiswa;
use App\Models\Cpmk;
use App\Models\Krs;
use App\Models\NilaiCpmk;
use App\Models\ObeEvaluation;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;

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

    $periode = session('previous_periode');
    $kelasInput = session('previous_kelas');
    $tahunFilter = session('selected_year', '');
    $user = Auth::user();
    $kodeProdi = $user->kode_prodi;

    Log::info('Session Data:', ['periode' => $periode, 'kelas' => $kelasInput, 'tahunFilter' => $tahunFilter]);

    // Validasi sesi
    if (!$periode || !$kelasInput || !$tahunFilter) {
        Log::warning('Periode, kelas, or tahunFilter not found or invalid in session');
        return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
            ->with('error', 'Silakan pilih periode dan kelas terlebih dahulu.');
    }

    [$kodeMk, $namaKelas] = explode('|', $kelasInput);

    // Ambil data mahasiswa
    $mahasiswa = Mahasiswa::findOrFail($mahasiswa_id);
    Log::info('Mahasiswa found:', ['id' => $mahasiswa->id, 'nama' => $mahasiswa->nama]);

    // Ambil data mata kuliah
    $mk = Mk::findOrFail($mk_id);
    Log::info('MK found:', ['id' => $mk->id, 'kode_mk' => $mk->kode_mk]);

    // Ambil data KRS untuk memastikan pendaftaran dan ambil kurikulum_id
    $krs = Krs::where('nim', $mahasiswa->nim)
        ->where('periode', $periode)
        ->where('kode_mk', $kodeMk)
        ->where('nama_kelas', $namaKelas)
        ->where('tahun', $tahunFilter)
        ->whereHas('mahasiswa', function ($query) use ($kodeProdi) {
            $query->where('kode_prodi', $kodeProdi);
        })
        ->first();

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

    $kurikulumId = $krs->kurikulum_id;

    // Ambil data CPMK dengan filter kurikulum_id dan debugging
    $cpmks = Cpmk::whereHas('mks', function ($query) use ($mk_id, $kurikulumId) {
        $query->where('mk_id', $mk_id)->where('kurikulum_id', $kurikulumId);
    })->with([
        'mks' => function ($query) use ($mk_id, $kurikulumId) {
            $query->where('mk_id', $mk_id)
                ->where('kurikulum_id', $kurikulumId)
                ->withPivot('bobot', 'min_standard');
        },
        'teknikPenilaian' => function ($query) use ($mk_id, $kurikulumId) {
            $query->where('mk_id', $mk_id)->where('kurikulum_id', $kurikulumId);
        },
        'nilaiCpmks' => function ($query) use ($mahasiswa_id, $mk_id) {
            $query->where('mahasiswa_id', $mahasiswa_id)->where('mk_id', $mk_id);
        }
    ])->get();

    // Debugging untuk memverifikasi data CPMK
    Log::info('Loaded CPMKs for MK ' . $mk_id . ': ' . json_encode($cpmks->pluck('id')->toArray()));
    Log::info('Pivot Bobots: ' . json_encode($cpmks->map(function ($cpmk) use ($mk_id) {
        $pivot = $cpmk->mks->where('id', $mk_id)->first()->pivot ?? null;
        return $pivot ? $pivot->bobot : 'null';
    })->toArray()));
    Log::info('Teknik Penilaian Bobots: ' . json_encode($cpmks->map(function ($cpmk) {
        return $cpmk->teknikPenilaian->sum('bobot');
    })->toArray()));

    if ($cpmks->isEmpty()) {
        Log::warning('No CPMKs found for MK ID: ' . $mk_id);
        return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
            ->with('error', 'Tidak ada CPMK yang terkait dengan mata kuliah ini.');
    }

    // Sinkronisasi nilaiCpmks dengan data terbaru
    $nilaiCpmks = [];
    foreach ($cpmks as $cpmk) {
        $nilai = $cpmk->nilaiCpmks->first();
        $nilaiCpmks[$cpmk->id] = $nilai ? $nilai->nilai : 0;
        Log::info('Nilai CPMK ' . $cpmk->id . ': ' . ($nilaiCpmks[$cpmk->id] ?? 'null'));
    }

    // Ambil minStandard dari pivot yang valid
    $minStandard = $cpmks->isNotEmpty() ? (int)($cpmks->first()->mks->where('id', $mk_id)->first()->pivot->min_standard ?? 55) : 55;
    Log::info('Min Standard:', ['minStandard' => $minStandard]);

    return view('penilaian_cpmk.index', compact('mahasiswa', 'mk', 'cpmks', 'minStandard', 'periode', 'kelasInput', 'nilaiCpmks'));
}

    public function calculateMkScore($mk_id, $mahasiswa_id)
    {
        Log::info('calculateMkScore called', ['mk_id' => $mk_id, 'mahasiswa_id' => $mahasiswa_id]);

        $nilaiCpmks = NilaiCpmk::where('mk_id', $mk_id)
            ->where('mahasiswa_id', $mahasiswa_id)
            ->with([
                'cpmk.mks' => function ($query) use ($mk_id) {
                    $query->where('mk_id', $mk_id);
                }
            ])
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

    public function storeEvaluation($mahasiswa_id, $mk_id, Request $request)
    {
        Log::info('storeEvaluation called', ['mahasiswa_id' => $mahasiswa_id, 'mk_id' => $mk_id]);

        $mahasiswa = Mahasiswa::findOrFail($mahasiswa_id);
        $mk = Mk::findOrFail($mk_id);
        $periode = session('previous_periode');
        $kelasInput = session('previous_kelas');
        [$kodeMk, $namaKelas] = explode('|', $kelasInput);

        $cpmks = Cpmk::whereHas('mks', function ($query) use ($mk_id) {
            $query->where('mk_id', $mk_id);
        })->with([
                    'mks' => function ($query) use ($mk_id) {
                        $query->where('mk_id', $mk_id)->withPivot('bobot');
                    },
                    'nilaiCpmks' => function ($query) use ($mahasiswa_id, $mk_id) {
                        $query->where('mahasiswa_id', $mahasiswa_id)->where('mk_id', $mk_id);
                    }
                ])->get();

        $nilaiCpmks = [];
        foreach ($cpmks as $cpmk) {
            $nilai = $cpmk->nilaiCpmks->first();
            $nilaiCpmks[$cpmk->id] = $nilai ? $nilai->nilai : 0;
        }

        $totalScore = 0;
        foreach ($cpmks as $cpmk) {
            $nilaiInput = $nilaiCpmks[$cpmk->id] ?? 0;
            $bobot = $cpmk->mks->first()->pivot->bobot ?? 0;
            $totalScore += ($nilaiInput * $bobot) / 100;
        }

        $evaluation = new ObeEvaluation([
            'mahasiswa_id' => $mahasiswa_id,
            'mk_id' => $mk_id,
            'nilai_cpmk' => json_encode($nilaiCpmks),
            'total_score' => $totalScore,
            'periode' => $periode,
            'tahun' => session('selected_year', ''),
        ]);
        $evaluation->save();

        Log::info('Evaluation saved', ['evaluation_id' => $evaluation->id, 'total_score' => $totalScore]);
        return redirect()->back()->with('success', 'Data evaluasi OBE berhasil disimpan!');
    }

    public function showEvaluationHistory($mahasiswa_id, $mk_id)
    {
        $mahasiswa = Mahasiswa::findOrFail($mahasiswa_id);
        $mk = Mk::findOrFail($mk_id);
        $evaluations = ObeEvaluation::where('mahasiswa_id', $mahasiswa_id)
            ->where('mk_id', $mk_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('penilaian_cpmk.evaluation_history', compact('mahasiswa', 'mk', 'evaluations'));
    }

   public function showEvaluationDetail($mahasiswa_id, $mk_id, $evaluation_id)
{
    $mahasiswa = Mahasiswa::findOrFail($mahasiswa_id);
    $mk = Mk::findOrFail($mk_id);
    $evaluation = ObeEvaluation::findOrFail($evaluation_id);

    // Ambil CPMK terkait dengan mk_id dan sertakan min_standard
    $cpmks = Cpmk::whereHas('mks', function ($query) use ($mk_id) {
        $query->where('mk_id', $mk_id);
    })->with([
        'mks' => function ($query) use ($mk_id) {
            $query->where('mk_id', $mk_id)->withPivot('bobot', 'min_standard');
        }
    ])->get();

    Log::info('CPMKs fetched for mk_id ' . $mk_id, ['cpmks' => $cpmks->toArray()]);

    // Parse nilai_cpmk dari JSON
    $nilaiCpmks = json_decode($evaluation->nilai_cpmk, true) ?? [];

    return view('penilaian_cpmk.evaluation_detail', compact('mahasiswa', 'mk', 'evaluation', 'cpmks', 'nilaiCpmks'));
}
}