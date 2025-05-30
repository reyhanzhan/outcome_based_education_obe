<?php

namespace App\Http\Controllers;

use App\Models\Cpmk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CpmkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Pastikan hanya user yang login yang bisa akses
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
        $cpmk = Cpmk::where('kode_prodi', $kodeProdi)->get();
        return view('CPMK.index', compact('cpmk'));
    }

    public function create()
    {
        return view('CPMK.create');
    }

    public function store(Request $request)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini. Hubungi admin.');
            }

            $request->validate([
                'kode_cpmk' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('cpmk')->where(function ($query) use ($kodeProdi) {
                        return $query->where('kode_prodi', $kodeProdi);
                    }),
                ],
                'deskripsi' => 'required|string',
            ]);

            $data = [
                'kode_cpmk' => $request->kode_cpmk,
                'deskripsi' => $request->deskripsi,
                'kode_prodi' => $kodeProdi,
            ];

            $cpmk = Cpmk::create($data);

            if (!$cpmk) {
                throw new \Exception('Gagal menyimpan data CPMK.');
            }

            return redirect()->route('cpmk.index')->with('success', 'Data CPMK berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $kodeProdi = Auth::user()->kode_prodi;
        $cpmk = Cpmk::where('kode_prodi', $kodeProdi)->findOrFail($id);
        return view('CPMK.edit', compact('cpmk'));
    }

    public function update(Request $request, $id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $cpmk = Cpmk::where('kode_prodi', $kodeProdi)->findOrFail($id);

            $request->validate([
                'kode_cpmk' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('cpmk')->where(function ($query) use ($kodeProdi) {
                        return $query->where('kode_prodi', $kodeProdi);
                    })->ignore($cpmk->id),
                ],
                'deskripsi' => 'required|string',
            ]);

            $cpmk->update([
                'kode_cpmk' => $request->kode_cpmk,
                'deskripsi' => $request->deskripsi,
                'kode_prodi' => $kodeProdi,
            ]);

            return redirect()->route('cpmk.index')->with('success', 'Data CPMK berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $cpmk = Cpmk::where('kode_prodi', $kodeProdi)->findOrFail($id);
            $cpmk->delete();

            return redirect()->route('cpmk.index')->with('success', 'Data CPMK berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
}