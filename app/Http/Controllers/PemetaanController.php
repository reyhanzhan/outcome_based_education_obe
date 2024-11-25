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
        return view('pemetaan.index', compact('cpl', 'pl'));
    }

    public function update(Request $request)
    {
        $data = $request->input('mapping'); // Data dari form
        foreach ($data as $cplId => $plIds) {
            foreach ($plIds as $plId => $checked) {
                $cpl = Cpl::find($cplId);
                $cpl->pls()->syncWithoutDetaching([$plId => ['checked' => $checked]]);
            }
        }

        return redirect()->route('pemetaan.index')->with('success', 'Pemetaan berhasil diperbarui!');
    }
}
