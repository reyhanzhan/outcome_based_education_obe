<?php

namespace App\Http\Controllers;

use App\Models\Mk;
use App\Models\Kelas;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PembobotanCpmkMkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!in_array($user->role, ['dosen', 'kps'])) {
                abort(403, 'Akses hanya untuk dosen atau KPS.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $user = Auth::user();
        $kodeProdi = $user->kode_prodi;
        if (is_null($kodeProdi)) {
            Log::warning('Kode prodi is null for user: ' . $user->email);
            return redirect()->route('/pembobotan')->with('error', 'Kode prodi tidak ditemukan untuk user ini.');
        }

        $tahunFilter = request('tahun', session('selected_year', ''));
        if (!$tahunFilter) {
            Log::warning('Selected year not found for user: ' . $user->email . ' and kode_prodi: ' . $kodeProdi);
            return redirect()->route('/pembobotan')->with('error', 'Tahun kurikulum tidak ditemukan. Pilih tahun terlebih dahulu.');
        }

        if (request()->has('tahun')) {
            session(['selected_year' => $tahunFilter]);
        }

        $kurikulumId = Kurikulum::where('kode_prodi', $kodeProdi)->where('tahun', $tahunFilter)->value('id');
        Log::info("Index - kodeProdi: {$kodeProdi}, tahunFilter: {$tahunFilter}, kurikulumId: {$kurikulumId}");

        try {
            $mks = collect();

            if ($user->role === 'dosen') {
                $nip = $user->nip;
                if (!$nip) {
                    Log::warning('NIP not found for user: ' . $user->email);
                    return redirect()->route('/pembobotan')->with('error', 'NIP tidak ditemukan.');
                }

                $kelas = Kelas::where('nip_dosen', $nip)
                    ->where('kode_prodi', $kodeProdi)
                    ->where('tahun', $tahunFilter)
                    ->with(['mk' => function ($query) use ($kodeProdi, $kurikulumId) {
                        $query->where('kode_prodi', $kodeProdi)
                            ->whereHas('kurikulum', function ($q) use ($kurikulumId) {
                                $q->where('id', $kurikulumId);
                            });
                    }])
                    ->get();

                $mks = $kelas->map(function ($item) {
                    return $item->mk;
                })->filter()->unique('id');
            } elseif ($user->role === 'kps') {
                $mks = Mk::where('kode_prodi', $kodeProdi)
                    ->whereHas('kelas', function ($query) use ($tahunFilter) {
                        $query->where('tahun', $tahunFilter);
                    })
                    ->whereHas('cpmks', function ($query) use ($kodeProdi, $kurikulumId) {
                        $query->where('kode_prodi', $kodeProdi)
                            ->where('kurikulum_id', $kurikulumId); // Filter berdasarkan kurikulum_id di cpmk
                    })
                    ->get();
            }

            $defaultMk = $mks->first();
            $cpmks = [];

            if ($defaultMk) {
                $cpmks = $defaultMk->cpmks()
                    ->where('kode_prodi', $kodeProdi)
                    ->where('kurikulum_id', $kurikulumId) // Filter berdasarkan kurikulum_id di cpmk
                    ->withPivot('bobot', 'kurikulum_id')
                    ->get()
                    ->map(function ($cpmk) use ($defaultMk, $kodeProdi, $tahunFilter, $kurikulumId) {
                        $teknikPenilaian = DB::table('teknik_penilaian')
                            ->where('mk_id', $defaultMk->id)
                            ->where('cpmk_id', $cpmk->id)
                            ->whereExists(function ($query) use ($kurikulumId) {
                                $query->select(DB::raw(1))
                                    ->from('cpmk_mk')
                                    ->whereColumn('cpmk_mk.mk_id', 'teknik_penilaian.mk_id')
                                    ->whereColumn('cpmk_mk.cpmk_id', 'teknik_penilaian.cpmk_id')
                                    ->where('cpmk_mk.kurikulum_id', $kurikulumId);
                            })
                            ->pluck('bobot', 'teknik')
                            ->toArray();

                        Log::info('CPMK Data (Index) for kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter . ', kurikulumId: ' . $kurikulumId . ': ' . json_encode([
                            'id' => $cpmk->id,
                            'kode_cpmk' => $cpmk->kode_cpmk,
                            'deskripsi' => $cpmk->deskripsi ?? 'Deskripsi tidak tersedia',
                            'bobot' => $cpmk->pivot->bobot ?? 0,
                            'teknik_penilaian' => $teknikPenilaian,
                        ]));

                        return (object) [
                            'id' => $cpmk->id,
                            'kode_cpmk' => $cpmk->kode_cpmk,
                            'deskripsi' => $cpmk->deskripsi ?? 'Deskripsi tidak tersedia',
                            'bobot' => $cpmk->pivot->bobot ?? 0,
                            'teknik_penilaian' => $teknikPenilaian,
                        ];
                    });
            }

            return view('pembobotan_cpmk_mk.index', compact('mks', 'cpmks', 'defaultMk', 'tahunFilter'));
        } catch (\Exception $e) {
            Log::error('Error loading pembobotan CPMK-MK for kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter . ', kurikulumId: ' . $kurikulumId . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data pembobotan: ' . $e->getMessage());
        }
    }

    public function searchMk(Request $request)
    {
        $user = Auth::user();
        $kodeProdi = $user->kode_prodi;
        $tahunFilter = request('tahun', session('selected_year', ''));
        if (is_null($kodeProdi) || !$tahunFilter) {
            return response()->json(['results' => []], 403);
        }

        try {
            $query = $request->input('q');
            $mksQuery = Mk::where('kode_prodi', $kodeProdi)->whereHas('cpmks');

            if ($user->role === 'dosen') {
                $nip = $user->nip;
                if (!$nip) {
                    return response()->json(['results' => []], 403);
                }

                $kelas = Kelas::where('nip_dosen', $nip)
                    ->where('kode_prodi', $kodeProdi)
                    ->where('tahun', $tahunFilter)
                    ->with(['mk' => function ($query) use ($kodeProdi) {
                        $query->where('kode_prodi', $kodeProdi);
                    }])
                    ->get();

                $mkIds = $kelas->pluck('mk.id')->filter()->unique();
                $mksQuery->whereIn('id', $mkIds);

                if ($mkIds->isEmpty()) {
                    Log::warning('No mata kuliah IDs found for NIP: ' . $nip . ', kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter);
                }
            } else {
                $mksQuery->whereHas('kelas', function ($query) use ($tahunFilter) {
                    $query->where('tahun', $tahunFilter); // Filter berdasarkan tahun via Kelas untuk KPS
                });
            }

            if ($query) {
                $mksQuery->where(function ($q) use ($query) {
                    $q->where('kode_mk', 'like', "%{$query}%")
                      ->orWhere('deskripsi', 'like', "%{$query}%");
                });
            }

            $mks = $mksQuery->get()->map(function ($mk) {
                return [
                    'id' => $mk->id,
                    'text' => "{$mk->kode_mk} - {$mk->deskripsi}",
                ];
            });

            Log::info('Search MK results for kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter . ', count: ' . $mks->count());
            return response()->json(['results' => $mks]);
        } catch (\Exception $e) {
            Log::error('Error in searchMk for kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter . ': ' . $e->getMessage());
            return response()->json(['results' => []], 500);
        }
    }

    public function getCpmks($mk_id)
    {
        $user = Auth::user();
        $kodeProdi = $user->kode_prodi;
        $tahunFilter = request('tahun', session('selected_year', ''));
        if (is_null($kodeProdi) || !$tahunFilter) {
            return response()->json(['error' => 'Kode prodi atau tahun tidak ditemukan.'], 403);
        }

        try {
            $mk = Mk::where('kode_prodi', $kodeProdi)
                ->whereHas('kelas', function ($query) use ($tahunFilter) {
                    $query->where('tahun', $tahunFilter); // Filter berdasarkan tahun via Kelas
                })
                ->with([
                    'cpmks' => function ($query) use ($kodeProdi) {
                        $query->where('kode_prodi', $kodeProdi)
                            ->select('cpmk.id', 'cpmk.kode_cpmk', 'cpmk.deskripsi')
                            ->withPivot('bobot');
                    }
                ])
                ->findOrFail($mk_id);

            if ($user->role === 'dosen') {
                $nip = $user->nip;
                if (!$nip) {
                    Log::warning('NIP not found for user: ' . $user->email);
                    return response()->json(['error' => 'NIP tidak ditemukan.'], 403);
                }

                $kelas = Kelas::where('nip_dosen', $nip)
                    ->where('kode_mk', $mk->kode_mk)
                    ->where('tahun', $tahunFilter)
                    ->exists();

                if (!$kelas) {
                    Log::warning('Dosen ' . $nip . ' tidak mengajar MK: ' . $mk->kode_mk . ' for kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter);
                    return response()->json(['error' => 'Anda tidak berhak mengakses pembobotan untuk mata kuliah ini.'], 403);
                }
            }

            $cpmks = $mk->cpmks->map(function ($cpmk) use ($mk_id, $kodeProdi, $tahunFilter) {
                $teknikPenilaian = DB::table('teknik_penilaian')
                    ->where('mk_id', $mk_id)
                    ->where('cpmk_id', $cpmk->id)
                    ->pluck('bobot', 'teknik')
                    ->toArray();

                Log::info('CPMK Data (getCpmks) for kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter . ': ' . json_encode([
                    'id' => $cpmk->id,
                    'kode_cpmk' => $cpmk->kode_cpmk,
                    'deskripsi' => $cpmk->deskripsi ?? 'Deskripsi tidak tersedia',
                    'bobot' => $cpmk->pivot->bobot ?? 0,
                    'teknik_penilaian' => $teknikPenilaian,
                    'mk_id' => $mk_id,
                ]));

                return [
                    'id' => $cpmk->id,
                    'kode_cpmk' => $cpmk->kode_cpmk,
                    'deskripsi' => $cpmk->deskripsi ?? 'Deskripsi tidak tersedia',
                    'bobot' => $cpmk->pivot->bobot ?? 0,
                    'teknik_penilaian' => $teknikPenilaian,
                ];
            });

            Log::info('Returned CPMKs for MK ' . $mk_id . ', kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter . ': ' . json_encode($cpmks));
            return response()->json($cpmks);
        } catch (\Exception $e) {
            Log::error('Error in getCpmks for mk_id ' . $mk_id . ', kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter . ': ' . $e->getMessage() . ', Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Gagal memuat CPMK: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $kodeProdi = $user->kode_prodi;
        $tahunFilter = request('tahun', session('selected_year', ''));
        if (is_null($kodeProdi) || !$tahunFilter) {
            return response()->json(['error' => 'Kode prodi atau tahun tidak ditemukan.'], 403);
        }

        try {
            $request->validate([
                'mk_id' => 'required|exists:mk,id',
                'teknikData' => 'required|array',
                'teknikData.*.cpmk_id' => 'required|exists:cpmk,id',
                'teknikData.*.teknik' => 'required|string',
                'teknikData.*.bobot' => 'required|integer|min:0|max:100',
            ]);

            $teknikDataGrouped = collect($request->teknikData)->groupBy('cpmk_id');
            $bobotCpmkMap = [];
            foreach ($teknikDataGrouped as $cpmk_id => $teknikItems) {
                $totalBobotTeknik = $teknikItems->sum('bobot');
                $bobotCpmkMap[$cpmk_id] = $totalBobotTeknik;
            }

            $totalBobot = array_sum($bobotCpmkMap);
            if ($totalBobot != 100) {
                return response()->json(['error' => 'Total bobot semua CPMK harus 100%!'], 422);
            }

            $mk_id = $request->mk_id;
            $mk = Mk::where('kode_prodi', $kodeProdi)
                ->whereHas('kelas', function ($query) use ($tahunFilter) {
                    $query->where('tahun', $tahunFilter); // Filter berdasarkan tahun via Kelas
                })
                ->findOrFail($mk_id);

            if ($user->role === 'dosen') {
                $nip = $user->nip;
                if (!$nip) {
                    Log::warning('NIP not found for user: ' . $user->email);
                    return response()->json(['error' => 'NIP tidak ditemukan.'], 403);
                }

                $kelas = Kelas::where('nip_dosen', $nip)
                    ->where('kode_mk', $mk->kode_mk)
                    ->where('tahun', $tahunFilter)
                    ->exists();

                if (!$kelas) {
                    Log::warning('Dosen ' . $nip . ' tidak mengajar MK: ' . $mk->kode_mk . ' for kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter);
                    return response()->json(['error' => 'Anda tidak berhak mengatur pembobotan untuk mata kuliah ini.'], 403);
                }
            }

            DB::beginTransaction();
            try {
                foreach ($bobotCpmkMap as $cpmk_id => $bobot) {
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
                                'min_standard' => 55,
                                'updated_at' => now()
                            ]);
                        Log::info('Updated bobot for mk_id: ' . $mk_id . ', cpmk_id: ' . $cpmk_id . ', bobot: ' . $bobot . ' for kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter);
                    } else {
                        DB::table('cpmk_mk')->insert([
                            'mk_id' => $mk_id,
                            'cpmk_id' => $cpmk_id,
                            'bobot' => $bobot,
                            'min_standard' => 55,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        Log::info('Inserted bobot for mk_id: ' . $mk_id . ', cpmk_id: ' . $cpmk_id . ', bobot: ' . $bobot . ' for kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter);
                    }
                }

                DB::table('teknik_penilaian')->where('mk_id', $mk_id)->delete();
                foreach ($request->teknikData as $teknik) {
                    $cpmk_id = $teknik['cpmk_id'];
                    $teknikNama = $teknik['teknik'];
                    $bobot = (int) $teknik['bobot'];

                    if ($bobot > 0) {
                        DB::table('teknik_penilaian')->insert([
                            'mk_id' => $mk_id,
                            'cpmk_id' => $cpmk_id,
                            'teknik' => $teknikNama,
                            'bobot' => $bobot,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        Log::info('Inserted teknik penilaian for mk_id: ' . $mk_id . ', cpmk_id: ' . $cpmk_id . ', teknik: ' . $teknikNama . ', bobot: ' . $bobot . ' for kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter);
                    }
                }

                DB::commit();
                Log::info('Bobot CPMK-MK and Teknik Penilaian updated/inserted for mk_id: ' . $request->mk_id . ', kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter);
                return response()->json(['success' => 'Data pembobotan tersimpan!']);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Transaction error in update for mk_id ' . $mk_id . ', kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter . ': ' . $e->getMessage() . ', Trace: ' . $e->getTraceAsString());
                return response()->json(['error' => 'Gagal menyimpan pembobotan: ' . $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error in update: ' . $e->getMessage() . ', Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Gagal menyimpan pembobotan: ' . $e->getMessage()], 500);
        }
    }
}