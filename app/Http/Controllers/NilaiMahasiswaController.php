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
        // Ambil daftar periode yang tersedia
        $periodes = Krs::distinct()->pluck('periode');

        // Ambil daftar kelas yang tersedia
        $kelasList = Krs::distinct()->pluck('kelas');

        // Variabel default untuk mahasiswa (kosong)
        $mahasiswas = [];

        // Jika periode dan kelas dipilih
        if ($request->has('periode') && $request->has('kelas')) {
            $periode = $request->input('periode');
            $kelas = $request->input('kelas');

            // Ambil mahasiswa yang ada dalam periode dan kelas tersebut
            $mahasiswas = Mahasiswa::whereHas('krs', function ($query) use ($periode, $kelas) {
                $query->where('periode', $periode)
                    ->where('kelas', $kelas);
            })
                ->join('program_studi', 'mahasiswa.kode_prodi', '=', 'program_studi.kode_prodi')
                ->join('krs', 'mahasiswa.nim', '=', 'krs.nim')
                ->select('mahasiswa.nim', 'mahasiswa.nama', 'program_studi.nama_prodi as program_studi', 'krs.kelas')
                ->groupBy('mahasiswa.nim', 'mahasiswa.nama', 'program_studi', 'krs.kelas')
                ->get();
        }

        return view('nilai_mahasiswa.choose_mahasiswa', compact('periodes', 'kelasList', 'mahasiswas'));
    }


    // Halaman untuk memilih mata kuliah berdasarkan mahasiswa
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
            // Ambil data kelas beserta deskripsi mata kuliah dari tabel mk
            $kelasOptions = Krs::where('krs.periode', $periode) // Tambahkan prefiks krs.
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

                // Ambil data kelas menggunakan model Mk
                $selectedKelas = Mk::where('kode_mk', $kodeMk)->first();
                if (!$selectedKelas) {
                    Log::warning('Mata Kuliah not found for Kode MK: ' . $kodeMk);
                    return redirect()->back()->with('error', 'Mata kuliah tidak ditemukan.');
                }

                $namaMk = $selectedKelas->deskripsi;

                // Ambil daftar mahasiswa menggunakan model Krs dan relasi mahasiswa
                $krsRecords = Krs::where('krs.periode', $periode) // Tambahkan prefiks krs.
                    ->where('krs.kode_mk', $kodeMk) // Tambahkan prefiks krs.
                    ->where('krs.nama_kelas', $namaKelas) // Tambahkan prefiks krs.
                    ->with(['mahasiswa'])
                    ->get();

                Log::info('KRS Records for Periode ' . $periode . ', Kode MK ' . $kodeMk . ', Nama Kelas ' . $namaKelas . ': ', $krsRecords->toArray());

                // Pastikan hanya mengambil mahasiswa yang unik berdasarkan id
                $mahasiswas = $krsRecords->pluck('mahasiswa')->filter()->unique('id');
                Log::info('Mahasiswas: ', $mahasiswas->toArray());

                // Simpan periode dan kelas ke session
                session(['previous_periode' => $periode, 'previous_kelas' => $kelasInput]);
            } else {
                Log::info('Kelas not selected or invalid: ' . $request->input('kelas', ''));
            }
        } else {
            Log::info('No periode selected');
        }

        return view('nilai_mahasiswa.choose_mata_kuliah', compact('periodes', 'kelasOptions', 'mahasiswas', 'selectedKelas', 'periode', 'namaMk'));
    }

    // public function getKelasByPeriode(Request $request)
    // {
    //     $periode = $request->input('periode');

    //     if (!$periode) {
    //         return response()->json(['options' => []]);
    //     }

    //     // Ambil data kelas beserta deskripsi mata kuliah dari tabel mk
    //     $kelasOptions = Krs::where('krs.periode', $periode) // Tambahkan prefiks krs.
    //         ->join('mk', 'krs.kode_mk', '=', 'mk.kode_mk')
    //         ->select('krs.kode_mk', 'krs.nama_kelas', 'mk.deskripsi')
    //         ->distinct()
    //         ->get()
    //         ->map(function ($item) {
    //             return [
    //                 'id' => $item->kode_mk . '|' . $item->nama_kelas,
    //                 'text' => $item->kode_mk . ' - ' . $item->deskripsi . ' (' . $item->nama_kelas . ')',
    //             ];
    //         });

    //     Log::info('AJAX Kelas Options for Periode ' . $periode . ': ', $kelasOptions->toArray());

    //     return response()->json(['options' => $kelasOptions]);
    // }
    public function getKelasByPeriode(Request $request)
{
    $periode = $request->input('periode');

    if (!$periode) {
        Log::warning('No periode provided in getKelasByPeriode');
        return response()->json(['options' => []]);
    }

    // Ambil data kelas beserta deskripsi mata kuliah dari tabel mk
    $kelasOptions = Krs::where('krs.periode', $periode)
        ->whereNotNull('krs.kode_mk') // Pastikan kode_mk tidak null
        ->whereNotNull('krs.nama_kelas') // Pastikan nama_kelas tidak null
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

    public function chooseMahasiswaDanPeriode(Request $request)
    {
        $mahasiswas = Mahasiswa::all();
        $periodes = Krs::distinct()->pluck('krs.periode'); // ✅ Pakai alias

        $mks = [];
        $mahasiswa = null;

        if ($request->has('nim') && $request->has('periode')) {
            $nim = $request->input('nim');
            $periode = $request->input('periode');

            // Validasi mahasiswa
            $mahasiswa = Mahasiswa::where('nim', $nim)->first();
            if (!$mahasiswa) {
                return redirect()->back()->with('error', 'Mahasiswa tidak ditemukan!');
            }

            // Ambil daftar mata kuliah mahasiswa pada periode yang dipilih
            $mks = Krs::where('krs.nim', $nim) // ✅ Pakai `krs.nim`
                ->where('krs.periode', $periode) // ✅ Pakai `krs.periode`
                ->join('mk', 'krs.kode_matakuliah', '=', 'mk.kode_mk')
                ->join('kelas', function ($join) {
                    $join->on('krs.kode_matakuliah', '=', 'kelas.kode_matakuliah')
                        ->on('krs.kelas', '=', 'kelas.kelas');
                })
                ->join('dosen', 'kelas.nip', '=', 'dosen.nip')
                ->select('mk.kode_mk', 'mk.deskripsi', 'kelas.kelas', 'dosen.nama as dosen')
                ->get();
        }

        return view('nilai_mahasiswa.choose_mata_kuliah', compact('mahasiswas', 'periodes', 'mks', 'mahasiswa'));
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

            $cpmks = Cpmk::whereHas('mks', function ($query) use ($mk) {
                $query->where('mk_id', $mk->id);
            })->with([
                        'mks' => function ($query) use ($mk) {
                            $query->where('mk_id', $mk->id)->withPivot('bobot', 'min_standard', 'jumlah_penilaian');
                        },
                        'nilaiCpmks' => function ($query) use ($mahasiswa, $mk) {
                            $query->where('mahasiswa_id', $mahasiswa->id)
                                ->where('mk_id', $mk->id);
                        }
                    ])->get();
            Log::info('CPMKs found:', ['count' => $cpmks->count()]);

            // Ambil jumlah penilaian dari tabel pivot cpmk_mk
            $jumlahPenilaian = $cpmks->first() && $cpmks->first()->mks->isNotEmpty()
                ? $cpmks->first()->mks->where('id', $mk->id)->first()->pivot->jumlah_penilaian
                : 1;
            Log::info('Jumlah Penilaian:', ['jumlahPenilaian' => $jumlahPenilaian]);

            // Ambil nilai untuk setiap penilaian
            $nilai = [];
            for ($i = 1; $i <= $jumlahPenilaian; $i++) {
                $nilai[$i] = NilaiCpmk::where('mahasiswa_id', $mahasiswa->id)
                    ->where('mk_id', $mk->id)
                    ->where('penilaian_ke', $i)
                    ->pluck('nilai', 'cpmk_id')
                    ->toArray();
            }

            $minStandard = session('min_standard', 55);
            Log::info('Min Standard:', ['minStandard' => $minStandard]);

            return view('nilai_mahasiswa.index', compact('mahasiswa', 'mk', 'cpmks', 'minStandard', 'jumlahPenilaian', 'nilai'));
        } catch (\Exception $e) {
            Log::error('Error in NilaiMahasiswaController::index', ['error' => $e->getMessage()]);
            return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }



    // Simpan nilai
    public function store(Request $request)
    {
        $user = Auth::user();
        Log::info('Request data: ' . json_encode($request->all()));

        // Validasi input
        $request->validate([
            'mk_id' => 'required|exists:mk,id',
            'nim' => 'required|exists:mahasiswa,nim',
            'penilaian_ke' => 'required|integer|min:1', // Pastikan penilaian_ke ada
            'min_standard' => 'required|integer|min:0|max:100',
        ]);

        $mk_id = $request->input('mk_id');
        $min_standard = $request->input('min_standard');
        $nim = $request->input('nim');
        $penilaian_ke = $request->input('penilaian_ke');
        Log::info('Received penilaian_ke: ' . $penilaian_ke); // Tambahkan log ini

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

        // Simpan nilai
        foreach ($request->except(['_token', 'mk_id', 'min_standard', 'nim', 'penilaian_ke']) as $key => $value) {
            if (preg_match('/nilai_(\d+)_(\d+)/', $key, $matches)) {
                $input_mahasiswa_id = $matches[1];
                $cpmk_id = $matches[2];
                Log::info('Input: mahasiswa_id=' . $input_mahasiswa_id . ', cpmk_id=' . $cpmk_id . ', nilai=' . $value . ', penilaian_ke=' . $penilaian_ke);

                $nilai = NilaiCpmk::updateOrCreate(
                    [
                        'mahasiswa_id' => $mahasiswa_id,
                        'mk_id' => $mk_id,
                        'cpmk_id' => $cpmk_id,
                        'penilaian_ke' => $penilaian_ke,
                    ],
                    [
                        'nilai' => $value,
                    ]
                );
                Log::info('Saved Nilai: Mahasiswa ID ' . $mahasiswa_id . ', MK ID ' . $mk_id . ', CPMK ID ' . $cpmk_id . ', Penilaian Ke ' . $penilaian_ke . ', Nilai ' . $value);
            }
        }

        return redirect()->back()->with('success', 'Nilai berhasil disimpan!');
    }

    public function grafik($nim, $kode_mk)
    {
        try {
            Log::info('NilaiMahasiswaController::grafik called', ['nim' => $nim, 'kode_mk' => $kode_mk]);

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

            $cpmks = Cpmk::whereHas('mks', function ($query) use ($mk) {
                $query->where('mk_id', $mk->id);
            })->with([
                        'mks' => function ($query) use ($mk) {
                            $query->where('mk_id', $mk->id)->withPivot('bobot', 'min_standard', 'jumlah_penilaian');
                        },
                        'nilaiCpmks' => function ($query) use ($mahasiswa, $mk) {
                            $query->where('mahasiswa_id', $mahasiswa->id)
                                ->where('mk_id', $mk->id);
                        }
                    ])->get();
            Log::info('CPMKs found:', ['count' => $cpmks->count()]);

            // Ambil jumlah penilaian dari tabel pivot cpmk_mk
            $jumlahPenilaian = $cpmks->first() && $cpmks->first()->mks->isNotEmpty()
                ? $cpmks->first()->mks->where('id', $mk->id)->first()->pivot->jumlah_penilaian
                : 1;
            Log::info('Jumlah Penilaian:', ['jumlahPenilaian' => $jumlahPenilaian]);

            // Ambil nilai untuk setiap penilaian
            $nilai = [];
            for ($i = 1; $i <= $jumlahPenilaian; $i++) {
                $nilai[$i] = NilaiCpmk::where('mahasiswa_id', $mahasiswa->id)
                    ->where('mk_id', $mk->id)
                    ->where('penilaian_ke', $i)
                    ->pluck('nilai', 'cpmk_id')
                    ->toArray();
            }

            $minStandard = session('min_standard', 55);
            Log::info('Min Standard:', ['minStandard' => $minStandard]);

            // Siapkan data untuk grafik radar per penilaian
            $radarDataPerPenilaian = [];
            $finalScores = [];
            $labels = $cpmks->pluck('kode_cpmk')->toArray();

            for ($i = 1; $i <= $jumlahPenilaian; $i++) {
                $data = [];
                $totalScore = 0;
                $totalBobot = 0;

                foreach ($cpmks as $cpmk) {
                    $nilaiInput = $nilai[$i][$cpmk->id] ?? 0;
                    $bobot = $cpmk->mks->isNotEmpty() ? ($cpmk->mks->where('id', $mk->id)->first()->pivot->bobot ?? 0) : 0;
                    $nilaiAkhir = ($nilaiInput * $bobot) / 100;
                    $data[] = $nilaiInput; // Gunakan nilai asli untuk grafik

                    // Hitung kontribusi untuk nilai total MK
                    $totalScore += $nilaiAkhir;
                    $totalBobot += $bobot;

                    Log::info('CPMK Data for Penilaian Ke ' . $i, [
                        'cpmk_id' => $cpmk->id,
                        'kode_cpmk' => $cpmk->kode_cpmk,
                        'nilaiInput' => $nilaiInput,
                        'bobot' => $bobot,
                        'nilaiAkhir' => $nilaiAkhir,
                    ]);
                }

                // Hitung nilai total MK untuk penilaian ini
                $finalScore = $totalBobot > 0 ? ($totalScore / ($totalBobot / 100)) : 0;
                $finalScores[$i] = $finalScore;

                // Simpan data untuk grafik radar
                $radarDataPerPenilaian[$i] = [
                    'labels' => $labels,
                    'data' => $data,
                ];

                Log::info('Radar Data for Penilaian Ke ' . $i, [
                    'mahasiswa_id' => $mahasiswa->id,
                    'mk_id' => $mk->id,
                    'labels' => $labels,
                    'data' => $data,
                    'finalScore' => $finalScore,
                ]);
            }

            return view('nilai_mahasiswa.grafik', compact('mahasiswa', 'mk', 'cpmks', 'minStandard', 'jumlahPenilaian', 'radarDataPerPenilaian', 'nilai', 'finalScores'));
        } catch (\Exception $e) {
            Log::error('Error in NilaiMahasiswaController::grafik', ['error' => $e->getMessage()]);
            return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}