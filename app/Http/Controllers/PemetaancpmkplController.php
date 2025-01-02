<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use App\Models\Cpmk;
use App\Models\Pl;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PemetaancpmkplController extends Controller
{
    public function index()
    {
        $cpmk = Cpmk::with('cpl')->get();
        $cpl = Cpl::all();
        return view('pemetaan_CPMK-CPL.index', compact('cpmk', 'cpl'));

    }

    public function update(Request $request)
    {
        $data = $request->input('mapping'); // Data dari form

        if (!$data || !is_array($data)) {
            return redirect()->route('pemetaan_CPMK-CPL.index')
                ->with('error', 'Data pemetaan tidak valid!');
        }

        // Perulangan untuk memperbarui data CPMK ke CPL
        foreach ($data as $cpmkId => $cplIds) {
            $cpmk = Cpmk::find($cpmkId);

            if ($cpmk) {
                // Simpan data yang baru dicentang
                $syncData = [];
                foreach ($cplIds as $cplId => $checked) {
                    $syncData[$cplId] = ['checked' => $checked];
                }
                $cpmk->cpl()->sync($syncData);
            }
        }

        return redirect()->route('pemetaan_CPMK-CPL.index')
            ->with('success', 'Pemetaan berhasil diperbarui!');
    }

}
