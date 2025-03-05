<?php

namespace App\Http\Controllers;

use App\Models\Mk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PembobotanCpmkMkController extends Controller
{
    public function index()
    {
        $mks = Mk::whereHas('cpmks')->get();
        $defaultMk = $mks->first();
        $cpmks = [];

        if ($defaultMk) {
            $cpmks = $defaultMk->cpmks()->withPivot('bobot')->get()->map(function ($cpmk) {
                Log::info('CPMK Data (Index): ' . json_encode([
                    'id' => $cpmk->id,
                    'kode_cpmk' => $cpmk->kode_cpmk,
                    'deskripsi' => $cpmk->deskripsi ?? 'Deskripsi tidak tersedia',
                    'bobot' => $cpmk->pivot->bobot ?? 0,
                ]));
                return (object) [
                    'id' => $cpmk->id,
                    'kode_cpmk' => $cpmk->kode_cpmk,
                    'deskripsi' => $cpmk->deskripsi ?? 'Deskripsi tidak tersedia',
                    'bobot' => $cpmk->pivot->bobot ?? 0,
                ];
            });
        }

        return view('pembobotan_cpmk_mk.index', compact('mks', 'cpmks', 'defaultMk'));
    }

    public function getCpmks($mk_id)
    {
        try {
            Log::info('Fetching CPMKs for mk_id: ' . $mk_id);
            $mk = Mk::with(['cpmks' => function ($query) {
                $query->select('cpmk.id', 'cpmk.kode_cpmk', 'cpmk.deskripsi')->withPivot('bobot');
            }])->findOrFail($mk_id);

            $cpmks = $mk->cpmks->map(function ($cpmk) use ($mk_id) {
                Log::info('CPMK Data (getCpmks): ' . json_encode([
                    'id' => $cpmk->id,
                    'kode_cpmk' => $cpmk->kode_cpmk,
                    'deskripsi' => $cpmk->deskripsi ?? 'Deskripsi tidak tersedia',
                    'bobot' => $cpmk->pivot->bobot ?? 0,
                    'mk_id' => $mk_id,
                ]));
                return [
                    'id' => $cpmk->id,
                    'kode_cpmk' => $cpmk->kode_cpmk,
                    'deskripsi' => $cpmk->deskripsi ?? 'Deskripsi tidak tersedia',
                    'bobot' => $cpmk->pivot->bobot ?? 0,
                ];
            });

            Log::info('Returned CPMKs for MK ' . $mk_id . ': ' . json_encode($cpmks));
            return response()->json($cpmks);
        } catch (\Exception $e) {
            Log::error('Error in getCpmks for mk_id ' . $mk_id . ': ' . $e->getMessage() . ', Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Gagal memuat CPMK: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'mk_id' => 'required|exists:mk,id',
                'bobotData' => 'required|array',
                'bobotData.*.bobot' => 'required|integer|min:0|max:100', // Validasi setiap bobot sebagai integer
                'bobotData.*.cpmk_id' => 'required|exists:cpmk,id', // Validasi cpmk_id
            ]);

            $totalBobot = array_sum(array_column($request->bobotData, 'bobot'));

            if ($totalBobot != 100) {
                return response()->json(['error' => 'Total bobot harus 100%!'], 422);
            }

            $mk_id = $request->mk_id;

            // Gunakan DB::table untuk menyimpan langsung ke tabel cpmk_mk, mirip dengan Cpmk_MkController
            // Pastkan tidak ada konflik dengan data dari Cpmk_MkController
            DB::beginTransaction(); // Mulai transaksi untuk memastkan konsistensi data
            try {
                foreach ($request->bobotData as $data) {
                    $cpmk_id = $data['cpmk_id'];
                    $bobot = (int) $data['bobot']; // Konversi bobot ke integer

                    // Cek apakah entri sudah ada untuk menghindari duplikat atau konflik
                    $existing = DB::table('cpmk_mk')
                        ->where('mk_id', $mk_id)
                        ->where('cpmk_id', $cpmk_id)
                        ->first();

                    if ($existing) {
                        DB::table('cpmk_mk')
                            ->where('mk_id', $mk_id)
                            ->where('cpmk_id', $cpmk_id)
                            ->update([
                                'bobot' => $bobot,
                                'min_standard' => 50, // Sesuaikan dengan default di Cpmk_MkController
                                'updated_at' => now()
                            ]);
                        Log::info('Updated bobot for mk_id: ' . $mk_id . ', cpmk_id: ' . $cpmk_id . ', bobot: ' . $bobot);
                    } else {
                        DB::table('cpmk_mk')->insert([
                            'mk_id' => $mk_id,
                            'cpmk_id' => $cpmk_id,
                            'bobot' => $bobot,
                            'min_standard' => 50, // Sesuaikan dengan default di Cpmk_MkController
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        Log::info('Inserted bobot for mk_id: ' . $mk_id . ', cpmk_id: ' . $cpmk_id . ', bobot: ' . $bobot);
                    }

                    // Verifikasi penyimpanan di database
                    $pivotEntry = DB::table('cpmk_mk')
                        ->where('mk_id', $mk_id)
                        ->where('cpmk_id', $cpmk_id)
                        ->first();
                    Log::info('Pivot entry after save for mk_id: ' . $mk_id . ', cpmk_id: ' . $cpmk_id . ': ' . json_encode($pivotEntry));
                }

                DB::commit(); // Commit transaksi jika berhasil
                Log::info('Bobot CPMK-MK updated/inserted for mk_id: ' . $request->mk_id . ', bobotData: ' . json_encode($request->bobotData));

                return response()->json(['success' => 'Data pembobotan tersimpan!']);
            } catch (\Exception $e) {
                DB::rollBack(); // Rollback transaksi jika ada error
                Log::error('Transaction error in update for mk_id ' . $mk_id . ': ' . $e->getMessage() . ', Trace: ' . $e->getTraceAsString());
                return response()->json(['error' => 'Gagal menyimpan pembobotan: ' . $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error in update: ' . $e->getMessage() . ', Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Gagal menyimpan pembobotan: ' . $e->getMessage()], 500);
        }
    }
}