<?php

namespace App\Http\Controllers;

use App\Models\Mk;
use App\Models\Cpmk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Cpmk_MkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->role !== 'kps') {
                abort(403, 'Akses hanya untuk KPS.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini. Hubungi admin.');
            }

            $cpmks = Cpmk::where('kode_prodi', $kodeProdi)->get();
            $mks = Mk::where('kode_prodi', $kodeProdi)->get();

            $pemetaan = DB::table('cpmk_mk')
                ->whereIn('cpmk_id', $cpmks->pluck('id'))
                ->whereIn('mk_id', $mks->pluck('id'))
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->cpmk_id . '-' . $item->mk_id => [
                        'bobot' => $item->bobot,
                        'min_standard' => $item->min_standard
                    ]];
                });

            Log::info("Successfully loaded pemetaan CPMK-MK for kode_prodi: {$kodeProdi}, CPMK count: {$cpmks->count()}, MK count: {$mks->count()}");

            return view('pemetaan_CPMK-MK.index', compact('cpmks', 'mks', 'pemetaan'));
        } catch (\Exception $e) {
            Log::error('Error loading pemetaan CPMK-MK: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data pemetaan: ' . $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                return response()->json(['error' => 'Kode prodi tidak ditemukan.'], 403);
            }

            Log::info('Received data: ' . json_encode($request->all()));

            $request->validate([
                'mk_id' => 'required|exists:mk,id',
                'cpmk_id' => 'required|exists:cpmk,id',
                'checked' => 'required|boolean',
                'bobot' => 'nullable|integer|min:0|max:100',
                'min_standard' => 'nullable|numeric|min:0|max:100'
            ]);

            $mk = Mk::where('id', $request->mk_id)->where('kode_prodi', $kodeProdi)->first();
            $cpmk = Cpmk::where('id', $request->cpmk_id)->where('kode_prodi', $kodeProdi)->first();

            if (!$mk || !$cpmk) {
                return response()->json(['error' => 'MK atau CPMK tidak ditemukan atau tidak sesuai dengan prodi Anda.'], 403);
            }

            $bobot = $request->bobot ?? 0;
            $min_standard = $request->min_standard ?? 50;
            $checked = (bool) $request->checked;

            if ($checked) {
                DB::table('cpmk_mk')->updateOrInsert(
                    [
                        'mk_id' => $request->mk_id,
                        'cpmk_id' => $request->cpmk_id,
                    ],
                    [
                        'bobot' => $bobot,
                        'min_standard' => $min_standard,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // Pastikan data CPMK konsisten
                if (!$cpmk->deskripsi) {
                    $cpmk->deskripsi = 'Deskripsi tidak tersedia';
                    $cpmk->save();
                }

                Log::info("Pemetaan CPMK-MK tersimpan: CPMK ID {$request->cpmk_id} - MK ID {$request->mk_id}, Bobot: {$bobot}, Min Standard: {$min_standard}");
                return response()->json(['success' => '✅ Data pemetaan tersimpan!']);
            } else {
                DB::table('cpmk_mk')
                    ->where('mk_id', $request->mk_id)
                    ->where('cpmk_id', $request->cpmk_id)
                    ->delete();

                Log::info("Pemetaan CPMK-MK dihapus: CPMK ID {$request->cpmk_id} - MK ID {$request->mk_id}");
                return response()->json(['success' => '❌ Data pemetaan dihapus!']);
            }
        } catch (\Exception $e) {
            Log::error('Error updating pemetaan CPMK-MK: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan pemetaan: ' . $e->getMessage()], 500);
        }
    }

    public function getCpmks($mk_id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                return response()->json(['error' => 'Kode prodi tidak ditemukan.'], 403);
            }

            $mk = Mk::where('id', $mk_id)
                ->where('kode_prodi', $kodeProdi)
                ->with(['cpmks' => function ($query) {
                    $query->select('cpmk.id', 'cpmk.kode_cpmk', 'cpmk.deskripsi')
                        ->withPivot('bobot', 'min_standard');
                }])
                ->firstOrFail();

            $cpmks = $mk->cpmks->map(function ($cpmk) {
                return [
                    'id' => $cpmk->id,
                    'kode_cpmk' => $cpmk->kode_cpmk,
                    'deskripsi' => $cpmk->deskripsi ?? 'Deskripsi tidak tersedia',
                    'bobot' => $cpmk->pivot->bobot ?? 0,
                    'min_standard' => $cpmk->pivot->min_standard ?? 50
                ];
            });

            Log::info("Successfully retrieved CPMKs for MK ID {$mk_id}, count: {$cpmks->count()}");
            return response()->json($cpmks);
        } catch (\Exception $e) {
            Log::error('Error retrieving CPMKs for MK ID {$mk_id}: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data CPMK: ' . $e->getMessage()], 500);
        }
    }
}