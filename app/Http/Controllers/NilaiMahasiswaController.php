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
    public function chooseMahasiswa()
    {
        $user = Auth::user();
        $mahasiswas = collect();

        if ($user->role === 'dosen') {
            $dosen = $user->dosen;
            if (!$dosen) {
                Log::warning('Dosen data not found for user: ' . $user->email);
                return redirect()->route('home')->with('error', 'Data dosen tidak ditemukan.');
            }

            // Ambil mahasiswa yang terdaftar di kelas yang diajar oleh dosen
            $kelas = Kelas::where('nip', $dosen->nip)->with([
                'krs' => function ($query) {
                    $query->with('mahasiswa');
                }
            ])->get();

            $mahasiswas = $kelas->flatMap(function ($kelas) {
                return $kelas->krs->map(function ($krs) {
                    return $krs->mahasiswa;
                });
            })->unique('id');
        } elseif ($user->role === 'kps') {
            // KPS bisa melihat semua mahasiswa
            $mahasiswas = Mahasiswa::all();
        } else {
            Log::warning('Unauthorized role for user: ' . $user->email);
            return redirect()->route('home')->with('error', 'Role tidak diizinkan.');
        }

        return view('nilai_mahasiswa.choose_mahasiswa', compact('mahasiswas'));
    }

    // Halaman untuk memilih mata kuliah berdasarkan mahasiswa
    public function chooseMataKuliah(Request $request, $nim)
    {
        // ✅ Ambil daftar mahasiswa & periode
        $mahasiswas = Mahasiswa::all();
        $periodes = Krs::distinct()->pluck('periode');

        // ✅ Validasi mahasiswa berdasarkan nim
        $mahasiswa = Mahasiswa::where('nim', $nim)->first();
        if (!$mahasiswa) {
            return redirect()->route('nilai.mahasiswa.choose')->with('error', 'Mahasiswa tidak ditemukan!');
        }

        // ✅ Ambil daftar mata kuliah mahasiswa di periode yang dipilih
        $mks = [];
        if ($request->has('periode')) {
            $periode = $request->input('periode');

            // Query daftar mata kuliah yang diambil oleh mahasiswa di periode tersebut
            $mks = Krs::where('nim', $nim)
                ->where('periode', $periode)
                ->join('mk', 'krs.kode_matakuliah', '=', 'mk.kode_mk')
                ->join('kelas', function ($join) {
                    $join->on('krs.kode_matakuliah', '=', 'kelas.kode_matakuliah')
                        ->on('krs.kelas', '=', 'kelas.kelas');
                })
                ->join('dosen', 'kelas.nip', '=', 'dosen.nip')
                ->select('mk.kode_mk', 'mk.deskripsi', 'kelas.kelas', 'dosen.nama as dosen')
                ->get();
        } else {
            $periode = null; // Jika belum dipilih, kosongkan periode
        }

        return view('nilai_mahasiswa.choose_mata_kuliah', compact('mahasiswas', 'periodes', 'mks', 'mahasiswa', 'periode'));
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

            // Validasi bahwa dosen mengajar mata kuliah ini
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

        // Ambil CPMK yang terkait dengan mata kuliah ini
        $cpmks = $mk->cpmks()->get();

        // Ambil standar minimum dari pivot table (default 70 jika belum diset)
        $minStandard = $cpmks->isNotEmpty() ? ($cpmks->first()->pivot->min_standard ?? 70) : 70;

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

        // Validasi dan konversi nim ke mahasiswa_id
        $mahasiswa = Mahasiswa::where('nim', $nim)->firstOrFail();
        $mahasiswa_id = $mahasiswa->id;
        Log::info('Converted NIM: ' . $nim . ' to Mahasiswa ID: ' . $mahasiswa_id);

        $mk = Mk::findOrFail($mk_id);

        if ($user->role === 'dosen') {
            $dosen = $user->dosen;
            if (!$dosen) {
                Log::warning('Dosen data not found for user: ' . $user->email);
                return redirect()->back()->with('error', 'Data dosen tidak ditemukan.');
            }

            // Validasi bahwa dosen mengajar mata kuliah ini dan mahasiswa mengambilnya
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

        // Simpan standar minimum ke pivot table cpmk_mk
        $cpmks = $mk->cpmks()->get();
        foreach ($cpmks as $cpmk) {
            $mk->cpmks()->updateExistingPivot($cpmk->id, ['min_standard' => $min_standard]);
        }

        // Simpan nilai per CPMK untuk mahasiswa
        foreach ($request->except(['_token', 'mk_id', 'min_standard', 'nim']) as $key => $value) {
            if (preg_match('/nilai_(\d+)_(\d+)/', $key, $matches)) {
                $cpmk_id = $matches[2];

                // Simpan atau update nilai
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