<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MahasiswaController extends Controller
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
            $mahasiswas = Mahasiswa::where('kode_prodi', $kodeProdi)->get();
            return view('mahasiswa.index', compact('mahasiswas'));
        } catch (\Exception $e) {
            Log::error('Error fetching mahasiswa: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data mahasiswa.');
        }
    }

    public function create()
    {
        $kodeProdi = Auth::user()->kode_prodi;
        return view('mahasiswa.create', compact('kodeProdi'));
    }

    public function store(Request $request)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini.');
            }

            $request->validate([
                'nim' => 'required|unique:mahasiswa,nim|alpha_num|size:10',
                'nama' => 'required|string|max:255',
                'periode_masuk' => 'required|string|max:9', // Misalnya '2025' atau '2025-06'
                'sistem_kuliah' => 'required|string|in:Reguler Pagi,Reguler Sore',
                'jalur_penerimaan' => 'required|string|in:SBMPTN,Seleksi Mandiri,Seleksi Mandiri PTS,Ujian Masuk Bersama PTS(UMB-PTS)',
                'gelombang_daftar' => 'required|string|in:K1-01,K1-02,K1-03',
                'agama' => 'required|string|in:Islam,Kristen,Hindu,Buddha,Khonghucu',
            ]);

            Mahasiswa::create([
                'nim' => $request->nim,
                'nama' => $request->nama,
                'periode_masuk' => $request->periode_masuk,
                'sistem_kuliah' => $request->sistem_kuliah,
                'jalur_penerimaan' => $request->jalur_penerimaan,
                'gelombang_daftar' => $request->gelombang_daftar,
                'agama' => $request->agama,
                'kode_prodi' => $kodeProdi,
            ]);

            return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error storing mahasiswa: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data mahasiswa: ' . $e->getMessage())->withInputs();
        }
    }

    public function edit($id)
    {
        try {
            $mahasiswa = Mahasiswa::findOrFail($id);
            if ($mahasiswa->kode_prodi !== Auth::user()->kode_prodi) {
                abort(403, 'Anda tidak memiliki akses ke data mahasiswa ini.');
            }
            return view('mahasiswa.edit', compact('mahasiswa'));
        } catch (\Exception $e) {
            Log::error('Error fetching mahasiswa for edit: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data mahasiswa.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $mahasiswa = Mahasiswa::findOrFail($id);
            if ($mahasiswa->kode_prodi !== Auth::user()->kode_prodi) {
                abort(403, 'Anda tidak memiliki akses ke data mahasiswa ini.');
            }

            $request->validate([
                'nim' => 'required|alpha_num|size:10|unique:mahasiswa,nim,' . $id,
                'nama' => 'required|string|max:255',
                'periode_masuk' => 'required|string|max:9',
                'sistem_kuliah' => 'required|string|in:Reguler Pagi,Reguler Sore',
                'jalur_penerimaan' => 'required|string|in:SBMPTN,Seleksi Mandiri,Seleksi Mandiri PTS,Ujian Masuk Bersama PTS(UMB-PTS)',
                'gelombang_daftar' => 'required|string|in:K1-01,K1-02,K1-03',
                'agama' => 'required|string|in:Islam,Kristen,Hindu,Buddha,Khonghucu',
            ]);

            $data = [
                'nim' => $request->nim,
                'nama' => $request->nama,
                'periode_masuk' => $request->periode_masuk,
                'sistem_kuliah' => $request->sistem_kuliah,
                'jalur_penerimaan' => $request->jalur_penerimaan,
                'gelombang_daftar' => $request->gelombang_daftar,
                'agama' => $request->agama,
                'kode_prodi' => Auth::user()->kode_prodi,
            ];

            $mahasiswa->update($data);

            return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Error updating mahasiswa: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data mahasiswa: ' . $e->getMessage())->withInputs();
        }
    }

    public function destroy($id)
    {
        try {
            $mahasiswa = Mahasiswa::findOrFail($id);
            if ($mahasiswa->kode_prodi !== Auth::user()->kode_prodi) {
                abort(403, 'Anda tidak memiliki akses ke data mahasiswa ini.');
            }
            $mahasiswa->delete();

            return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting mahasiswa: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data mahasiswa.');
        }
    }
}