<?php

namespace App\Http\Controllers;

use App\Models\Mk;
use App\Models\Cpl;
use App\Models\Cpmk;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class Cpmk_MkController extends Controller
{
    public function index()
    {
        $mks = Mk::with('mks')->get();
        $cpmks = Cpmk::all();
        return view('pemetaan_CPMK-MK.index', compact('mks', 'cpmks'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'mk_id' => 'required|exists:mk,id',
            'cpmk_id' => 'required|exists:cpmk,id',
            'checked' => 'required|boolean'
        ]);

        if ($request->checked) {
            // ✅ Simpan data jika checkbox dicentang
            DB::table('cpmk_mk')->updateOrInsert([
                'mk_id' => $request->mk_id,
                'cpmk_id' => $request->cpmk_id
            ], [
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['success' => '✅ Data tersimpan!']);
        } else {
            // ❌ Hapus data jika checkbox dihapus
            DB::table('cpmk_mk')
                ->where('mk_id', $request->mk_id)
                ->where('cpmk_id', $request->cpmk_id)
                ->delete();

            return response()->json(['success' => '❌ Data dihapus!']);
        }
    }
}
