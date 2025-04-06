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

class PenilaianCplController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, $mahasiswa_id)
    {
        try {
            $periode = session('previous_periode');
            $kelasInput = session('previous_kelas');

            if (!$periode || !$kelasInput) {
                return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                    ->with('error', 'Silakan pilih periode dan kelas terlebih dahulu di Penilaian CPMK.');
            }

            [$kodeMk, $namaKelas] = explode('|', $kelasInput);

            $scope = $request->query('scope', 'periode');

            $mahasiswa = Mahasiswa::findOrFail($mahasiswa_id);
            $cpls = Cpl::with(['cpmks.mks'])->get();

            $cplData = [];
            $labels = [];
            $datasets = [];
            $minStandard = 55;

            $bobotPenilaian = [
                1 => 0.3, // Perkuliahan: 30%
                2 => 0.3, // UTS: 30%
                3 => 0.4, // UAS: 40%
            ];

            Log::info('Processing CPL for Mahasiswa: ' . $mahasiswa->nama . ', ID: ' . $mahasiswa_id . ', Periode: ' . $periode . ', Scope: ' . $scope);

            // Siapkan data untuk grafik per tahap penilaian
            $penilaianKeys = array_keys($bobotPenilaian); // [1, 2, 3]
            $dataByPenilaian = [];
            foreach ($penilaianKeys as $penilaianKe) {
                $dataByPenilaian[$penilaianKe] = [];
            }

            foreach ($cpls as $cpl) {
                $totalScore = 0;
                $totalMaxWeight = 0;
                $contributions = [];

                $cplScoresByPenilaian = [];
                $totalMaxWeightByPenilaian = [];

                Log::info('Processing CPL: ' . $cpl->kode_cpl . ', ID: ' . $cpl->id);

                foreach ($cpl->cpmks as $cpmk) {
                    Log::info('Processing CPMK: ' . $cpmk->kode_cpmk . ', ID: ' . $cpmk->id);

                    foreach ($cpmk->mks as $mk) {
                        Log::info('Processing MK: ' . $mk->kode_mk . ', ID: ' . $mk->id);

                        $nilaiCpmks = NilaiCpmk::where('mahasiswa_id', $mahasiswa_id)
                            ->where('cpmk_id', $cpmk->id)
                            ->where('mk_id', $mk->id)
                            ->whereExists(function ($query) use ($periode, $mahasiswa, $scope) {
                                $query->select(DB::raw(1))
                                    ->from('krs')
                                    ->join('mk', 'krs.kode_mk', '=', 'mk.kode_mk')
                                    ->whereColumn('mk.id', 'nilai_cpmk.mk_id')
                                    ->where('krs.nim', $mahasiswa->nim);

                                if ($scope === 'periode') {
                                    $query->where('krs.periode', $periode);
                                }
                            })
                            ->get();

                        if ($nilaiCpmks->isNotEmpty()) {
                            $nilaiAkhir = 0;
                            $detailPenilaian = [];

                            // Hitung nilai akhir CPMK dan simpan detail penilaian
                            foreach ($nilaiCpmks as $nilaiCpmk) {
                                $penilaianKe = $nilaiCpmk->penilaian_ke;
                                $nilai = $nilaiCpmk->nilai ?? 0;
                                $bobot = $bobotPenilaian[$penilaianKe] ?? 0;

                                $nilaiAkhir += $nilai * $bobot;

                                $detailPenilaian[] = [
                                    'penilaian_ke' => $penilaianKe,
                                    'nilai' => $nilai,
                                    'bobot' => $bobot * 100,
                                    'kontribusi' => $nilai * $bobot,
                                ];

                                // Hitung CPL per tahap penilaian
                                $bobotMk = $mk->pivot->bobot ?? 0;
                                if ($bobotMk > 0) {
                                    $scoreContribution = ($nilai * $bobotMk) / 100;

                                    if (!isset($cplScoresByPenilaian[$penilaianKe])) {
                                        $cplScoresByPenilaian[$penilaianKe] = 0;
                                        $totalMaxWeightByPenilaian[$penilaianKe] = 0;
                                    }

                                    $cplScoresByPenilaian[$penilaianKe] += $scoreContribution;
                                    $totalMaxWeightByPenilaian[$penilaianKe] += $bobotMk;
                                }
                            }

                            $bobotMk = $mk->pivot->bobot ?? 0;

                            if ($bobotMk > 0) {
                                $scoreContribution = ($nilaiAkhir * $bobotMk) / 100;
                                $totalScore += $scoreContribution;
                                $totalMaxWeight += $bobotMk;

                                $contributions[] = [
                                    'mk_kode' => $mk->kode_mk,
                                    'mk_deskripsi' => $mk->deskripsi,
                                    'cpmk_kode' => $cpmk->kode_cpmk,
                                    'cpmk_deskripsi' => $cpmk->deskripsi,
                                    'nilai_akhir' => $nilaiAkhir,
                                    'bobot' => $bobotMk,
                                    'kontribusi' => $scoreContribution,
                                    'detail_penilaian' => $detailPenilaian,
                                ];
                            }
                        } else {
                            Log::warning('No Nilai CPMK found for Mahasiswa ID ' . $mahasiswa_id . ', CPMK ID ' . $cpmk->id . ', MK ID ' . $mk->id);
                        }
                    }
                }

                $pencapaianCpl = $totalMaxWeight > 0 ? round(($totalScore / $totalMaxWeight) * 100, 2) : 0;

                // Hitung pencapaian CPL untuk setiap tahap penilaian
                $pencapaianCplByPenilaian = [];
                foreach ($cplScoresByPenilaian as $penilaianKe => $score) {
                    $totalMaxWeight = $totalMaxWeightByPenilaian[$penilaianKe] ?? 0;
                    $pencapaianCplByPenilaian[$penilaianKe] = $totalMaxWeight > 0 ? round(($score / $totalMaxWeight) * 100, 2) : 0;
                }

                $cplData[] = [
                    'kode_cpl' => $cpl->kode_cpl,
                    'deskripsi' => $cpl->deskripsi,
                    'nilai_cpl' => $totalScore,
                    'pencapaian_cpl' => $pencapaianCpl,
                    'pencapaian_cpl_by_penilaian' => $pencapaianCplByPenilaian,
                    'contributions' => $contributions,
                ];

                // Hanya masukkan ke labels dan data jika ada kontribusi
                if (!empty($pencapaianCplByPenilaian)) {
                    $labels[] = $cpl->kode_cpl;
                    foreach ($penilaianKeys as $penilaianKe) {
                        $dataByPenilaian[$penilaianKe][] = $pencapaianCplByPenilaian[$penilaianKe] ?? 0;
                    }
                }
            }

            // Siapkan datasets untuk grafik
            $colors = [
                1 => ['background' => 'rgba(0, 123, 255, 0.2)', 'border' => 'rgba(0, 123, 255, 1)'], // Penilaian 1
                2 => ['background' => 'rgba(75, 192, 192, 0.2)', 'border' => 'rgba(75, 192, 192, 1)'], // Penilaian 2
                3 => ['background' => 'rgba(255, 206, 86, 0.2)', 'border' => 'rgba(255, 206, 86, 1)'], // Penilaian 3
            ];

            foreach ($penilaianKeys as $penilaianKe) {
                $datasets[] = [
                    'label' => 'Pencapaian CPL (Penilaian ' . $penilaianKe . ')',
                    'data' => $dataByPenilaian[$penilaianKe],
                    'fill' => true,
                    'backgroundColor' => $colors[$penilaianKe]['background'],
                    'borderColor' => $colors[$penilaianKe]['border'],
                    'borderWidth' => 2,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 0,
                ];
            }

            // Tambahkan dataset untuk standar minimum
            $datasets[] = [
                'label' => 'Standar Minimum',
                'data' => array_fill(0, count($labels), $minStandard),
                'fill' => true,
                'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                'borderColor' => 'rgba(255, 99, 132, 1)',
                'borderWidth' => 2,
                'pointRadius' => 0,
                'pointHoverRadius' => 0,
            ];

            return view('penilaian_cpl.index', compact('mahasiswa', 'cplData', 'labels', 'datasets', 'minStandard', 'periode', 'kodeMk', 'namaKelas', 'scope'));
        } catch (\Exception $e) {
            Log::error('Error in PenilaianCplController::index', ['error' => $e->getMessage()]);
            return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}