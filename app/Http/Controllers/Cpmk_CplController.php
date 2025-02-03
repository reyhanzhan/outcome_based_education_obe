<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cpmk;
use App\Models\Cpl;
use Illuminate\Support\Facades\DB;

class Cpmk_CplController extends Controller
{
    public function index()
    {
        $cpmks = Cpmk::with('cpls')->get();
        $cpls = Cpl::all();
        return view('pemetaan_CPMK-CPL.index', compact('cpmks', 'cpls'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'cpmk_id' => 'required|exists:cpmk,id',
            'cpl_id' => 'required|exists:cpl,id',
            'checked' => 'required|boolean'
        ]);

        if ($request->checked) {
            // ✅ Simpan data jika checkbox dicentang
            DB::table('cpmk_cpl')->updateOrInsert([
                'cpmk_id' => $request->cpmk_id,
                'cpl_id' => $request->cpl_id
            ], [
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['success' => '✅ Data tersimpan!']);
        } else {
            // ❌ Hapus data jika checkbox dihapus
            DB::table('cpmk_cpl')
                ->where('cpmk_id', $request->cpmk_id)
                ->where('cpl_id', $request->cpl_id)
                ->delete();

            return response()->json(['success' => '❌ Data dihapus!']);
        }
    }
}
