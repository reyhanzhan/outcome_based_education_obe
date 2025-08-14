<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Cpl;
use App\Models\NilaiCpmk;
use App\Models\Krs;
use App\Models\Mk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PenilaianCplController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, $mahasiswa_id)
{
    try {
        // Ambil periode dan kelas dari session
        $periode = session('previous_periode');
        $kelasInput = session('previous_kelas');
        $tahunFilter = session('selected_year', '');
        $user = Auth::user();
        $kodeProdi = $user->kode_prodi;

        Log::info('Session Data:', ['periode' => $periode, 'kelasInput' => $kelasInput, 'tahunFilter' => $tahunFilter, 'kodeProdi' => $kodeProdi]);

        if (!$periode || !$kelasInput || !$tahunFilter) {
            return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                ->with('error', 'Silakan pilih periode dan kelas terlebih dahulu.');
        }

        [$kodeMk, $namaKelas] = explode('|', $kelasInput);

        // Scope: 'periode' (hanya matakuliah tertentu) atau 'all' (semua periode hingga periode yang dipilih)
        $scope = $request->query('scope', 'periode');

        $mahasiswa = Mahasiswa::where('id', $mahasiswa_id)->where('kode_prodi', $kodeProdi)->firstOrFail();
        Log::info('Mahasiswa found:', ['id' => $mahasiswa->id, 'nama' => $mahasiswa->nama, 'kode_prodi' => $mahasiswa->kode_prodi]);

        // Ambil KRS untuk mendapatkan kurikulum_id
        $krs = Krs::where('nim', $mahasiswa->nim)
            ->where('periode', $periode)
            ->where('kode_mk', $kodeMk)
            ->where('nama_kelas', $namaKelas)
            ->where('tahun', $tahunFilter)
            ->whereHas('mahasiswa', function ($query) use ($kodeProdi) {
                $query->where('kode_prodi', $kodeProdi);
            })
            ->first();

        if (!$krs) {
            Log::warning('KRS data not found for mahasiswa', ['nim' => $mahasiswa->nim, 'periode' => $periode, 'kode_mk' => $kodeMk]);
            return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                ->with('error', 'Mahasiswa tidak terdaftar pada mata kuliah ini.');
        }

        $kurikulumId = $krs->kurikulum_id;

        // Filter CPL berdasarkan kode_prodi dan matakuliah tertentu saat scope 'periode'
        $cpls = Cpl::where('kode_prodi', $kodeProdi)
            ->with(['cpmks' => function ($query) use ($kodeProdi, $kodeMk, $scope, $kurikulumId) {
                $query->where('kode_prodi', $kodeProdi)
                      ->with(['mks' => function ($query) use ($kodeProdi, $kodeMk, $scope, $kurikulumId) {
                          $query->where('kode_prodi', $kodeProdi)
                                ->wherePivot('kurikulum_id', $kurikulumId);
                          if ($scope === 'periode') {
                              $query->where('kode_mk', $kodeMk); // Filter hanya matakuliah yang dipilih
                          }
                      }]);
            }])
            ->get();
        Log::info('CPLs found:', ['count' => $cpls->count(), 'kodeProdi' => $kodeProdi, 'kurikulumId' => $kurikulumId, 'scope' => $scope]);

        // Ambil daftar periode yang diambil mahasiswa dengan filter kode_prodi
        $periodeList = Krs::where('nim', $mahasiswa->nim)
            ->whereHas('mahasiswa', function ($query) use ($kodeProdi) {
                $query->where('kode_prodi', $kodeProdi);
            })
            ->distinct()
            ->pluck('periode')
            ->toArray();
        sort($periodeList);
        Log::info('Periode yang tersedia:', ['periodeList' => $periodeList]);

        $cplData = [];
        $labels = [];
        $data = [];
        $minStandard = 65;

        Log::info('Memproses CPL untuk Mahasiswa: ' . $mahasiswa->nama . ', ID: ' . $mahasiswa_id . ', Periode: ' . $periode . ', Scope: ' . $scope);

        foreach ($cpls as $cpl) {
            $totalScore = 0;
            $totalMaxWeight = 0;
            $contributions = [];

            Log::info('Memproses CPL: ' . $cpl->kode_cpl . ', ID: ' . $cpl->id);

            foreach ($cpl->cpmks as $cpmk) {
                Log::info('Memproses CPMK: ' . $cpmk->kode_cpmk . ', ID: ' . $cpmk->id);

                // Gunakan collection untuk menghindari duplikat MK
                $mks = $cpmk->mks->unique('id');

                foreach ($mks as $mk) {
                    Log::info('Memproses MK: ' . $mk->kode_mk . ', ID: ' . $mk->id);

                    // Query nilai CPMK dengan filter unik berdasarkan penilaian terbaru
                    $query = NilaiCpmk::where('mahasiswa_id', $mahasiswa_id)
                        ->where('cpmk_id', $cpmk->id)
                        ->where('mk_id', $mk->id)
                        ->whereExists(function ($query) use ($periode, $mahasiswa, $scope, $mk, $kodeProdi, $periodeList, $kurikulumId) {
                            $query->select(DB::raw(1))
                                  ->from('krs')
                                  ->whereColumn('krs.nim', '=', DB::raw("'" . $mahasiswa->nim . "'"))
                                  ->where('krs.kode_mk', $mk->kode_mk)
                                  ->where('krs.kurikulum_id', $kurikulumId)
                                  ->whereExists(function ($subquery) use ($kodeProdi) {
                                      $subquery->select(DB::raw(1))
                                               ->from('mahasiswa')
                                               ->whereColumn('mahasiswa.nim', 'krs.nim')
                                               ->where('mahasiswa.kode_prodi', $kodeProdi);
                                  });
                            if ($scope === 'periode') {
                                $query->where('krs.periode', $periode);
                            } elseif ($scope === 'all' && !empty($periodeList)) {
                                $query->whereIn('krs.periode', $periodeList);
                            }
                        })
                        ->latest('created_at') // Ambil entri terbaru
                        ->first();

                    Log::info('Query Nilai CPMK:', [
                        'mahasiswa_id' => $mahasiswa_id,
                        'cpmk_id' => $cpmk->id,
                        'mk_id' => $mk->id,
                        'found' => $query ? true : false,
                        'nilai' => $query ? $query->nilai : null,
                    ]);

                    if ($query) {
                        $nilai = $query->nilai ?? 0;
                        $bobotMk = $mk->pivot->bobot ?? 0;

                        Log::info('Bobot MK:', [
                            'mk_id' => $mk->id,
                            'cpmk_id' => $cpmk->id,
                            'bobot' => $bobotMk,
                        ]);

                        if ($bobotMk > 0) {
                            $scoreContribution = ($nilai * $bobotMk) / 100;
                            $totalScore += $scoreContribution;
                            $totalMaxWeight += $bobotMk;

                            $contributions[] = [
                                'mk_kode' => $mk->kode_mk,
                                'mk_deskripsi' => $mk->deskripsi,
                                'cpmk_kode' => $cpmk->kode_cpmk,
                                'cpmk_deskripsi' => $cpmk->deskripsi,
                                'nilai' => $nilai,
                                'bobot' => $bobotMk,
                                'kontribusi' => $scoreContribution,
                            ];
                        } else {
                            Log::warning('Bobot MK nol atau null untuk MK ID ' . $mk->id . ', CPMK ID ' . $cpmk->id);
                        }
                    }
                }
            }

            // Hitung pencapaian CPL
            $pencapaianCpl = $totalMaxWeight > 0 ? round(($totalScore / $totalMaxWeight) * 100, 2) : 0;

            // Sertakan CPL jika ada kontribusi atau pencapaian > 0
            if (!empty($contributions) || $pencapaianCpl > 0) {
                $cplData[] = [
                    'kode_cpl' => $cpl->kode_cpl,
                    'deskripsi' => $cpl->deskripsi,
                    'pencapaian_cpl' => $pencapaianCpl,
                    'contributions' => $contributions,
                ];
                $labels[] = $cpl->kode_cpl;
                $data[] = $pencapaianCpl;
            }

            Log::info('CPL Processed:', [
                'kode_cpl' => $cpl->kode_cpl,
                'pencapaian_cpl' => $pencapaianCpl,
                'contributions_count' => count($contributions),
            ]);
        }

        Log::info('CPL Data:', ['cplData' => $cplData]);

        // Siapkan dataset untuk grafik radar
        $datasets = [
            [
                'label' => 'Pencapaian CPL (' . ($scope === 'periode' ? $periode : 'Semua Periode') . ')',
                'data' => $data,
                'fill' => true,
                'backgroundColor' => 'rgba(0, 123, 255, 0.2)',
                'borderColor' => 'rgba(0, 123, 255, 1)',
                'borderWidth' => 2,
                'pointRadius' => 0,
                'pointHoverRadius' => 0,
            ],
            [
                'label' => 'Standar Minimum',
                'data' => array_fill(0, count($labels), $minStandard),
                'fill' => false,
                'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                'borderColor' => 'rgba(255, 99, 132, 1)',
                'borderWidth' => 2,
                'pointRadius' => 0,
                'pointHoverRadius' => 0,
            ],
        ];

        return view('penilaian_cpl.index', compact('mahasiswa', 'cplData', 'labels', 'datasets', 'minStandard', 'periode', 'scope', 'periodeList'));
    } catch (\Exception $e) {
        Log::error('Error di PenilaianCplController::index', ['error' => $e->getMessage()]);
        return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
            ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}
}