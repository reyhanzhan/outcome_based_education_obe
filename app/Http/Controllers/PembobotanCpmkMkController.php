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

    public function index($kode_prodi = null, $tahun = null)
    {
        $user = Auth::user();
        $kodeProdi = $kode_prodi ?? $user->kode_prodi;

        if (is_null($kodeProdi)) {
            Log::warning('Kode prodi is null for user: ' . $user->email);
            return redirect()->route('pembobotan')->with('error', 'Kode prodi tidak ditemukan untuk user ini.');
        }

        // Prioritaskan tahun dari parameter URL, lalu session, lalu fallback ke tahun terbaru
        $tahunFilter = $tahun ?? session('selected_year', Kurikulum::where('kode_prodi', $kodeProdi)->max('tahun') ?? date('Y'));
        session(['selected_year' => $tahunFilter]); // Simpan ke sesi

        // Ambil kurikulum ID berdasarkan tahun dan kode prodi
        $kurikulumId = Kurikulum::where('kode_prodi', $kodeProdi)
            ->where('tahun', $tahunFilter)
            ->value('id');
        if (!$kurikulumId) {
            Log::warning("No kurikulumId found for kode_prodi: {$kodeProdi}, tahunFilter: {$tahunFilter}");
            $kurikulumId = Kurikulum::where('kode_prodi', $kodeProdi)->latest('id')->value('id');
            if (!$kurikulumId) {
                return redirect()->route('pembobotan.index')->with('error', 'Kurikulum tidak ditemukan untuk prodi ini.');
            }
            Log::info("Fallback to latest kurikulumId: {$kurikulumId} for kode_prodi: {$kodeProdi}");
        }

        Log::info("Index - kodeProdi: {$kodeProdi}, tahunFilter: {$tahunFilter}, kurikulumId: {$kurikulumId}");

        try {
            $mks = collect();

            if ($user->role === 'dosen') {
                $nip = $user->nip;
                if (!$nip) {
                    Log::warning('NIP not found for user: ' . $user->email);
                    return redirect()->route('pembobotan.index')->with('error', 'NIP tidak ditemukan.');
                }

                $kelas = Kelas::where('nip_dosen', $nip)
                    ->where('kode_prodi', $kodeProdi)
                    ->with([
                        'mk' => function ($query) use ($kodeProdi, $kurikulumId) {
                            $query->where('kode_prodi', $kodeProdi)
                                ->whereHas('cpmks', function ($q) use ($kurikulumId) {
                                    $q->where('kurikulum_id', $kurikulumId);
                                });
                        }
                    ])
                    ->get();

                $mks = $kelas->pluck('mk')->flatten()->filter()->unique('id');
            } elseif ($user->role === 'kps') {
                $mks = Mk::where('kode_prodi', $kodeProdi)
                    ->whereHas('cpmks', function ($query) use ($kurikulumId) {
                        $query->where('kurikulum_id', $kurikulumId);
                    })
                    ->get();
            }

            $defaultMk = $mks->first(); // Pilih MK pertama sebagai default
            $cpmks = [];

            if ($defaultMk) {
                $cpmks = $defaultMk->cpmks()
                    ->where('kode_prodi', $kodeProdi)
                    ->where('kurikulum_id', $kurikulumId)
                    ->withPivot('bobot', 'kurikulum_id')
                    ->get()
                    ->map(function ($cpmk) use ($defaultMk, $kodeProdi, $kurikulumId) {
                        $teknikPenilaianRaw = DB::table('teknik_penilaian')
                            ->where('mk_id', $defaultMk->id)
                            ->where('cpmk_id', $cpmk->id)
                            ->where('kurikulum_id', $kurikulumId)
                            ->get();
                        Log::info('Raw teknik_penilaian for mk_id: ' . $defaultMk->id . ', cpmk_id: ' . $cpmk->id . ', kurikulum_id: ' . $kurikulumId . ': ' . json_encode($teknikPenilaianRaw));
                        $teknikPenilaian = $teknikPenilaianRaw->pluck('bobot', 'teknik')->toArray();

                        Log::info('CPMK Data (Index) for kode_prodi: ' . $kodeProdi . ', kurikulumId: ' . $kurikulumId . ': ' . json_encode([
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

            $kurikulumOptions = Kurikulum::where('kode_prodi', $kodeProdi)
                ->distinct('tahun')
                ->pluck('tahun')
                ->sortDesc()
                ->values()
                ->all();


            return view('pembobotan_cpmk_mk.index', compact('mks', 'cpmks', 'defaultMk', 'kurikulumOptions', 'tahunFilter'));
        } catch (\Exception $e) {
            Log::error('Error loading pembobotan CPMK-MK for kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter . ', kurikulumId: ' . $kurikulumId . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data pembobotan: ' . $e->getMessage());
        }
    }

    // Method untuk memperbarui session tahun
    public function updateSessionYear(Request $request)
    {
        $request->validate(['tahun' => 'required|integer']);
        session(['selected_year' => $request->tahun]);
        return response()->json(['success' => true]);
    }

    public function searchMk(Request $request)
{
    $user = Auth::user();
    $kodeProdi = $user->kode_prodi;
    if (is_null($kodeProdi)) {
        return response()->json(['results' => []], 403);
    }

    $tahunFilter = $request->input('tahun', session('selected_year', ''));
    if (!$tahunFilter) {
        $tahunFilter = Kurikulum::where('kode_prodi', $kodeProdi)->max('tahun') ?? date('Y');
        session(['selected_year' => $tahunFilter]);
        Log::info("No selected year found, defaulting to latest year: {$tahunFilter} for user: {$user->email}");
    }

    $kurikulumId = Kurikulum::where('kode_prodi', $kodeProdi)
        ->where('tahun', $tahunFilter)
        ->value('id');
    if (!$kurikulumId) {
        Log::warning("No kurikulumId found for kode_prodi: {$kodeProdi}, tahunFilter: {$tahunFilter}");
        $kurikulumId = Kurikulum::where('kode_prodi', $kodeProdi)->latest('tahun')->value('id');
        if (!$kurikulumId) {
            return response()->json(['results' => []], 403);
        }
        Log::info("Fallback to latest kurikulumId: {$kurikulumId} for kode_prodi: {$kodeProdi}");
    }

    try {
        $query = $request->input('q');
        $mksQuery = Mk::where('kode_prodi', $kodeProdi)
            ->whereHas('cpmks', function ($query) use ($kurikulumId) {
                $query->where('kurikulum_id', $kurikulumId);
            });

        if ($user->role === 'dosen') {
            $nip = $user->nip;
            if (!$nip) {
                return response()->json(['results' => []], 403);
            }

            $kelas = Kelas::where('nip_dosen', $nip)
                ->where('kode_prodi', $kodeProdi)
                ->where('tahun', $tahunFilter) // Filter berdasarkan tahun
                ->with([
                    'mk' => function ($query) use ($kodeProdi, $kurikulumId) {
                        $query->where('kode_prodi', $kodeProdi)
                            ->whereHas('cpmks', function ($q) use ($kurikulumId) {
                                $q->where('kurikulum_id', $kurikulumId);
                            });
                    }
                ])
                ->get();

            $mkIds = $kelas->pluck('mk.id')->filter()->unique();
            $mksQuery->whereIn('id', $mkIds);

            if ($mkIds->isEmpty()) {
                Log::warning('No mata kuliah IDs found for NIP: ' . $nip . ', kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter);
            }
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

        Log::info('Search MK results for kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter . ', kurikulumId: ' . $kurikulumId . ', count: ' . $mks->count());
        return response()->json(['results' => $mks]);
    } catch (\Exception $e) {
        Log::error('Error in searchMk for kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter . ', kurikulumId: ' . $kurikulumId . ': ' . $e->getMessage());
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
        $kurikulumId = Kurikulum::where('kode_prodi', $kodeProdi)
            ->where('tahun', $tahunFilter)
            ->value('id');
        if (!$kurikulumId) {
            Log::warning("No kurikulumId found for kode_prodi: {$kodeProdi}, tahunFilter: {$tahunFilter}");
            return response()->json(['error' => 'Kurikulum tidak ditemukan untuk tahun yang dipilih.'], 403);
        }

        // Query dasar untuk mencari mk
        $mkQuery = Mk::where('kode_prodi', $kodeProdi);

        // Hanya terapkan whereHas('kelas') jika peran adalah dosen
        if ($user->role === 'dosen') {
            $mkQuery->whereHas('kelas', function ($query) use ($tahunFilter) {
                $query->where('tahun', $tahunFilter);
            });
        }

        // Ambil data mk dengan cpmks
        $mk = $mkQuery->with([
            'cpmks' => function ($query) use ($kodeProdi, $kurikulumId) {
                $query->where('kode_prodi', $kodeProdi)
                    ->whereHas('mks', function ($q) use ($kurikulumId) {
                        $q->where('cpmk_mk.kurikulum_id', $kurikulumId);
                    })
                    ->select('cpmk.id', 'cpmk.kode_cpmk', 'cpmk.deskripsi')
                    ->withPivot('bobot');
            }
        ])->findOrFail($mk_id);

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

        $cpmks = $mk->cpmks->unique('id')->map(function ($cpmk) use ($mk_id, $kodeProdi, $tahunFilter, $kurikulumId) {
            $teknikPenilaianRaw = DB::table('teknik_penilaian')
                ->where('mk_id', $mk_id)
                ->where('cpmk_id', $cpmk->id)
                ->where('kurikulum_id', $kurikulumId)
                ->get();
            Log::info('Raw teknik_penilaian for mk_id: ' . $mk_id . ', cpmk_id: ' . $cpmk->id . ', kurikulum_id: ' . $kurikulumId . ': ' . json_encode($teknikPenilaianRaw));
            $teknikPenilaian = $teknikPenilaianRaw->pluck('bobot', 'teknik')->toArray();

            Log::info('CPMK Data (getCpmks) for kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter . ', kurikulumId: ' . $kurikulumId . ': ' . json_encode([
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

        Log::info('Returned CPMKs for MK ' . $mk_id . ', kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter . ', kurikulumId: ' . $kurikulumId . ': ' . json_encode($cpmks));
        return response()->json($cpmks);
    } catch (\Exception $e) {
        Log::error('Error in getCpmks for mk_id ' . $mk_id . ', kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter . ', kurikulumId: ' . $kurikulumId . ': ' . $e->getMessage() . ', Trace: ' . $e->getTraceAsString());
        return response()->json(['error' => 'Gagal memuat CPMK: ' . $e->getMessage()], 500);
    }
}

    public function update(Request $request)
    {
        $user = Auth::user();
        $kodeProdi = $user->kode_prodi;
        $tahunFilter = $request->input('tahun', session('selected_year', ''));
        if (is_null($kodeProdi) || !$tahunFilter) {
            return response()->json(['error' => 'Kode prodi atau tahun tidak ditemukan.'], 403);
        }

        try {
            $request->validate([
                'mk_id' => 'required|exists:mk,id',
                'teknikData' => 'required|array',
                'teknikData.*.cpmk_id' => 'required|exists:cpmk,id',
                'teknikData.*.teknik' => 'required|string',
                'teknikData.*.bobot' => 'required|numeric|min:0|max:100',
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
            $kurikulumId = Kurikulum::where('kode_prodi', $kodeProdi)->where('tahun', $tahunFilter)->value('id');
            if (!$kurikulumId) {
                Log::warning("No kurikulumId found for kode_prodi: {$kodeProdi}, tahunFilter: {$tahunFilter}");
                return response()->json(['error' => 'Kurikulum tidak ditemukan untuk tahun yang dipilih.'], 403);
            }

            $mk = Mk::where('kode_prodi', $kodeProdi)
                ->whereHas('kelas', function ($query) use ($tahunFilter) {
                    $query->where('tahun', $tahunFilter);
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
                        ->where('kurikulum_id', $kurikulumId)
                        ->first();

                    if ($existing) {
                        DB::table('cpmk_mk')
                            ->where('mk_id', $mk_id)
                            ->where('cpmk_id', $cpmk_id)
                            ->where('kurikulum_id', $kurikulumId)
                            ->update([
                                'bobot' => $bobot,
                                'min_standard' => 55,
                                'updated_at' => now()
                            ]);
                        Log::info('Updated bobot for mk_id: ' . $mk_id . ', cpmk_id: ' . $cpmk_id . ', bobot: ' . $bobot . ', kurikulum_id: ' . $kurikulumId);
                    } else {
                        $count = DB::table('cpmk_mk')
                            ->where('mk_id', $mk_id)
                            ->where('cpmk_id', $cpmk_id)
                            ->where('kurikulum_id', $kurikulumId)
                            ->count();
                        if ($count == 0) {
                            DB::table('cpmk_mk')->insert([
                                'mk_id' => $mk_id,
                                'cpmk_id' => $cpmk_id,
                                'kurikulum_id' => $kurikulumId,
                                'bobot' => $bobot,
                                'min_standard' => 55,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            Log::info('Inserted bobot for mk_id: ' . $mk_id . ', cpmk_id: ' . $cpmk_id . ', bobot: ' . $bobot . ', kurikulum_id: ' . $kurikulumId);
                        }
                    }
                }

                // Hapus semua teknik penilaian untuk mk_id dan kurikulum_id ini
                DB::table('teknik_penilaian')
                    ->where('mk_id', $mk_id)
                    ->where('kurikulum_id', $kurikulumId)
                    ->delete();

                foreach ($request->teknikData as $teknik) {
                    $cpmk_id = $teknik['cpmk_id'];
                    $teknikNama = $teknik['teknik'];
                    $bobot = (float) $teknik['bobot'];

                    if ($bobot > 0) {
                        DB::table('teknik_penilaian')->insert([
                            'mk_id' => $mk_id,
                            'cpmk_id' => $cpmk_id,
                            'kurikulum_id' => $kurikulumId, // Tambahkan kurikulum_id
                            'teknik' => $teknikNama,
                            'bobot' => $bobot,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        Log::info('Inserted teknik penilaian for mk_id: ' . $mk_id . ', cpmk_id: ' . $cpmk_id . ', teknik: ' . $teknikNama . ', bobot: ' . $bobot . ', kurikulum_id: ' . $kurikulumId);
                    }
                }

                DB::commit();
                Log::info('Bobot CPMK-MK and Teknik Penilaian updated/inserted for mk_id: ' . $request->mk_id . ', kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter . ', kurikulumId: ' . $kurikulumId);
                return response()->json(['success' => 'Data pembobotan tersimpan!']);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Transaction error in update for mk_id ' . $mk_id . ', kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter . ', kurikulumId: ' . $kurikulumId . ': ' . $e->getMessage() . ', Trace: ' . $e->getTraceAsString());
                return response()->json(['error' => 'Gagal menyimpan pembobotan: ' . $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error in update: ' . $e->getMessage() . ', Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Gagal menyimpan pembobotan: ' . $e->getMessage()], 500);
        }
    }

}