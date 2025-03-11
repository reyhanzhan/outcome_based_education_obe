<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Cpl;
use App\Models\NilaiCpmk;
use Illuminate\Http\Request;

class PenilaianCplController extends Controller
{
    // ✅ Pilih Mahasiswa sebelum melihat Penilaian CPL
    public function chooseMahasiswa()
    {
        $mahasiswas = Mahasiswa::all();
        return view('penilaian_cpl.choose_mahasiswa', compact('mahasiswas'));
    }

    // ✅ Tampilkan hasil penilaian CPL untuk mahasiswa yang dipilih
    public function index($mahasiswa_id)
    {
        $mahasiswa = Mahasiswa::findOrFail($mahasiswa_id);
        $cpls = Cpl::with(['cpmks.mks'])->get(); // Ambil semua CPL beserta CPMK dan MK terkait

        $cplData = [];

        foreach ($cpls as $cpl) {
            $totalScore = 0;
            $totalMaxWeight = 0;

            foreach ($cpl->cpmks as $cpmk) {
                foreach ($cpmk->mks as $mk) {
                    $nilaiCpmk = NilaiCpmk::where('mahasiswa_id', $mahasiswa_id)
                        ->where('cpmk_id', $cpmk->id)
                        ->where('mk_id', $mk->id)
                        ->first();

                    $nilai = $nilaiCpmk->nilai ?? 0;
                    $bobot = $mk->pivot->bobot ?? 0; // Bobot dari CPMK di mata kuliah tertentu

                    if ($bobot > 0) {
                        $totalScore += ($nilai * $bobot) / 100;
                        $totalMaxWeight += $bobot;
                    }
                }
            }

            // Hitung pencapaian CPL
            $pencapaianCpl = $totalMaxWeight > 0 ? round(($totalScore / $totalMaxWeight) * 100, 2) : 0;

            $cplData[] = [
                'kode_cpl' => $cpl->kode_cpl,
                'deskripsi' => $cpl->deskripsi,
                'nilai_cpl' => $totalScore,
                'pencapaian_cpl' => $pencapaianCpl
            ];
        }

        return view('penilaian_cpl.index', compact('mahasiswa', 'cplData'));
    }
}
