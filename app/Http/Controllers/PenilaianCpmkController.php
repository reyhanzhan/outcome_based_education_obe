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

    public function index($mk_id)
    {
        $mk = Mk::findOrFail($mk_id);
        $mahasiswas = Mahasiswa::all(); // Ambil semua mahasiswa untuk tampilan tabel

        // Ambil CPMK yang terkait dengan MK ini
        $cpmks = Cpmk::with([
            'mks' => function ($query) use ($mk_id) {
                $query->where('mk_id', $mk_id)->withPivot('bobot', 'min_standard');
            },
            'nilaiCpmks' => function ($query) use ($mk_id) {
                $query->where('mk_id', $mk_id);
            }
        ])->whereHas('mks', function ($query) use ($mk_id) {
            $query->where('mk_id', $mk_id);
        })->get();

        // Ambil min_standard dari tabel pivot cpmk_mk untuk MK ini
        $minStandard = $cpmks->isNotEmpty() ? ($cpmks->first()->mks->first()->pivot->min_standard ?? 55) : 55;

        // Ambil mahasiswa_id dari query string atau sesi
        $mahasiswa_id = request()->input('mahasiswa_id') ?? request()->session()->get('current_mahasiswa_id');
        if (!$mahasiswa_id) {
            $mahasiswa_id = $mahasiswas->first()->id ?? null; // Default ke mahasiswa pertama jika tidak ada
        }
        request()->session()->put('current_mahasiswa_id', $mahasiswa_id);

        // Debug: Log data nilai CPMK
        $nilaiCpmks = NilaiCpmk::where('mk_id', $mk_id)->get();
        Log::info('Nilai CPMK untuk MK ID ' . $mk_id . ': ' . $nilaiCpmks->toJson());

        return view('penilaian_cpmk.index', compact('mk', 'mahasiswas', 'cpmks', 'minStandard', 'mahasiswa_id'));
    }

    

    public function calculateMkScore($mk_id, $mahasiswa_id)
    {
        $nilaiCpmks = NilaiCpmk::where('mk_id', $mk_id)
            ->where('mahasiswa_id', $mahasiswa_id)
            ->with('cpmk')
            ->get();

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
        Log::info("Total Score untuk Mahasiswa ID {$mahasiswa_id}, MK ID {$mk_id}: {$finalScore}, Total Bobot: {$totalBobot}");

        return $finalScore;
    }
}