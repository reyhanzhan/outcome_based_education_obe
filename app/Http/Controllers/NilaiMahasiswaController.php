<?php

namespace App\Http\Controllers;

use App\Models\Cpmk;
use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Mk;
use App\Models\NilaiCpmk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class NilaiMahasiswaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Halaman untuk memilih mahasiswa
    public function chooseMahasiswa(Request $request)
    {
        $periodes = Krs::distinct()->pluck('periode');
        $kelasList = Krs::distinct()->pluck('kelas');
        $mahasiswas = [];

        if ($request->has('periode') && $request->has('kelas')) {
            $periode = $request->input('periode');
            $kelas = $request->input('kelas');

            $mahasiswas = Mahasiswa::whereHas('krs', function ($query) use ($periode, $kelas) {
                $query->where('periode', $periode)->where('kelas', $kelas);
            })
                ->join('program_studi', 'mahasiswa.kode_prodi', '=', 'program_studi.kode_prodi')
                ->join('krs', 'mahasiswa.nim', '=', 'krs.nim')
                ->select('mahasiswa.nim', 'mahasiswa.nama', 'program_studi.nama_prodi as program_studi', 'krs.kelas')
                ->groupBy('mahasiswa.nim', 'mahasiswa.nama', 'program_studi', 'krs.kelas')
                ->get();
        }

        return view('nilai_mahasiswa.choose_mahasiswa', compact('periodes', 'kelasList', 'mahasiswas'));
    }

    // Halaman untuk memilih mata kuliah
    public function chooseMataKuliah(Request $request)
    {
        $periodes = Krs::distinct()->pluck('periode');
        Log::info('Available Periodes: ', $periodes->toArray());

        $kelasOptions = collect();
        $mahasiswas = null;
        $periode = $request->input('periode');
        $selectedKelas = null;
        $namaMk = 'N/A';

        Log::info('Request Input: ', $request->all());

        if ($periode) {
            $kelasOptions = Krs::where('krs.periode', $periode)
                ->whereNotNull('krs.kode_mk')
                ->whereNotNull('krs.nama_kelas')
                ->join('mk', 'krs.kode_mk', '=', 'mk.kode_mk')
                ->select('krs.kode_mk', 'krs.nama_kelas', 'mk.deskripsi')
                ->distinct()
                ->get()
                ->map(function ($item) {
                    return (object) [
                        'id' => $item->kode_mk . '|' . $item->nama_kelas,
                        'text' => $item->kode_mk . ' - ' . $item->deskripsi . ' (' . $item->nama_kelas . ')',
                    ];
                });

            Log::info('Periode: ' . $periode);
            Log::info('Kelas Options: ', $kelasOptions->toArray());

            if ($request->has('kelas') && $request->input('kelas') !== '' && strpos($request->input('kelas'), '|') !== false) {
                $kelasInput = $request->input('kelas');
                Log::info('Selected Kelas Input: ' . $kelasInput);

                [$kodeMk, $namaKelas] = explode('|', $kelasInput);

                $selectedKelas = Mk::where('kode_mk', $kodeMk)->first();
                if (!$selectedKelas) {
                    Log::warning('Mata Kuliah not found for Kode MK: ' . $kodeMk);
                    return redirect()->back()->with('error', 'Mata kuliah tidak ditemukan.');
                }

                $namaMk = $selectedKelas->deskripsi;

                $krsRecords = Krs::where('krs.periode', $periode)
                    ->where('krs.kode_mk', $kodeMk)
                    ->where('krs.nama_kelas', $namaKelas)
                    ->with(['mahasiswa'])
                    ->get();

                Log::info('KRS Records for Periode ' . $periode . ', Kode MK ' . $kodeMk . ', Nama Kelas ' . $namaKelas . ': ', $krsRecords->toArray());

                $mahasiswas = $krsRecords->pluck('mahasiswa')->filter()->unique('id');
                Log::info('Mahasiswas: ', $mahasiswas->toArray());

                session(['previous_periode' => $periode, 'previous_kelas' => $kelasInput]);
            } else {
                Log::info('Kelas not selected or invalid: ' . $request->input('kelas', ''));
            }
        } else {
            Log::info('No periode selected');
        }

        return view('nilai_mahasiswa.choose_mata_kuliah', compact('periodes', 'kelasOptions', 'mahasiswas', 'selectedKelas', 'periode', 'namaMk'));
    }

    public function getKelasByPeriode(Request $request)
    {
        $periode = $request->input('periode');

        if (!$periode) {
            Log::warning('No periode provided in getKelasByPeriode');
            return response()->json(['options' => []]);
        }

        $kelasOptions = Krs::where('krs.periode', $periode)
            ->whereNotNull('krs.kode_mk')
            ->whereNotNull('krs.nama_kelas')
            ->join('mk', 'krs.kode_mk', '=', 'mk.kode_mk')
            ->select('krs.kode_mk', 'krs.nama_kelas', 'mk.deskripsi')
            ->distinct()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->kode_mk . '|' . $item->nama_kelas,
                    'text' => $item->kode_mk . ' - ' . $item->deskripsi . ' (' . $item->nama_kelas . ')',
                ];
            });

        Log::info('AJAX Kelas Options for Periode ' . $periode . ': ', $kelasOptions->toArray());

        return response()->json(['options' => $kelasOptions]);
    }

    public function grafik($nim, $kode_mk)
{
    try {
        Log::info('NilaiMahasiswaController::grafik called', ['nim' => $nim, 'kode_mk' => $kode_mk]);

        // Ambil periode dan kelas dari session
        $periode = session('previous_periode');
        $kelasInput = session('previous_kelas');
        Log::info('Session Data:', ['periode' => $periode, 'kelas' => $kelasInput]);

        if (!$periode || !$kelasInput) {
            Log::warning('Periode or kelas not found in session');
            return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                ->with('error', 'Silakan pilih periode dan kelas terlebih dahulu.');
        }

        // Parse kelasInput (kode_mk|nama_kelas)
        [$selectedKodeMk, $namaKelas] = explode('|', $kelasInput);

        // Ambil data mahasiswa
        $mahasiswa = Mahasiswa::where('nim', $nim)->firstOrFail();
        Log::info('Mahasiswa found:', ['id' => $mahasiswa->id, 'nama' => $mahasiswa->nama]);

        // Ambil data mata kuliah
        $mk = Mk::where('kode_mk', $kode_mk)->firstOrFail();
        Log::info('MK found:', ['id' => $mk->id, 'kode_mk' => $mk->kode_mk]);

        // Validasi KRS
        $krs = Krs::where('nim', $mahasiswa->nim)
            ->where('periode', $periode)
            ->where('kode_mk', $selectedKodeMk)
            ->where('nama_kelas', $namaKelas)
            ->exists();

        if (!$krs) {
            Log::warning('KRS data not found for mahasiswa', [
                'nim' => $mahasiswa->nim,
                'periode' => $periode,
                'kode_mk' => $selectedKodeMk,
                'nama_kelas' => $namaKelas,
            ]);
            return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                ->with('error', 'Mahasiswa tidak terdaftar pada mata kuliah ini.');
        }

        // Ambil CPMK dengan bobot dan nilai
        $cpmks = Cpmk::whereHas('mks', function ($query) use ($mk) {
            $query->where('mk_id', $mk->id);
        })->with([
            'mks' => function ($query) use ($mk) {
                $query->where('mk_id', $mk->id)->withPivot('bobot', 'min_standard');
            },
            'nilaiCpmks' => function ($query) use ($mahasiswa, $mk) {
                $query->where('mahasiswa_id', $mahasiswa->id)
                    ->where('mk_id', $mk->id);
            }
        ])->get();
        Log::info('CPMKs found:', ['count' => $cpmks->count()]);

        if ($cpmks->isEmpty()) {
            Log::warning('No CPMKs found for MK ID: ' . $mk->id);
            return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                ->with('error', 'Tidak ada CPMK yang terkait dengan mata kuliah ini.');
        }

        // Siapkan data untuk grafik
        $labels = $cpmks->pluck('kode_cpmk')->toArray();
        $data = [];
        $nilaiCpmks = [];
        $totalScore = 0;

        foreach ($cpmks as $cpmk) {
            $nilai = $cpmk->nilaiCpmks->first();
            $nilaiInput = $nilai ? ($nilai->nilai ?? 0) : 0;
            $bobot = $cpmk->mks->where('id', $mk->id)->first()->pivot->bobot ?? 0;
            $nilaiAkhir = ($nilaiInput * $bobot) / 100;

            $nilaiCpmks[$cpmk->id] = $nilaiInput;
            $data[] = $nilaiInput; // Nilai asli untuk grafik
            $totalScore += $nilaiAkhir;

            Log::info("Memproses CPMK: {$cpmk->kode_cpmk}, Nilai Input: {$nilaiInput}, Bobot: {$bobot}, Nilai Akhir: {$nilaiAkhir}");
        }

        $minStandard = $cpmks->isNotEmpty() ? ($cpmks->first()->mks->first()->pivot->min_standard ?? 55) : 55;
        Log::info('Grafik Data:', ['labels' => $labels, 'data' => $data, 'totalScore' => $totalScore, 'minStandard' => $minStandard]);

        return view('nilai_mahasiswa.grafik', compact('mahasiswa', 'mk', 'cpmks', 'labels', 'data', 'nilaiCpmks', 'totalScore', 'minStandard'));
    } catch (\Exception $e) {
        Log::error('Error in NilaiMahasiswaController::grafik', ['error' => $e->getMessage()]);
        return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
            ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

    public function index($nim, $kode_mk)
    {
        try {
            Log::info('NilaiMahasiswaController::index called', ['nim' => $nim, 'kode_mk' => $kode_mk]);

            $periode = session('previous_periode');
            $kelasInput = session('previous_kelas');
            Log::info('Session Data:', ['periode' => $periode, 'kelas' => $kelasInput]);

            if (!$periode || !$kelasInput) {
                Log::warning('Periode or kelas not found in session');
                return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                    ->with('error', 'Silakan pilih periode dan kelas terlebih dahulu.');
            }

            [$selectedKodeMk, $namaKelas] = explode('|', $kelasInput);

            $mahasiswa = Mahasiswa::where('nim', $nim)->firstOrFail();
            Log::info('Mahasiswa found:', ['id' => $mahasiswa->id, 'nama' => $mahasiswa->nama]);

            $mk = Mk::where('kode_mk', $kode_mk)->firstOrFail();
            Log::info('MK found:', ['id' => $mk->id, 'kode_mk' => $mk->kode_mk]);

            $krs = Krs::where('nim', $mahasiswa->nim)
                ->where('periode', $periode)
                ->where('kode_mk', $selectedKodeMk)
                ->where('nama_kelas', $namaKelas)
                ->exists();

            if (!$krs) {
                Log::warning('KRS data not found for mahasiswa', [
                    'nim' => $mahasiswa->nim,
                    'periode' => $periode,
                    'kode_mk' => $selectedKodeMk,
                    'nama_kelas' => $namaKelas,
                ]);
                return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                    ->with('error', 'Mahasiswa tidak terdaftar pada mata kuliah ini untuk periode dan kelas yang dipilih.');
            }

            // Ambil CPMK
            $cpmks = Cpmk::whereHas('mks', function ($query) use ($mk) {
                $query->where('mk_id', $mk->id);
            })->with([
                'mks' => function ($query) use ($mk) {
                    $query->where('mk_id', $mk->id)->withPivot('bobot', 'min_standard');
                },
                'nilaiCpmks' => function ($query) use ($mahasiswa, $mk) {
                    $query->where('mahasiswa_id', $mahasiswa->id)->where('mk_id', $mk->id);
                }
            ])->get();
            Log::info('CPMKs found:', ['count' => $cpmks->count()]);

            if ($cpmks->isEmpty()) {
                Log::warning('No CPMKs found for MK ID: ' . $mk->id);
                return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                    ->with('error', 'Tidak ada CPMK yang terkait dengan mata kuliah ini.');
            }

            // Siapkan data nilai CPMK
            $nilaiCpmks = [];
            foreach ($cpmks as $cpmk) {
                $nilai = $cpmk->nilaiCpmks->first();
                $nilaiCpmks[$cpmk->id] = $nilai ? $nilai->nilai : 0;
            }

            $minStandard = session('min_standard', 55);
            Log::info('Min Standard:', ['minStandard' => $minStandard]);

            return view('nilai_mahasiswa.index', compact('mahasiswa', 'mk', 'cpmks', 'minStandard', 'nilaiCpmks', 'periode', 'kelasInput'));
        } catch (\Exception $e) {
            Log::error('Error in NilaiMahasiswaController::index', ['error' => $e->getMessage()]);
            return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        Log::info('Request data: ' . json_encode($request->all()));

        $request->validate([
            'mk_id' => 'required|exists:mk,id',
            'nim' => 'required|exists:mahasiswa,nim',
            'min_standard' => 'required|integer|min:0|max:100',
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        $mk_id = $request->input('mk_id');
        $min_standard = $request->input('min_standard');
        $nim = $request->input('nim');
        $nilai = $request->input('nilai');

        $mahasiswa = Mahasiswa::where('nim', $nim)->firstOrFail();
        $mahasiswa_id = $mahasiswa->id;
        Log::info('Converted NIM: ' . $nim . ' to Mahasiswa ID: ' . $mahasiswa_id);

        $mk = Mk::findOrFail($mk_id);
        Log::info('MK ID: ' . $mk_id . ', Kode MK: ' . $mk->kode_mk);

        // Validasi dosen
        if ($user->role === 'dosen') {
            $dosen = $user->dosen;
            if (!$dosen) {
                Log::warning('Dosen data not found for user: ' . $user->email);
                return redirect()->back()->with('error', 'Data dosen tidak ditemukan.');
            }

            $kelas = Kelas::where('kode_matakuliah', $mk->kode_mk)
                ->where('nip', $dosen->nip)
                ->whereHas('krs', function ($query) use ($nim) {
                    $query->where('nim', $nim);
                })
                ->exists();

            if (!$kelas) {
                Log::warning('Dosen ' . $dosen->nip . ' tidak mengajar MK: ' . $mk->kode_mk . ' untuk NIM: ' . $nim);
                return redirect()->back()->with('error', 'Anda tidak berhak menginput nilai untuk mata kuliah ini.');
            }
        }

        // Simpan standar minimum
        $cpmks = $mk->cpmks()->get();
        foreach ($cpmks as $cpmk) {
            $mk->cpmks()->updateExistingPivot($cpmk->id, ['min_standard' => $min_standard]);
        }

        // Simpan nilai CPMK
        foreach ($nilai as $cpmk_id => $nilaiCpmk) {
            if (!is_null($nilaiCpmk) && $nilaiCpmk >= 0) {
                NilaiCpmk::updateOrCreate(
                    [
                        'mahasiswa_id' => $mahasiswa_id,
                        'mk_id' => $mk_id,
                        'cpmk_id' => $cpmk_id,
                    ],
                    [
                        'nilai' => $nilaiCpmk,
                    ]
                );
                Log::info('Saved Nilai CPMK: Mahasiswa ID ' . $mahasiswa_id . ', MK ID ' . $mk_id . ', CPMK ID ' . $cpmk_id . ', Nilai ' . $nilaiCpmk);
            }
        }

        return redirect()->back()->with('success', 'Nilai berhasil disimpan!');
    }
}