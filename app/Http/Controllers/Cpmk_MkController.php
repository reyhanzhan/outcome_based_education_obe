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
            'bobot' => 'nullable|integer|min:0|max:100',
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

            // Pastkan data CPMK konsisten (opsional, jika perlu sinkronisasi deskripsi)
            $cpmk = Cpmk::find($cpmk_id);
            if ($cpmk && !$cpmk->deskripsi) {
                $cpmk->deskripsi = 'Deskripsi tidak tersedia'; // Fallback jika deskripsi kosong
                $cpmk->save();
            }


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
        $mk = Mk::with(['cpmks' => function ($query) {
            $query->select('cpmk.id', 'cpmk.kode_cpmk', 'cpmk.deskripsi')->withPivot('bobot', 'min_standard');
        }])->findOrFail($mk_id);

        $cpmks = $mk->cpmks->map(function ($cpmk) {
            return [
                'id' => $cpmk->id,
                'kode_cpmk' => $cpmk->kode_cpmk,
                'deskripsi' => $cpmk->deskripsi ?? 'Deskripsi tidak tersedia', // Pastkan deskripsi selalu ada
                'bobot' => $cpmk->pivot->bobot ?? 0,
                'min_standard' => $cpmk->pivot->min_standard ?? 50
            ];
        });

        return response()->json($cpmks);
    }

}