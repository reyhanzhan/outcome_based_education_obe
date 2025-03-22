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
            $kelasOptions = Krs::where('periode', $periode)
                ->whereNotNull('kode_mk')
                ->whereNotNull('nama_kelas')
                ->select('kode_mk', 'nama_kelas')
                ->distinct()
                ->get()
                ->map(function ($item) {
                    return (object) [
                        'id' => $item->kode_mk . '|' . $item->nama_kelas,
                        'text' => $item->kode_mk . ' - ' . $item->nama_kelas,
                    ];
                });

            Log::info('Periode: ' . $periode);
            Log::info('Kelas Options: ', $kelasOptions->toArray());

            if ($request->has('kelas') && $request->input('kelas') !== '' && strpos($request->input('kelas'), '|') !== false) {
                $kelasInput = $request->input('kelas');
                Log::info('Selected Kelas Input: ' . $kelasInput);

                [$kodeMk, $namaKelas] = explode('|', $kelasInput);

                $selectedKelas = (object) [
                    'kode_mk' => $kodeMk,
                    'nama_kelas' => $namaKelas,
                ];

                $krsRecords = Krs::where('periode', $periode)
                    ->where('kode_mk', $kodeMk)
                    ->where('nama_kelas', $namaKelas)
                    ->with(['mahasiswa', 'mk'])
                    ->get();

                Log::info('KRS Records for Periode ' . $periode . ', Kode MK ' . $kodeMk . ', Nama Kelas ' . $namaKelas . ': ', $krsRecords->toArray());

                $mahasiswas = $krsRecords->pluck('mahasiswa')->filter()->unique('id');
                Log::info('Mahasiswas: ', $mahasiswas->toArray());

                $mk = Mk::where('kode_mk', $kodeMk)->first();
                $namaMk = $mk ? $mk->deskripsi : 'N/A';
                Log::info('Nama MK for Kode MK ' . $kodeMk . ': ' . $namaMk);

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

    public function getKelasByPeriode(Request $request)
    {
        $periode = $request->input('periode');

        if (!$periode) {
            return response()->json(['options' => []]);
        }

        $kelasOptions = Krs::where('periode', $periode)
            ->select('kode_mk', 'nama_kelas')
            ->distinct()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->kode_mk . '|' . $item->nama_kelas,
                    'text' => $item->kode_mk . ' - ' . $item->nama_kelas,
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

    // Halaman untuk input nilai
    public function index($nim, $kode_mk)
    {
        $user = Auth::user();
        $mahasiswa = Mahasiswa::where('nim', $nim)->firstOrFail();
        $mk = Mk::where('kode_mk', $kode_mk)->firstOrFail();

        if ($user->role === 'dosen') {
            $dosen = $user->dosen;
            if (!$dosen) {
                Log::warning('Dosen data not found for user: ' . $user->email);
                return redirect()->route('home')->with('error', 'Data dosen tidak ditemukan.');
            }

            $kelas = Kelas::where('kode_matakuliah', $mk->kode_mk)
                ->where('nip', $dosen->nip)
                ->whereHas('krs', function ($query) use ($nim) {
                    $query->where('nim', $nim);
                })
                ->exists();

            if (!$kelas) {
                Log::warning('Dosen ' . $dosen->nip . ' tidak mengajar MK: ' . $mk->kode_mk . ' untuk NIM: ' . $nim);
                return redirect()->route('home')->with('error', 'Anda tidak berhak menginput nilai untuk mata kuliah ini.');
            }
        }

        // Perbaikan: Tambahkan $mk ke dalam use
        $cpmks = $mk->cpmks()->with([
            'nilaiCpmks' => function ($query) use ($mahasiswa, $mk) {
                $query->where('mahasiswa_id', $mahasiswa->id)
                    ->where('mk_id', $mk->id);
            }
        ])->get();

        $minStandard = $cpmks->isNotEmpty() ? ($cpmks->first()->pivot->min_standard ?? 70) : 70;

        // Debug: Cek nilai dari database
        $nilaiCpmks = NilaiCpmk::where('mahasiswa_id', $mahasiswa->id)
            ->where('mk_id', $mk->id)
            ->get();
        Log::info('Nilai CPMK dari DB untuk NIM ' . $nim . ', MK ' . $kode_mk . ': ' . $nilaiCpmks->toJson());

        return view('nilai_mahasiswa.index', compact('mahasiswa', 'mk', 'cpmks', 'minStandard'));
    }

    // Simpan nilai
    public function store(Request $request)
    {
        $user = Auth::user();
        Log::info('Request data: ' . json_encode($request->all()));

        $mk_id = $request->input('mk_id');
        $min_standard = $request->input('min_standard');
        $nim = $request->input('nim');

        $mahasiswa = Mahasiswa::where('nim', $nim)->firstOrFail();
        $mahasiswa_id = $mahasiswa->id;
        Log::info('Converted NIM: ' . $nim . ' to Mahasiswa ID: ' . $mahasiswa_id);

        $mk = Mk::findOrFail($mk_id);
        Log::info('MK ID: ' . $mk_id . ', Kode MK: ' . $mk->kode_mk);

        // Validasi dosen (tetap sama)
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
        foreach ($request->except(['_token', 'mk_id', 'min_standard', 'nim']) as $key => $value) {
            if (preg_match('/nilai_(\d+)_(\d+)/', $key, $matches)) {
                $input_mahasiswa_id = $matches[1];
                $cpmk_id = $matches[2];
                Log::info('Input: mahasiswa_id=' . $input_mahasiswa_id . ', cpmk_id=' . $cpmk_id . ', nilai=' . $value);

                $nilai = NilaiCpmk::updateOrCreate(
                    [
                        'mahasiswa_id' => $mahasiswa_id,
                        'mk_id' => $mk_id,
                        'cpmk_id' => $cpmk_id,
                    ],
                    [
                        'nilai' => $value,
                    ]
                );
                Log::info('Saved Nilai: Mahasiswa ID ' . $mahasiswa_id . ', MK ID ' . $mk_id . ', CPMK ID ' . $cpmk_id . ', Nilai ' . $value);
            }
        }

        return redirect()->back()->with('success', 'Nilai berhasil disimpan!');
    }
}