<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Cpmk;
use App\Models\Mk;
use Illuminate\Support\Facades\DB;

class Cpmk_Cpl_Mk_Controller extends Controller
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

            $cpls = Cpl::where('kode_prodi', $kodeProdi)
                ->whereHas('cpmks', function ($query) use ($kodeProdi) {
                    $query->where('kode_prodi', $kodeProdi)
                        ->whereHas('mks', function ($subQuery) use ($kodeProdi) {
                            $subQuery->where('kode_prodi', $kodeProdi);
                        });
                })
                ->with([
                    'cpmks' => function ($query) use ($kodeProdi) {
                        $query->where('kode_prodi', $kodeProdi)
                            ->whereHas('mks')
                            ->with(['mks' => function ($subQuery) use ($kodeProdi) {
                                $subQuery->where('kode_prodi', $kodeProdi)->distinct();
                            }]);
                    },
                ])
                ->get();

            Log::info("Successfully loaded pemetaan CPMK-CPL-MK for kode_prodi: {$kodeProdi}, CPL count: {$cpls->count()}");

            return view('pemetaan_CPMK-CPL-MK.index', compact('cpls'));
        } catch (\Exception $e) {
            Log::error('Error loading pemetaan CPMK-CPL-MK: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data pemetaan: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan.');
            }

            $mappings = $request->input('mappings', []);

            foreach ($mappings as $mapping) {
                [$cplId, $cpmkId, $mkId] = explode('|', $mapping);

                $cpl = Cpl::where('id', $cplId)->where('kode_prodi', $kodeProdi)->first();
                $cpmk = Cpmk::where('id', $cpmkId)->where('kode_prodi', $kodeProdi)->first();
                $mk = Mk::where('id', $mkId)->where('kode_prodi', $kodeProdi)->first();

                if ($cpl && $cpmk && $mk) {
                    DB::table('cpmk_cpl_mk')->updateOrInsert(
                        ['cpl_id' => $cplId, 'cpmk_id' => $cpmkId, 'mk_id' => $mkId],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

            Log::info("Pemetaan CPMK-CPL-MK stored for kode_prodi: {$kodeProdi}, mappings count: " . count($mappings));
            return redirect()->route('teknik_penilaian.index')->with('success', 'Pemetaan berhasil disimpan!');
        } catch (\Exception $e) {
            Log::error('Error storing pemetaan CPMK-CPL-MK: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan pemetaan: ' . $e->getMessage());
        }
    }
}