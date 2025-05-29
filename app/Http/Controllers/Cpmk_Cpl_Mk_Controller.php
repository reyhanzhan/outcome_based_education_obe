<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class Cpmk_Cpl_Mk_Controller extends Controller
{

    public function index()
    {
        $cpls = Cpl::whereHas('cpmks', function ($query) {
            $query->whereHas('mks'); // Pastikan CPMK memiliki MK terkait
        })->with([
                    'cpmks' => function ($query) {
                        $query->whereHas('mks')->with([
                            'mks' => function ($subQuery) {
                                $subQuery->distinct(); // Ambil MK unik
                            }
                        ]);
                    },
                ])->get();
        return view('pemetaan_CPMK-CPL-MK.index', compact('cpls'));
    }

    public function store(Request $request)
    {
        $mappings = $request->input('mappings', []);

        foreach ($mappings as $mapping) {
            [$cplId, $cpmkId, $mkId] = explode('|', $mapping);

            DB::table('cpmk_cpl_mk')->updateOrInsert(
                ['cpl_id' => $cplId, 'cpmk_id' => $cpmkId, 'mk_id' => $mkId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        return redirect()->route('teknik_penilaian.index')->with('success', 'Pemetaan berhasil disimpan!');
    }


    // public function pilihMetodePenilaian()
    // {
    //     $pemetaan = DB::table('cpmk_cpl_mk')
    //         ->join('cpmk', 'cpmk_cpl_mk.cpmk_id', '=', 'cpmk.id')
    //         ->join('cpl', 'cpmk_cpl_mk.cpl_id', '=', 'cpl.id')
    //         ->join('mk', 'cpmk_cpl_mk.mk_id', '=', 'mk.id')
    //         ->select('cpmk_cpl_mk.id', 'cpl.kode_cpl', 'mk.kode_mk', 'cpmk.kode_cpmk')
    //         ->get();

    //     return view('metode_penilaian.index', compact('pemetaan'));
    // }


}
