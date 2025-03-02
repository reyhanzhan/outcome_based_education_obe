<?php

namespace App\Http\Controllers;

use App\Models\Mk;
use App\Models\Cpmk;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class Cpmk_MkController extends Controller
{
    public function index()
    {
        $cpmks = Cpmk::with(['mks' => function ($query) {
            $query->withPivot('bobot', 'min_standard');
        }])->get(); // Ambil CPMK beserta MK terkait dengan pivot bobot dan min_standard
        $mks = Mk::all();
        return view('pemetaan_CPMK-MK.index', compact('cpmks', 'mks'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'mk_id' => 'required|exists:mk,id',
            'cpmk_id' => 'required|exists:cpmk,id',
            'checked' => 'required|boolean',
            'bobot' => 'nullable|numeric|min:0|max:100',
            'min_standard' => 'nullable|numeric|min:0|max:100'
        ]);

        $mk_id = $request->mk_id;
        $cpmk_id = $request->cpmk_id;
        $bobot = $request->bobot ?? 0;
        $min_standard = $request->min_standard ?? 50;

        if ($request->checked) {
            // Simpan data jika checkbox dicentang
            DB::table('cpmk_mk')->updateOrInsert(
                [
                    'mk_id' => $mk_id,
                    'cpmk_id' => $cpmk_id
                ],
                [
                    'bobot' => $bobot,
                    'min_standard' => $min_standard,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            return response()->json(['success' => '✅ Data tersimpan!']);
        } else {
            // Hapus data jika checkbox dihapus
            DB::table('cpmk_mk')
                ->where('mk_id', $mk_id)
                ->where('cpmk_id', $cpmk_id)
                ->delete();

            return response()->json(['success' => '❌ Data dihapus!']);
        }
    }

    public function getCpmks($mk_id)
    {
        $mk = Mk::findOrFail($mk_id);
        $cpmks = $mk->cpmks()->withPivot('bobot', 'min_standard')->get()->map(function ($cpmk) {
            return [
                'id' => $cpmk->id,
                'kode_cpmk' => $cpmk->kode_cpmk,
                'bobot' => $cpmk->pivot->bobot,
                'min_standard' => $cpmk->pivot->min_standard
            ];
        });

        return response()->json($cpmks);
    }
}