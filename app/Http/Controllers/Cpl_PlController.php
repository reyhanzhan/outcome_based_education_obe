<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use App\Models\Pl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Cpl_PlController extends Controller
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

            $cpls = Cpl::where('kode_prodi', $kodeProdi)->get();
            $pls = Pl::where('kode_prodi', $kodeProdi)->get();

            $pemetaan = DB::table('cpl_pl')
                ->whereIn('cpl_id', $cpls->pluck('id'))
                ->whereIn('pl_id', $pls->pluck('id'))
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->cpl_id . '-' . $item->pl_id => true];
                });

            Log::info("Successfully loaded pemetaan CPL-PL for kode_prodi: {$kodeProdi}, CPL count: {$cpls->count()}, PL count: {$pls->count()}");

            return view('pemetaan_CPL-PL.index', compact('cpls', 'pls', 'pemetaan'));
        } catch (\Exception $e) {
            Log::error('Error loading pemetaan CPL-PL: ' . $e->getMessage());
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
                'cpl_id' => 'required|exists:cpl,id',
                'pl_id' => 'required|exists:pl,id',
                'checked' => 'required|boolean',
            ]);

            $cpl = Cpl::where('id', $request->cpl_id)->where('kode_prodi', $kodeProdi)->first();
            $pl = Pl::where('id', $request->pl_id)->where('kode_prodi', $kodeProdi)->first();

            if (!$cpl || !$pl) {
                return response()->json(['error' => 'CPL atau PL tidak ditemukan atau tidak sesuai dengan prodi Anda.'], 403);
            }

            // Langsung gunakan nilai checked setelah validasi
            $checked = (bool) $request->checked;

            if ($checked) {
                DB::table('cpl_pl')->updateOrInsert(
                    [
                        'cpl_id' => $request->cpl_id,
                        'pl_id' => $request->pl_id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                Log::info("Pemetaan CPL-PL tersimpan: CPL ID {$request->cpl_id} - PL ID {$request->pl_id}");
                return response()->json(['success' => '✅ Data pemetaan tersimpan!']);
            } else {
                DB::table('cpl_pl')
                    ->where('cpl_id', $request->cpl_id)
                    ->where('pl_id', $request->pl_id)
                    ->delete();
                Log::info("Pemetaan CPL-PL dihapus: CPL ID {$request->cpl_id} - PL ID {$request->pl_id}");
                return response()->json(['success' => '❌ Data pemetaan dihapus!']);
            }
        } catch (\Exception $e) {
            Log::error('Error updating pemetaan CPL-PL: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan pemetaan: ' . $e->getMessage()], 500);
        }
    }
}