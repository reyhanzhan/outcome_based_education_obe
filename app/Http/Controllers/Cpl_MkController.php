<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use App\Models\Mk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Cpl_MkController extends Controller
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
            $mks = Mk::where('kode_prodi', $kodeProdi)->get();

            $pemetaan = DB::table('cpl_mk')
                ->whereIn('cpl_id', $cpls->pluck('id'))
                ->whereIn('mk_id', $mks->pluck('id'))
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->cpl_id . '-' . $item->mk_id => true];
                });

            Log::info("Successfully loaded pemetaan CPL-MK for kode_prodi: {$kodeProdi}, CPL count: {$cpls->count()}, MK count: {$mks->count()}");

            return view('pemetaan_CPL-MK.index', compact('cpls', 'mks', 'pemetaan'));
        } catch (\Exception $e) {
            Log::error('Error loading pemetaan CPL-MK: ' . $e->getMessage());
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
                'mk_id' => 'required|exists:mk,id',
                'checked' => 'required|boolean',
            ]);

            $cpl = Cpl::where('id', $request->cpl_id)->where('kode_prodi', $kodeProdi)->first();
            $mk = Mk::where('id', $request->mk_id)->where('kode_prodi', $kodeProdi)->first();

            if (!$cpl || !$mk) {
                return response()->json(['error' => 'CPL atau MK tidak ditemukan atau tidak sesuai dengan prodi Anda.'], 403);
            }

            $checked = (bool) $request->checked;

            if ($checked) {
                DB::table('cpl_mk')->updateOrInsert(
                    [
                        'cpl_id' => $request->cpl_id,
                        'mk_id' => $request->mk_id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                Log::info("Pemetaan CPL-MK tersimpan: CPL ID {$request->cpl_id} - MK ID {$request->mk_id}");
                return response()->json(['success' => '✅ Data pemetaan tersimpan!']);
            } else {
                DB::table('cpl_mk')
                    ->where('cpl_id', $request->cpl_id)
                    ->where('mk_id', $request->mk_id)
                    ->delete();
                Log::info("Pemetaan CPL-MK dihapus: CPL ID {$request->cpl_id} - MK ID {$request->mk_id}");
                return response()->json(['success' => '❌ Data pemetaan dihapus!']);
            }
        } catch (\Exception $e) {
            Log::error('Error updating pemetaan CPL-MK: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan pemetaan: ' . $e->getMessage()], 500);
        }
    }
}