<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cpl;
use App\Models\Bk;
use Illuminate\Support\Facades\DB;

class Cpl_BkController extends Controller
{
    public function index()
    {
        $cpls = Cpl::all();
        $bks = Bk::all();
        return view('pemetaan_CPL-BK.index', compact('cpls', 'bks'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'cpl_id' => 'required|exists:cpl,id',
            'bk_id' => 'required|exists:bk,id',
            'checked' => 'required|boolean'
        ]);

        if ($request->checked) {
            // ✅ Simpan data jika checkbox dicentang
            DB::table('cpl_bk')->updateOrInsert([
                'cpl_id' => $request->cpl_id,
                'bk_id' => $request->bk_id
            ], [
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['success' => '✅ Data tersimpan!']);
        } else {
            // ❌ Hapus data jika checkbox dihapus
            DB::table('cpl_bk')
                ->where('cpl_id', $request->cpl_id)
                ->where('bk_id', $request->bk_id)
                ->delete();

            return response()->json(['success' => '❌ Data dihapus!']);
        }
    }
}
