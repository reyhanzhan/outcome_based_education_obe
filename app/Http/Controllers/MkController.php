<?php

namespace App\Http\Controllers;

use App\Models\Mk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MkController extends Controller
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
        $kodeProdi = Auth::user()->kode_prodi;
        $mk = Mk::where('kode_prodi', $kodeProdi)->get();
        return view('MK.index', compact('mk'));
    }

    public function create()
    {
        return view('MK.create');
    }

    public function store(Request $request)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini. Hubungi admin.');
            }

            $request->validate([
                'kode_mk' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('mk')->where(function ($query) use ($kodeProdi) {
                        return $query->where('kode_prodi', $kodeProdi);
                    }),
                ],
                'deskripsi' => 'required|string',
                'sks' => 'required|integer|min:1',
                'jenis_mk' => 'required|in:Kuliah,Skripsi', // Validasi untuk jenis_mk
            ]);

            $data = [
                'kode_mk' => $request->kode_mk,
                'deskripsi' => $request->deskripsi,
                'sks' => $request->sks,
                'jenis_mk' => $request->jenis_mk, // Tambahkan jenis_mk
                'kode_prodi' => $kodeProdi,
            ];

            

            $mk = Mk::create($data);

            if (!$mk) {
                throw new \Exception('Gagal menyimpan data MK.');
            }

            return redirect()->route('mk.index')->with([
                'success' => 'Data MK berhasil ditambahkan!',
                'new_id' => $mk->id,
            ]);
        } catch (\Exception $e) {
            back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $kodeProdi = Auth::user()->kode_prodi;
        $mk = Mk::where('kode_prodi', $kodeProdi)->findOrFail($id);
        return view('MK.edit', compact('mk'));
    }

    public function update(Request $request, $id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $mk = Mk::where('kode_prodi', $kodeProdi)->findOrFail($id);

            $request->validate([
                'kode_mk' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('mk')->where(function ($query) use ($kodeProdi) {
                        return $query->where('kode_prodi', $kodeProdi);
                    })->ignore($mk->id),
                ],
                'deskripsi' => 'required|string',
                'sks' => 'required|integer|min:1',
                'jenis_mk' => 'required|in:Kuliah,Skripsi', // Validasi untuk jenis_mk
            ]);

            $mk->update([
                'kode_mk' => $request->kode_mk,
                'deskripsi' => $request->deskripsi,
                'sks' => $request->sks,
                'jenis_mk' => $request->jenis_mk, // Tambahkan jenis_mk
                'kode_prodi' => $kodeProdi,
            ]);

            return redirect()->route('mk.index')->with('success', 'Data MK berhasil diperbarui');
        } catch (\Exception $e) {
            back()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $mk = Mk::where('kode_prodi', $kodeProdi)->findOrFail($id);
            $mk->delete();

            return redirect()->route('mk.index')->with('success', 'Data MK berhasil dihapus');
        } catch (\Exception $e) {
            back()->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
}