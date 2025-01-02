<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cpl;
use App\Models\Pl;


class PemetaanController extends Controller
{
    public function index()
    {
        $cpl = Cpl::with('pl')->get();
        $pl = Pl::all();
        return view('pemetaan_CPL-PL.index', compact('cpl', 'pl'));
    }

    public function update(Request $request)
    {
        $data = $request->input('mapping'); // Data dari form
        // dd($data);
        // Perulangan untuk memperbarui data CPL ke PL
        foreach ($data as $cplId => $plIds) {
            $cpl = Cpl::find($cplId);

            // Simpan data yang baru dicentang
            $syncData = [];
            foreach ($plIds as $plId => $checked) {
                $syncData[$plId] = ['checked' => $checked];
            }
            $cpl->pl()->sync($syncData); // Menghapus yang tidak dicentang, menambahkan yang baru
        }

        return redirect()->route('pemetaan_CPL-PL.index')->with('success', 'Pemetaan berhasil diperbarui!');
    }
}
