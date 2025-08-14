<?php

namespace App\Http\Controllers;

use App\Models\Cpmk;
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

    public function chooseMahasiswa(Request $request)
    {
        $user = Auth::user();
        $kodeProdi = $user->kode_prodi;

        $tahunFilter = request('tahun', session('selected_year', ''));
        if (!$tahunFilter) {
            Log::warning('Tahun filter not found for user: ' . $user->email . ' and kode_prodi: ' . $kodeProdi);
            return redirect()->back()->with('error', 'Tahun kurikulum tidak ditemukan. Pilih tahun terlebih dahulu.');
        }
        session(['selected_year' => $tahunFilter]);

        $periodes = Krs::distinct()
            ->whereHas('mahasiswa', function ($query) use ($kodeProdi) {
                $query->where('kode_prodi', $kodeProdi);
            })
            ->where('tahun', $tahunFilter)
            ->pluck('periode');

        $kelasList = Krs::distinct()
            ->whereHas('mahasiswa', function ($query) use ($kodeProdi) {
                $query->where('kode_prodi', $kodeProdi);
            })
            ->where('tahun', $tahunFilter)
            ->pluck('kelas');

        $mahasiswas = [];

        if ($request->has('periode') && $request->has('kelas')) {
            $periode = $request->input('periode');
            $kelas = $request->input('kelas');

            $krsYear = Krs::where('periode', $periode)->where('kelas', $kelas)->value('tahun');
            if ($krsYear != $tahunFilter) {
                Log::warning('Tahun from KRS ' . $krsYear . ' does not match tahunFilter ' . $tahunFilter);
                return redirect()->back()->with('error', 'Periode atau kelas yang dipilih tidak sesuai dengan tahun kurikulum.');
            }

            $mahasiswas = Mahasiswa::where('kode_prodi', $kodeProdi)
                ->whereHas('krs', function ($query) use ($periode, $kelas) {
                    $query->where('periode', $periode)->where('kelas', $kelas);
                })
                ->join('program_studi', 'mahasiswa.kode_prodi', '=', 'program_studi.kode_prodi')
                ->join('krs', 'mahasiswa.nim', '=', 'krs.nim')
                ->where('krs.tahun', $tahunFilter)
                ->select('mahasiswa.nim', 'mahasiswa.nama', 'program_studi.nama_prodi as program_studi', 'krs.kelas')
                ->groupBy('mahasiswa.nim', 'mahasiswa.nama', 'program_studi', 'krs.kelas')
                ->get();
        }

        return view('nilai_mahasiswa.choose_mahasiswa', compact('periodes', 'kelasList', 'mahasiswas', 'tahunFilter'));
    }

   public function chooseMataKuliah(Request $request)
{
    $user = Auth::user();
    $kodeProdi = $user->kode_prodi;
    $nip = $user->role === 'dosen' ? $user->nip : null;

    $tahunFilter = request('tahun', session('selected_year', ''));
    if (!$tahunFilter) {
        Log::warning('Tahun filter not found for user: ' . $user->email . ' and kode_prodi: ' . $kodeProdi);
        return redirect()->back()->with('error', 'Tahun kurikulum tidak ditemukan. Pilih tahun terlebih dahulu.');
    }
    session(['selected_year' => $tahunFilter]);

    $periodesQuery = Krs::distinct()
        ->whereHas('mahasiswa', function ($query) use ($kodeProdi) {
            $query->where('kode_prodi', $kodeProdi);
        })
        ->where('tahun', $tahunFilter);

    if ($nip) {
        // Ambil periode dari kelas berdasarkan nip_dosen dan tahun
        $kelasPeriodes = Kelas::where('nip_dosen', $nip)
            ->whereHas('mk', function ($query) use ($kodeProdi) {
                $query->where('kode_prodi', $kodeProdi);
            })
            ->where('tahun', $tahunFilter)
            ->pluck('periode');
        Log::info('Kelas Periodes for nip ' . $nip . ': ' . json_encode($kelasPeriodes)); // Debugging

        if ($kelasPeriodes->isEmpty()) {
            Log::warning('No periodes found for nip: ' . $nip . ', kode_prodi: ' . $kodeProdi . ', tahunFilter: ' . $tahunFilter);
        } else {
            // Cocokkan berdasarkan tahun dari periode Krs
            $periodesQuery->where(function ($query) use ($kelasPeriodes, $tahunFilter) {
                foreach ($kelasPeriodes as $kelasPeriode) {
                    $yearPart = substr($kelasPeriode, 0, 4); // Ambil tahun dari "20241" (misalnya "2024")
                    $query->orWhere('tahun', $yearPart)
                          ->orWhereRaw("periode LIKE ?", ["%{$yearPart}%"]); // Cocokkan dengan "2023/2024 Ganjil"
                }
            });
        }
    }

    $periodes = $periodesQuery->pluck('periode');

    $kelasOptions = collect();
    $mahasiswas = null;
    $periode = $request->input('periode');
    $selectedKelas = null;
    $namaMk = 'N/A';

    if ($periode) {
        $krsYear = Krs::where('periode', $periode)->value('tahun');
        if ($krsYear != $tahunFilter) {
            Log::warning('Tahun from KRS ' . $krsYear . ' does not match tahunFilter ' . $tahunFilter);
            return redirect()->back()->with('error', 'Periode yang dipilih tidak sesuai dengan tahun kurikulum.');
        }

        $kelasOptions = Krs::where('krs.periode', $periode)
            ->where('tahun', $tahunFilter)
            ->whereHas('mahasiswa', function ($query) use ($kodeProdi) {
                $query->where('kode_prodi', $kodeProdi);
            })
            ->whereNotNull('krs.kode_mk')
            ->whereNotNull('krs.nama_kelas')
            ->join('mk', 'krs.kode_mk', '=', 'mk.kode_mk')
            ->select('krs.kode_mk', 'krs.nama_kelas', 'mk.deskripsi');

        if ($nip) {
            // Gunakan periode dari kelas yang sesuai dengan tahun
            $kelasIds = Kelas::where('nip_dosen', $nip)
                ->where('tahun', $tahunFilter) // Cocokkan dengan tahunFilter
                ->whereIn('periode', ['20241', '20242']) // Sesuaikan dengan periode yang valid di kelas
                ->whereHas('mk', function ($query) use ($kodeProdi) {
                    $query->where('kode_prodi', $kodeProdi);
                })
                ->pluck('kode_mk');
            Log::info('Kelas IDs for nip ' . $nip . ' and periode ' . $periode . ': ' . json_encode($kelasIds));
            if ($kelasIds->isEmpty()) {
                Log::warning('No kelas IDs found for nip: ' . $nip . ', periode: ' . $periode . ', tahunFilter: ' . $tahunFilter);
            }
            $kelasOptions->whereIn('krs.kode_mk', $kelasIds);
        }

        $kelasOptions = $kelasOptions->distinct()
            ->get()
            ->map(function ($item) {
                return (object) [
                    'id' => $item->kode_mk . '|' . $item->nama_kelas,
                    'text' => $item->kode_mk . ' - ' . $item->deskripsi . ' (' . $item->nama_kelas . ')',
                ];
            });

        if ($request->has('kelas') && $request->input('kelas') !== '' && strpos($request->input('kelas'), '|') !== false) {
            $kelasInput = $request->input('kelas');
            [$kodeMk, $namaKelas] = explode('|', $kelasInput);

            $selectedKelas = Mk::where('kode_mk', $kodeMk)->where('kode_prodi', $kodeProdi)->first();
            if (!$selectedKelas) {
                Log::warning('Mata Kuliah not found for Kode MK: ' . $kodeMk);
                return redirect()->back()->with('error', 'Mata kuliah tidak ditemukan.');
            }

            $namaMk = $selectedKelas->deskripsi;

            $krsRecords = Krs::where('krs.periode', $periode)
                ->where('krs.kode_mk', $kodeMk)
                ->where('krs.nama_kelas', $namaKelas)
                ->where('tahun', $tahunFilter)
                ->whereHas('mahasiswa', function ($query) use ($kodeProdi) {
                    $query->where('kode_prodi', $kodeProdi);
                })
                ->with(['mahasiswa'])
                ->get();

            $mahasiswas = $krsRecords->pluck('mahasiswa')->filter()->unique('id');
            session(['previous_periode' => $periode, 'previous_kelas' => $kelasInput]);
        }
    }

    return view('nilai_mahasiswa.choose_mata_kuliah', compact('periodes', 'kelasOptions', 'mahasiswas', 'selectedKelas', 'periode', 'namaMk', 'tahunFilter'));
}

    public function getKelasByPeriode(Request $request)
{
    $periode = $request->input('periode');
    $tahunFilter = session('selected_year', '');

    if (!$periode || !$tahunFilter) {
        Log::warning('No periode or tahunFilter provided in getKelasByPeriode');
        return response()->json(['options' => []]);
    }

    $user = Auth::user();
    $kodeProdi = $user->kode_prodi;

    $krsYear = Krs::where('periode', $periode)->value('tahun');
    if ($krsYear != $tahunFilter) {
        Log::warning('Tahun from KRS ' . $krsYear . ' does not match tahunFilter ' . $tahunFilter);
        return response()->json(['options' => []]);
    }

    $kelasOptions = Krs::where('krs.periode', $periode)
        ->where('tahun', $tahunFilter)
        ->whereHas('mahasiswa', function ($query) use ($kodeProdi) {
            $query->where('kode_prodi', $kodeProdi);
        })
        ->whereNotNull('krs.kode_mk')
        ->whereNotNull('krs.nama_kelas')
        ->join('mk', 'krs.kode_mk', '=', 'mk.kode_mk')
        ->select('krs.kode_mk', 'krs.nama_kelas', 'mk.deskripsi');

    if ($user->role === 'dosen') {
        $nip = $user->nip;
        if ($nip) {
            $kelasIds = Kelas::where('nip_dosen', $nip)
                ->where('tahun', $tahunFilter) // Cocokkan dengan tahunFilter
                ->whereIn('periode', ['20241', '20242']) // Sesuaikan dengan periode yang valid
                ->whereHas('mk', function ($query) use ($kodeProdi) {
                    $query->where('kode_prodi', $kodeProdi);
                })
                ->pluck('kode_mk');
            Log::info('Kelas IDs for nip ' . $nip . ' and periode ' . $periode . ': ' . json_encode($kelasIds));
            if ($kelasIds->isEmpty()) {
                Log::warning('No kelas IDs found for nip: ' . $nip . ', periode: ' . $periode . ', tahunFilter: ' . $tahunFilter);
            }
            $kelasOptions->whereIn('krs.kode_mk', $kelasIds);
        } else {
            Log::warning('NIP not found for user: ' . $user->email);
            return response()->json(['options' => []]);
        }
    }

    $kelasOptions = $kelasOptions->distinct()
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->kode_mk . '|' . $item->nama_kelas,
                'text' => $item->kode_mk . ' - ' . $item->deskripsi . ' (' . $item->nama_kelas . ')',
            ];
        });

    return response()->json(['options' => $kelasOptions]);
}

    public function grafik($nim, $kode_mk)
{
    $periode = session('previous_periode');
    $kelasInput = session('previous_kelas');
    $tahunFilter = session('selected_year', '');
    $user = Auth::user();
    $kodeProdi = $user->kode_prodi;

    if (!$periode || !$kelasInput || !$tahunFilter) {
        Log::warning('Periode, kelas, or tahunFilter not found or invalid in session');
        return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
            ->with('error', 'Silakan pilih periode dan kelas yang sesuai dengan tahun terlebih dahulu.');
    }

    $krsYear = Krs::where('periode', $periode)->where('kode_mk', explode('|', $kelasInput)[0])->value('tahun');
    if ($krsYear != $tahunFilter) {
        Log::warning('Tahun from KRS ' . $krsYear . ' does not match tahunFilter ' . $tahunFilter);
        return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
            ->with('error', 'Periode yang dipilih tidak sesuai dengan tahun kurikulum.');
    }

    $mahasiswa = Mahasiswa::where('nim', $nim)->where('kode_prodi', $kodeProdi)->firstOrFail();
    $mk = Mk::where('kode_mk', $kode_mk)->where('kode_prodi', $kodeProdi)->firstOrFail();

    $krs = Krs::where('nim', $mahasiswa->nim)
        ->where('periode', $periode)
        ->where('kode_mk', explode('|', $kelasInput)[0])
        ->where('nama_kelas', explode('|', $kelasInput)[1])
        ->where('tahun', $tahunFilter)
        ->whereHas('mahasiswa', function ($query) use ($kodeProdi) {
            $query->where('kode_prodi', $kodeProdi);
        })
        ->first();

    if (!$krs) {
        Log::warning('KRS data not found for mahasiswa', ['nim' => $nim, 'periode' => $periode, 'kode_mk' => $kode_mk]);
        return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
            ->with('error', 'Mahasiswa tidak terdaftar pada mata kuliah ini.');
    }

    $kurikulumId = $krs->kurikulum_id; // Ambil kurikulum_id dari krs

    // Ambil semua CPMK dengan filter kurikulum_id
    $cpmks = Cpmk::whereHas('mks', function ($query) use ($mk, $kurikulumId) {
        $query->where('mk_id', $mk->id)->where('kurikulum_id', $kurikulumId);
    })->with([
        'mks' => function ($query) use ($mk, $kurikulumId) {
            $query->where('mk_id', $mk->id)
                ->where('kurikulum_id', $kurikulumId)
                ->withPivot('bobot', 'min_standard');
        },
        'teknikPenilaian' => function ($query) use ($mk, $kurikulumId) {
            $query->where('mk_id', $mk->id)->where('kurikulum_id', $kurikulumId);
        },
        'nilaiCpmks' => function ($query) use ($mahasiswa, $mk) {
            $query->where('mahasiswa_id', $mahasiswa->id)->where('mk_id', $mk->id);
        }
    ])->get();

    Log::info('Loaded CPMKs for MK ' . $mk->id . ': ' . json_encode($cpmks->pluck('id')->toArray()));
    Log::info('Pivot Bobots: ' . json_encode($cpmks->map(function ($cpmk) use ($mk) {
        $pivot = $cpmk->mks->where('id', $mk->id)->first()->pivot ?? null;
        return $pivot ? $pivot->bobot : 'null';
    })->toArray()));
    Log::info('Teknik Penilaian Bobots: ' . json_encode($cpmks->map(function ($cpmk) {
        return $cpmk->teknikPenilaian->sum('bobot');
    })->toArray()));

    if ($cpmks->isEmpty()) {
        Log::warning('No CPMKs found for MK ID: ' . $mk->id);
        return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
            ->with('error', 'Tidak ada CPMK yang terkait dengan mata kuliah ini.');
    }

    // Hapus filter bobot > 0 untuk menampilkan semua CPMK
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
        $data[] = $nilaiInput; // Gunakan nilai asli untuk grafik
        $totalScore += $nilaiAkhir;
    }

    $minStandard = $cpmks->isNotEmpty() ? (int)($cpmks->first()->mks->where('id', $mk->id)->first()->pivot->min_standard ?? 55) : 55;

    return view('nilai_mahasiswa.grafik', compact('mahasiswa', 'mk', 'cpmks', 'labels', 'data', 'nilaiCpmks', 'totalScore', 'minStandard', 'tahunFilter'));
}

    public function index($nim, $kode_mk)
{
    $periode = session('previous_periode');
    $kelasInput = session('previous_kelas');
    $tahunFilter = session('selected_year', '');
    $user = Auth::user();
    $kodeProdi = $user->kode_prodi;

    // Validasi sesi
    if (!$periode || !$kelasInput || !$tahunFilter) {
        Log::warning('Periode, kelas, or tahunFilter not found or invalid in session');
        return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
            ->with('error', 'Silakan pilih periode dan kelas yang sesuai dengan tahun terlebih dahulu.');
    }

    // Validasi tahun dari KRS
    $krsYear = Krs::where('periode', $periode)->where('kode_mk', explode('|', $kelasInput)[0])->value('tahun');
    if ($krsYear != $tahunFilter) {
        Log::warning('Tahun from KRS ' . $krsYear . ' does not match tahunFilter ' . $tahunFilter);
        return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
            ->with('error', 'Periode yang dipilih tidak sesuai dengan tahun kurikulum.');
    }

    // Ambil data mahasiswa
    $mahasiswa = Mahasiswa::where('nim', $nim)->where('kode_prodi', $kodeProdi)->firstOrFail();
    $mk = Mk::where('kode_mk', $kode_mk)->where('kode_prodi', $kodeProdi)->firstOrFail();

    // Ambil data KRS dan inisialisasi $kurikulumId
    $krs = Krs::where('nim', $mahasiswa->nim)
        ->where('periode', $periode)
        ->where('kode_mk', explode('|', $kelasInput)[0])
        ->where('nama_kelas', explode('|', $kelasInput)[1])
        ->where('tahun', $tahunFilter)
        ->whereHas('mahasiswa', function ($query) use ($kodeProdi) {
            $query->where('kode_prodi', $kodeProdi);
        })
        ->first();

    if (!$krs) {
        Log::warning('KRS data not found for mahasiswa', ['nim' => $nim, 'periode' => $periode, 'kode_mk' => $kode_mk]);
        return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
            ->with('error', 'Mahasiswa tidak terdaftar pada mata kuliah ini untuk periode dan kelas yang dipilih.');
    }

    $kurikulumId = $krs->kurikulum_id;

    // Ambil data CPMK dengan debugging
    $cpmks = Cpmk::whereHas('mks', function ($query) use ($mk) {
        $query->where('mk_id', $mk->id);
    })->with([
        'mks' => function ($query) use ($mk, $tahunFilter, $kodeProdi, $kurikulumId) {
            $query->where('mk_id', $mk->id)
                ->wherePivot('kurikulum_id', $kurikulumId) // Pastikan kurikulum_id sesuai
                ->withPivot('bobot', 'min_standard');
        },
        'teknikPenilaian' => function ($query) use ($mk, $kurikulumId) {
            $query->where('mk_id', $mk->id)->where('kurikulum_id', $kurikulumId);
        },
        'nilaiCpmks' => function ($query) use ($mahasiswa, $mk) {
            $query->where('mahasiswa_id', $mahasiswa->id)->where('mk_id', $mk->id);
        }
    ])->get();

    // Debugging untuk memverifikasi data CPMK
    Log::info('Loaded CPMKs for MK ' . $mk->id . ': ' . json_encode($cpmks->pluck('id')->toArray()));
    Log::info('Pivot Bobots: ' . json_encode($cpmks->map(function ($cpmk) use ($mk) {
        $pivot = $cpmk->mks->where('id', $mk->id)->first()->pivot ?? null;
        return $pivot ? $pivot->bobot : 'null';
    })->toArray()));
    Log::info('Teknik Penilaian Bobots: ' . json_encode($cpmks->map(function ($cpmk) {
        return $cpmk->teknikPenilaian->sum('bobot');
    })->toArray()));

    if ($cpmks->isEmpty()) {
        Log::warning('No CPMKs found for MK ID: ' . $mk->id);
        return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
            ->with('error', 'Tidak ada CPMK yang terkait dengan mata kuliah ini.');
    }

    // Perbaikan logika hasValidBobot untuk mempertimbangkan teknikPenilaian
    $hasValidBobot = $cpmks->contains(function ($cpmk) use ($mk) {
        $pivotBobot = $cpmk->mks->where('id', $mk->id)->first()->pivot->bobot ?? 0;
        $teknikBobot = $cpmk->teknikPenilaian->sum('bobot');
        return $pivotBobot > 0 || $teknikBobot > 0;
    });

    $nilaiCpmks = [];
    foreach ($cpmks as $cpmk) {
        $nilai = $cpmk->nilaiCpmks->first();
        $nilaiCpmks[$cpmk->id] = $nilai ? $nilai->nilai : 0;
    }

    // Pastikan $minStandard diambil dari pivot pertama yang valid
    $minStandard = $cpmks->isNotEmpty() ? (int)($cpmks->first()->mks->where('id', $mk->id)->first()->pivot->min_standard ?? 55) : 55;
    Log::info('Min Standard set to: ' . $minStandard);

    // Kembalikan view dengan data
    return view('nilai_mahasiswa.index', compact('mahasiswa', 'mk', 'cpmks', 'minStandard', 'nilaiCpmks', 'periode', 'kelasInput', 'tahunFilter', 'hasValidBobot'));
}

    public function store(Request $request)
{
    $user = Auth::user();
    Log::info('Store method called with data: ' . json_encode($request->all()));

    try {
        $request->validate([
            'mk_id' => 'required|exists:mk,id',
            'nim' => 'required|exists:mahasiswa,nim',
            'min_standard' => 'required|numeric|min:0|max:100', // Ubah ke numeric untuk handle desimal
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        $tahunFilter = session('selected_year', '');
        $periode = session('previous_periode');
        if (!$periode || !$tahunFilter) {
            Log::warning('Periode or tahunFilter not found or invalid in session');
            return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                ->with('error', 'Silakan pilih periode yang sesuai dengan tahun terlebih dahulu.');
        }

        $krsYear = Krs::where('periode', $periode)->where('kode_mk', Mk::find($request->input('mk_id'))->kode_mk)->value('tahun');
        if ($krsYear != $tahunFilter) {
            Log::warning('Tahun from KRS ' . $krsYear . ' does not match tahunFilter ' . $tahunFilter);
            return redirect()->route('nilai.mahasiswa.choose_mata_kuliah')
                ->with('error', 'Periode yang dipilih tidak sesuai dengan tahun kurikulum.');
        }

        $mkId = $request->input('mk_id');
        $minStandard = (int)$request->input('min_standard');
        // $minStandard = $request->input('min_standard');
        $nim = $request->input('nim');
        $nilai = $request->input('nilai');

        $mahasiswa = Mahasiswa::where('nim', $nim)->firstOrFail();
        $mahasiswaId = $mahasiswa->id;

        $mk = Mk::findOrFail($mkId);

        if ($user->role === 'dosen') {
            $nip = $user->nip;
            if (!$nip) {
                Log::warning("NIP not found for user: {$user->email}");
                return redirect()->back()->with('error', 'NIP tidak ditemukan.');
            }

            $isTeaching = Kelas::where('nip_dosen', $nip)
                ->where('kode_mk', $mk->kode_mk)
                ->where('periode', $periode)
                ->exists();
            if (!$isTeaching) {
                Log::warning("Dosen {$nip} tidak mengajar MK: {$mk->kode_mk} pada periode {$periode}");
                return redirect()->back()->with('error', 'Anda tidak berhak menginput nilai untuk mata kuliah ini.');
            }
        }

        $cpmks = $mk->cpmks()->get();
        foreach ($cpmks as $cpmk) {
            $mk->cpmks()->updateExistingPivot($cpmk->id, ['min_standard' => $minStandard]);
        }

        foreach ($cpmks as $cpmk) {
            $cpmkId = $cpmk->id;
            $nilaiCpmk = $nilai[$cpmkId] ?? null;

            NilaiCpmk::updateOrCreate(
                [
                    'mahasiswa_id' => $mahasiswaId,
                    'mk_id' => $mkId,
                    'cpmk_id' => $cpmkId,
                ],
                [
                    'nilai' => $nilaiCpmk !== null ? $nilaiCpmk : 0,
                ]
            );
        }

        Log::info('Nilai saved successfully for mahasiswa_id: ' . $mahasiswaId . ', mk_id: ' . $mkId);
        return redirect()->route('nilai.mahasiswa.index', ['nim' => $nim, 'kode_mk' => $mk->kode_mk])
            ->with('success', 'Nilai berhasil disimpan!');
    } catch (\Exception $e) {
        Log::error('Error in store method: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan nilai. Silakan coba lagi.');
    }
}
}