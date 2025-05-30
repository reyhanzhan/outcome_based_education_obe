<?php

namespace App\Http\Controllers;

use App\Models\Bk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BkController extends Controller
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
        $bk = Bk::where('kode_prodi', $kodeProdi)->get();
        return view('BK.index', compact('bk'));
    }

    public function create()
    {
        return view('BK.create');
    }

    public function store(Request $request)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini. Hubungi admin.');
            }

            $request->validate([
                'kode_bk' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('bk')->where(function ($query) use ($kodeProdi) {
                        return $query->where('kode_prodi', $kodeProdi);
                    }),
                ],
                'deskripsi' => 'required|string',
            ]);

            $data = [
                'kode_bk' => $request->kode_bk,
                'deskripsi' => $request->deskripsi,
                'kode_prodi' => $kodeProdi,
            ];

            $bk = Bk::create($data);

            if (!$bk) {
                throw new \Exception('Gagal menyimpan data BK.');
            }

            return redirect()->route('bk.index')->with('success', 'Data BK berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $kodeProdi = Auth::user()->kode_prodi;
        $bk = Bk::where('kode_prodi', $kodeProdi)->findOrFail($id);
        return view('BK.edit', compact('bk'));
    }

    public function update(Request $request, $id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $bk = Bk::where('kode_prodi', $kodeProdi)->findOrFail($id);

            $request->validate([
                'kode_bk' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('bk')->where(function ($query) use ($kodeProdi) {
                        return $query->where('kode_prodi', $kodeProdi);
                    })->ignore($bk->id),
                ],
                'deskripsi' => 'required|string',
            ]);

            $bk->update([
                'kode_bk' => $request->kode_bk,
                'deskripsi' => $request->deskripsi,
                'kode_prodi' => $kodeProdi,
            ]);

            return redirect()->route('bk.index')->with('success', 'Data BK berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $bk = Bk::where('kode_prodi', $kodeProdi)->findOrFail($id);
            $bk->delete();

            return redirect()->route('bk.index')->with('success', 'Data BK berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
}