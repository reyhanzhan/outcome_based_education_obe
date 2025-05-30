<?php

namespace App\Http\Controllers;

use App\Models\Cpmk;
use App\Models\Cpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Cpmk_CplController extends Controller
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
            $cpls = Cpl::where('kode_prodi', $kodeProdi)->get();

            $pemetaan = DB::table('cpmk_cpl')
                ->whereIn('cpmk_id', $cpmks->pluck('id'))
                ->whereIn('cpl_id', $cpls->pluck('id'))
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->cpmk_id . '-' . $item->cpl_id => true];
                });

            Log::info("Successfully loaded pemetaan CPMK-CPL for kode_prodi: {$kodeProdi}, CPMK count: {$cpmks->count()}, CPL count: {$cpls->count()}");

            return view('pemetaan_CPMK-CPL.index', compact('cpmks', 'cpls', 'pemetaan'));
        } catch (\Exception $e) {
            Log::error('Error loading pemetaan CPMK-CPL: ' . $e->getMessage());
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
                'cpmk_id' => 'required|exists:cpmk,id',
                'cpl_id' => 'required|exists:cpl,id',
                'checked' => 'required|boolean',
            ]);

            $cpmk = Cpmk::where('id', $request->cpmk_id)->where('kode_prodi', $kodeProdi)->first();
            $cpl = Cpl::where('id', $request->cpl_id)->where('kode_prodi', $kodeProdi)->first();

            if (!$cpmk || !$cpl) {
                return response()->json(['error' => 'CPMK atau CPL tidak ditemukan atau tidak sesuai dengan prodi Anda.'], 403);
            }

            $checked = (bool) $request->checked;

            if ($checked) {
                DB::table('cpmk_cpl')->updateOrInsert(
                    [
                        'cpmk_id' => $request->cpmk_id,
                        'cpl_id' => $request->cpl_id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                Log::info("Pemetaan CPMK-CPL tersimpan: CPMK ID {$request->cpmk_id} - CPL ID {$request->cpl_id}");
                return response()->json(['success' => '✅ Data pemetaan tersimpan!']);
            } else {
                DB::table('cpmk_cpl')
                    ->where('cpmk_id', $request->cpmk_id)
                    ->where('cpl_id', $request->cpl_id)
                    ->delete();
                Log::info("Pemetaan CPMK-CPL dihapus: CPMK ID {$request->cpmk_id} - CPL ID {$request->cpl_id}");
                return response()->json(['success' => '❌ Data pemetaan dihapus!']);
            }
        } catch (\Exception $e) {
            Log::error('Error updating pemetaan CPMK-CPL: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan pemetaan: ' . $e->getMessage()], 500);
        }
    }
}