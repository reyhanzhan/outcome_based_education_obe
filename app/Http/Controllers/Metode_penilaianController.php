<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class Metode_penilaianController extends Controller
{

    public function pilihMetodePenilaian()
    {
        $pemetaan = DB::table('cpmk_cpl_mk')
            ->join('cpmk', 'cpmk_cpl_mk.cpmk_id', '=', 'cpmk.id')
            ->join('cpl', 'cpmk_cpl_mk.cpl_id', '=', 'cpl.id')
            ->join('mk', 'cpmk_cpl_mk.mk_id', '=', 'mk.id')
            ->leftJoin('metode_penilaian', 'metode_penilaian.cpmk_cpl_mk_id', '=', 'cpmk_cpl_mk.id')
            ->select(
                'cpmk_cpl_mk.id',
                'cpl.kode_cpl',
                'mk.kode_mk',
                'cpmk.kode_cpmk',
                'metode_penilaian.partisipasi',
                'metode_penilaian.observasi',
                'metode_penilaian.unjuk_kerja',
                'metode_penilaian.tes_tulis_uts',
                'metode_penilaian.tes_tulis_uas',
                'metode_penilaian.tes_lisan'
            )
            ->get();

        return view('metode_penilaian.index', compact('pemetaan'));
    }

    public function simpanMetodePenilaian(Request $request)
    {
        $pemetaanData = $request->input('pemetaan', []);

        foreach ($pemetaanData as $id => $data) {
            DB::table('metode_penilaian')->updateOrInsert(
                ['cpmk_cpl_mk_id' => $id],
                [
                    'partisipasi' => isset($data['partisipasi']) ? true : false,
                    'observasi' => isset($data['observasi']) ? true : false,
                    'unjuk_kerja' => isset($data['unjuk_kerja']) ? true : false,
                    'tes_tulis_uts' => isset($data['tes_tulis_uts']) ? true : false,
                    'tes_tulis_uas' => isset($data['tes_tulis_uas']) ? true : false,
                    'tes_lisan' => isset($data['tes_lisan']) ? true : false,
                    'updated_at' => now(),
                ]
            );
        }

        return redirect()->route('teknik_penilaian.index')->with('success', 'Metode penilaian berhasil disimpan!');
    }


}

