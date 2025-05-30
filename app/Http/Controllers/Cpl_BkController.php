<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use App\Models\Bk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Cpl_BkController extends Controller
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
            $bks = Bk::where('kode_prodi', $kodeProdi)->get();

            $pemetaan = DB::table('cpl_bk')
                ->whereIn('cpl_id', $cpls->pluck('id'))
                ->whereIn('bk_id', $bks->pluck('id'))
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->cpl_id . '-' . $item->bk_id => true];
                });

            Log::info("Successfully loaded pemetaan CPL-BK for kode_prodi: {$kodeProdi}, CPL count: {$cpls->count()}, BK count: {$bks->count()}");

            return view('pemetaan_CPL-BK.index', compact('cpls', 'bks', 'pemetaan'));
        } catch (\Exception $e) {
            Log::error('Error loading pemetaan CPL-BK: ' . $e->getMessage());
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
                'bk_id' => 'required|exists:bk,id',
                'checked' => 'required|boolean',
            ]);

            $cpl = Cpl::where('id', $request->cpl_id)->where('kode_prodi', $kodeProdi)->first();
            $bk = Bk::where('id', $request->bk_id)->where('kode_prodi', $kodeProdi)->first();

            if (!$cpl || !$bk) {
                return response()->json(['error' => 'CPL atau BK tidak ditemukan atau tidak sesuai dengan prodi Anda.'], 403);
            }

            $checked = (bool) $request->checked;

            if ($checked) {
                DB::table('cpl_bk')->updateOrInsert(
                    [
                        'cpl_id' => $request->cpl_id,
                        'bk_id' => $request->bk_id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                Log::info("Pemetaan CPL-BK tersimpan: CPL ID {$request->cpl_id} - BK ID {$request->bk_id}");
                return response()->json(['success' => '✅ Data pemetaan tersimpan!']);
            } else {
                DB::table('cpl_bk')
                    ->where('cpl_id', $request->cpl_id)
                    ->where('bk_id', $request->bk_id)
                    ->delete();
                Log::info("Pemetaan CPL-BK dihapus: CPL ID {$request->cpl_id} - BK ID {$request->bk_id}");
                return response()->json(['success' => '❌ Data pemetaan dihapus!']);
            }
        } catch (\Exception $e) {
            Log::error('Error updating pemetaan CPL-BK: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan pemetaan: ' . $e->getMessage()], 500);
        }
    }
}