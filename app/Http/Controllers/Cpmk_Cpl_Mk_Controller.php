<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Cpmk;
use App\Models\Mk;
use Illuminate\Support\Facades\DB;
use App\Models\Kurikulum;

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

            $tahunFilter = request('tahun', session('selected_year', ''));
            if ($tahunFilter) {
                session(['selected_year' => $tahunFilter]);
            } else {
                $tahunFilter = session('selected_year', '');
            }
            Log::info("Index - kodeProdi: {$kodeProdi}, tahunFilter: {$tahunFilter}");

            $kurikulumId = $tahunFilter ? Kurikulum::where('kode_prodi', $kodeProdi)->where('tahun', $tahunFilter)->value('id') : null;
            Log::info("Index - kurikulumId: {$kurikulumId}");

            $cpls = Cpl::where('kode_prodi', $kodeProdi)
                ->whereHas('cpmks', function ($query) use ($kodeProdi, $kurikulumId) {
                    $query->where('kode_prodi', $kodeProdi)
                        ->whereHas('mks', function ($subQuery) use ($kodeProdi, $kurikulumId) {
                            $subQuery->where('kode_prodi', $kodeProdi);
                            if ($kurikulumId) {
                                $subQuery->whereHas('cpmkMks', function ($q) use ($kurikulumId) {
                                    $q->where('cpmk_mk.kurikulum_id', $kurikulumId); // Kualifikasi tabel
                                });
                            }
                        })
                        ->whereHas('cplCpmks', function ($q) use ($kurikulumId) {
                            if ($kurikulumId) {
                                $q->where('cpmk_cpl.kurikulum_id', $kurikulumId); // Kualifikasi tabel
                            }
                        });
                })
                ->with([
                    'cpmks' => function ($query) use ($kodeProdi, $kurikulumId) {
                        $query->where('kode_prodi', $kodeProdi)
                            ->whereHas('mks')
                            ->with(['mks' => function ($subQuery) use ($kodeProdi, $kurikulumId) {
                                $subQuery->where('kode_prodi', $kodeProdi)->distinct();
                                if ($kurikulumId) {
                                    $subQuery->whereHas('cpmkMks', function ($q) use ($kurikulumId) {
                                        $q->where('cpmk_mk.kurikulum_id', $kurikulumId); // Kualifikasi tabel
                                    });
                                }
                            }])
                            ->whereHas('cplCpmks', function ($q) use ($kurikulumId) {
                                if ($kurikulumId) {
                                    $q->where('cpmk_cpl.kurikulum_id', $kurikulumId); // Kualifikasi tabel
                                }
                            });
                    },
                ])
                ->get();

            Log::info("Successfully loaded pemetaan CPMK-CPL-MK for kode_prodi: {$kodeProdi}, tahun: {$tahunFilter}, kurikulum_id: {$kurikulumId}, CPL count: {$cpls->count()}");

            return view('pemetaan_CPMK-CPL-MK.index', compact('cpls', 'tahunFilter'));
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

            $tahunFilter = session('selected_year', '');
            $kurikulumId = $tahunFilter ? Kurikulum::where('kode_prodi', $kodeProdi)->where('tahun', $tahunFilter)->value('id') : null;

            $mappings = $request->input('mappings', []);

            foreach ($mappings as $mapping) {
                [$cplId, $cpmkId, $mkId] = explode('|', $mapping);

                $cpl = Cpl::where('id', $cplId)->where('kode_prodi', $kodeProdi)->first();
                $cpmk = Cpmk::where('id', $cpmkId)->where('kode_prodi', $kodeProdi)->first();
                $mk = Mk::where('id', $mkId)->where('kode_prodi', $kodeProdi)->first();

                if ($cpl && $cpmk && $mk) {
                    DB::table('cpmk_cpl_mk')->updateOrInsert(
                        ['cpl_id' => $cplId, 'cpmk_id' => $cpmkId, 'mk_id' => $mkId],
                        [
                            'kurikulum_id' => $kurikulumId,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                }
            }

            Log::info("Pemetaan CPMK-CPL-MK stored for kode_prodi: {$kodeProdi}, tahun: {$tahunFilter}, kurikulum_id: {$kurikulumId}, mappings count: " . count($mappings));
            return redirect()->route('teknik_penilaian.index')->with('success', 'Pemetaan berhasil disimpan!');
        } catch (\Exception $e) {
            Log::error('Error storing pemetaan CPMK-CPL-MK: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan pemetaan: ' . $e->getMessage());
        }
    }
}