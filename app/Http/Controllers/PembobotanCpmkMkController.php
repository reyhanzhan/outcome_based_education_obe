<?php

namespace App\Http\Controllers;

use App\Models\Mk;
use App\Models\Cpmk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembobotanCpmkMkController extends Controller
{
    public function index()
    {
        // Ambil semua MK yang memiliki CPMK
        $mks = Mk::whereHas('cpmks')->get();

        // Pilih MK default (misalnya, MK pertama)
        $defaultMk = $mks->first();
        $cpmks = [];

        if ($defaultMk) {
            // Ambil CPMK terkait dengan MK default beserta bobot dan deskripsi
            $cpmks = DB::table('cpmk_mk')
                ->where('mk_id', $defaultMk->id)
                ->join('cpmk', 'cpmk_mk.cpmk_id', '=', 'cpmk.id')
                ->select('cpmk.id', 'cpmk.kode_cpmk', 'cpmk.deskripsi', 'cpmk_mk.bobot')
                ->get()
                ->map(function ($cpmk) {
                    // Jika bobot null, gunakan 0 sebagai default
                    $cpmk->bobot = $cpmk->bobot ?? 0;
                    return $cpmk;
                });
        }

        return view('pembobotan_cpmk_mk.index', compact('mks', 'cpmks', 'defaultMk'));
    }

    public function getCpmks($mk_id)
    {
        // Ambil CPMK terkait dengan MK beserta bobot dan deskripsi
        $cpmks = DB::table('cpmk_mk')
            ->where('mk_id', $mk_id)
            ->join('cpmk', 'cpmk_mk.cpmk_id', '=', 'cpmk.id')
            ->select('cpmk.id', 'cpmk.kode_cpmk', 'cpmk.deskripsi', 'cpmk_mk.bobot')
            ->get()
            ->map(function ($cpmk) {
                $cpmk->bobot = $cpmk->bobot ?? 0;
                return $cpmk;
            });

        return response()->json($cpmks);
    }

    public function update(Request $request)
    {
        $request->validate([
            'mk_id' => 'required|exists:mk,id',
            'bobotData' => 'required|array',
        ]);

        // Hitung total bobot
        $totalBobot = array_sum(array_column($request->bobotData, 'bobot'));

        if ($totalBobot != 100) {
            return response()->json(['error' => 'Total bobot harus 100%!'], 422);
        }

        foreach ($request->bobotData as $data) {
            DB::table('cpmk_mk')->updateOrInsert(
                [
                    'mk_id' => $request->mk_id,
                    'cpmk_id' => $data['cpmk_id']
                ],
                [
                    'bobot' => $data['bobot'],
                    'updated_at' => now()
                ]
            );
        }

        return response()->json(['success' => 'Bobot CPMK-MK berhasil diperbarui!']);
    }
}