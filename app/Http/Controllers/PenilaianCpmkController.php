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

        // Ambil data mahasiswa berdasarkan mahasiswa_id
        $mahasiswa = Mahasiswa::findOrFail($mahasiswa_id);
        Log::info('Mahasiswa found:', ['id' => $mahasiswa->id, 'nama' => $mahasiswa->nama]);

        // Ambil data mata kuliah berdasarkan mk_id
        $mk = Mk::findOrFail($mk_id);
        Log::info('MK found:', ['id' => $mk->id, 'kode_mk' => $mk->kode_mk]);

        // Validasi bahwa mata kuliah sesuai dengan periode dan kelas yang dipilih
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
                ->with('error', 'Mahasiswa tidak terdaftar pada mata kuliah ini untuk periode dan kelas yang dipilih.');
        }

        // Ambil CPMK yang terkait dengan mata kuliah
        $cpmks = Cpmk::whereHas('mks', function ($query) use ($mk_id) {
            $query->where('mk_id', $mk_id);
        })->with([
            'mks' => function ($query) use ($mk_id) {
                $query->where('mk_id', $mk_id)->withPivot('bobot', 'min_standard');
            },
            'nilaiCpmks' => function ($query) use ($mahasiswa_id, $mk_id) {
                $query->where('mahasiswa_id', $mahasiswa_id)
                    ->where('mk_id', $mk_id);
            }
        ])->get();
        Log::info('CPMKs found:', ['count' => $cpmks->count()]);

        // Ambil jumlah penilaian maksimum
        $jumlahPenilaian = NilaiCpmk::where('mahasiswa_id', $mahasiswa_id)
            ->where('mk_id', $mk_id)
            ->max('penilaian_ke') ?? 1; // Default ke 1 jika tidak ada data
        Log::info('Jumlah Penilaian:', ['jumlahPenilaian' => $jumlahPenilaian]);

        // Ambil nilai CPMK dan kelompokkan berdasarkan penilaian_ke
        $nilaiPerPenilaian = [];
        for ($i = 1; $i <= $jumlahPenilaian; $i++) {
            $nilaiPerPenilaian[$i] = NilaiCpmk::where('mahasiswa_id', $mahasiswa_id)
                ->where('mk_id', $mk_id)
                ->where('penilaian_ke', $i)
                ->get()
                ->keyBy('cpmk_id');
        }
        Log::info('Nilai per Penilaian:', ['nilaiPerPenilaian' => $nilaiPerPenilaian]);

        $minStandard = $cpmks->isNotEmpty() ? ($cpmks->first()->mks->first()->pivot->min_standard ?? 55) : 55;
        Log::info('Min Standard:', ['minStandard' => $minStandard]);

        // Kirim semua variabel yang diperlukan ke view
        return view('penilaian_cpmk.index', compact('mahasiswa', 'mk', 'cpmks', 'minStandard', 'periode', 'kelasInput', 'jumlahPenilaian', 'nilaiPerPenilaian'));
    }

    public function calculateMkScore($mk_id, $mahasiswa_id, $penilaian_ke = null)
    {
        $query = NilaiCpmk::where('mk_id', $mk_id)
            ->where('mahasiswa_id', $mahasiswa_id)
            ->with('cpmk');

        // Jika penilaian_ke diberikan, filter berdasarkan penilaian_ke
        if ($penilaian_ke !== null) {
            $query->where('penilaian_ke', $penilaian_ke);
        }

        $nilaiCpmks = $query->get();

        $totalScore = 0;
        $totalBobot = 0;

        foreach ($nilaiCpmks as $nilaiCpmk) {
            $bobot = $nilaiCpmk->cpmk->mks()->where('mk_id', $mk_id)->first()->pivot->bobot ?? 0;
            if ($bobot > 0) {
                // Hitung kontribusi per CPMK dan bulatkan ke 0 desimal
                $kontribusi = round(($nilaiCpmk->nilai * $bobot) / 100, 0);
                $totalScore += $kontribusi;
                $totalBobot += $bobot;
                Log::info("CPMK ID {$nilaiCpmk->cpmk_id}: Nilai = {$nilaiCpmk->nilai}, Bobot = {$bobot}, Kontribusi (dibulatkan) = {$kontribusi}");
            } else {
                Log::warning('Bobot CPMK belum disetel untuk CPMK: ' . $nilaiCpmk->cpmk_id . ', MK: ' . $mk_id);
            }
        }

        // Normalisasi total score berdasarkan total bobot
        $finalScore = $totalBobot > 0 ? ($totalScore / ($totalBobot / 100)) : 0;
        Log::info("Total Score untuk Mahasiswa ID {$mahasiswa_id}, MK ID {$mk_id}, Penilaian Ke {$penilaian_ke}: {$finalScore}, Total Bobot: {$totalBobot}");

        return $finalScore;
    }
}