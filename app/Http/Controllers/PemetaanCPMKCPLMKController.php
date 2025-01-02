<?php

namespace App\Http\Controllers;

use App\Models\Mk;
use App\Models\Cpl;
use App\Models\Cpmk;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class PemetaanCPMKCPLMKController extends Controller
{
    public function index()
    {
        $cpmk = Cpmk::with('cpl')->get();
        $cpl = Cpl::all();
        $mk = Mk::all();

        return view('pemetaan_CPMK-CPL-MK.index', compact('cpmk', 'cpl', 'mk'));
    }

    public function update(Request $request)
    {
        $data = $request->input('mapping');

        foreach ($data as $cpmkId => $cplMapping) {
            foreach ($cplMapping as $cplId => $mkMapping) {
                foreach ($mkMapping as $mkId => $checked) {
                    DB::table('pemetaan_cpmk_cpl_mk')->updateOrInsert(
                        ['cpmk_id' => $cpmkId, 'cpl_id' => $cplId, 'mk_id' => $mkId],
                        ['checked' => $checked]
                    );
                }
            }
        }

        return redirect()->route('pemetaan_CPMK-CPL-MK.index')->with('success', 'Pemetaan berhasil diperbarui!');
    }
}
