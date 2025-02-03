<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use App\Models\Mk;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Cpl_MkController extends Controller
{
    public function index()
    {
        $cpls = Cpl::all();
        $mks = Mk::all();
        return view('pemetaan_CPL-MK.index', compact('cpls', 'mks'));
    }

    public function update(Request $request)
    {
        // Debug untuk melihat request masuk
        Log::info('Request Data:', $request->all());
        $request->validate([
            'cpl_id' => 'required|exists:cpl,id',
            'mk_id' => 'required|exists:mk,id',
            'checked' => 'required|boolean'
        ]);

        if ($request->checked) {
            // ✅ Simpan data jika checkbox dicentang
            DB::table('cpl_mk')->updateOrInsert([
                'cpl_id' => $request->cpl_id,
                'mk_id' => $request->mk_id
            ], [
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['success' => '✅ Data tersimpan!']);
        } else {
            // ❌ Hapus data jika checkbox dihapus
            DB::table('cpl_mk')
                ->where('cpl_id', $request->cpl_id)
                ->where('mk_id', $request->mk_id)
                ->delete();

            return response()->json(['success' => '❌ Data dihapus!']);
        }
    }
}
