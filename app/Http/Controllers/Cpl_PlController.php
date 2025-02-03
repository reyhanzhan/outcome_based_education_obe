<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cpl;
use App\Models\Pl;
use Illuminate\Support\Facades\DB;

class Cpl_PlController extends Controller
{
    public function index()
    {
        $cpls = Cpl::all();
        $pls = Pl::all();
        return view('pemetaan_CPL-PL.index', compact('cpls', 'pls'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'cpl_id' => 'required|exists:cpl,id',
            'pl_id' => 'required|exists:pl,id',
            'checked' => 'required|boolean'
        ]);

        if ($request->checked) {
            // ✅ Simpan data jika checkbox dicentang
            DB::table('cpl_pl')->updateOrInsert([
                'cpl_id' => $request->cpl_id,
                'pl_id' => $request->pl_id
            ], [
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['success' => '✅ Data tersimpan!']);
        } else {
            // ❌ Hapus data jika checkbox dihapus
            DB::table('cpl_pl')
                ->where('cpl_id', $request->cpl_id)
                ->where('pl_id', $request->pl_id)
                ->delete();

            return response()->json(['success' => '❌ Data dihapus!']);
        }
    }
}
