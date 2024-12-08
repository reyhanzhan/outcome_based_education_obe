<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cpmk;
use App\Models\Pl;

class PemetaancpmkplController extends Controller
{
    public function index()
    {
        $cpmk = Cpmk::with('pl')->get();
        $pl = Pl::all();
        return view('pemetaan_CPMK-PL.index', compact('cpmk', 'pl'));
        
    }

    public function update(Request $request)
    {
        $data = $request->input('mapping'); // Data dari form
        // dd($data);
        // Perulangan untuk memperbarui data CPL ke PL
        foreach ($data as $cpmkId => $plIds) {
            $cpmk = Cpmk::find($cpmkId);

            // Simpan data yang baru dicentang
            $syncData = [];
            foreach ($plIds as $plId => $checked) {
                $syncData[$plId] = ['checked' => $checked];
            }
            $cpmk->pl()->sync($syncData); // Menghapus yang tidak dicentang, menambahkan yang baru
        }

        return redirect()->route('pemetaan_CPMK-PL.index')->with('success', 'Pemetaan berhasil diperbarui!');
    }
}
