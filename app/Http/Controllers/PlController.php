<?php

namespace App\Http\Controllers;

use App\Models\Pl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PlController extends Controller
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
        $pls = Pl::where('kode_prodi', $kodeProdi)->get();
        return view('PL.index', compact('pls'));
    }

    public function create()
    {
        return view('PL.create');
    }

    public function store(Request $request)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini. Hubungi admin.');
            }

            $request->validate([
                'kode_pl' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('pl')->where(function ($query) use ($kodeProdi) {
                        return $query->where('kode_prodi', $kodeProdi);
                    }),
                ],
                'deskripsi' => 'required|string',
                'kategori' => 'required|string',
            ]);

            $data = [
                'kode_pl' => $request->kode_pl,
                'deskripsi' => $request->deskripsi,
                'kategori' => $request->kategori,
                'kode_prodi' => $kodeProdi,
            ];

            $pl = Pl::create($data);

            if (!$pl) {
                throw new \Exception('Gagal menyimpan data PL.');
            }

            return redirect()->route('pl.index')->with('success', 'Data PL berhasil ditambahkan.');
        } catch (\Exception $e) {
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $kodeProdi = Auth::user()->kode_prodi;
        $pl = Pl::where('kode_prodi', $kodeProdi)->findOrFail($id);
        return view('PL.edit', compact('pl'));
    }

    public function update(Request $request, $id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $pl = Pl::where('kode_prodi', $kodeProdi)->findOrFail($id);

            $request->validate([
                'kode_pl' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('pl')->where(function ($query) use ($kodeProdi) {
                        return $query->where('kode_prodi', $kodeProdi);
                    })->ignore($pl->id),
                ],
                'deskripsi' => 'required|string',
                'kategori' => 'required|string',
            ]);

            $pl->update([
                'kode_pl' => $request->kode_pl,
                'deskripsi' => $request->deskripsi,
                'kategori' => $request->kategori,
                'kode_prodi' => $kodeProdi,
            ]);

            return redirect()->route('pl.index')->with('success', 'Data PL berhasil diperbarui');
        } catch (\Exception $e) {
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $pl = Pl::where('kode_prodi', $kodeProdi)->findOrFail($id);
            $pl->delete();

            return redirect()->route('pl.index')->with('success', 'Data PL berhasil dihapus');
        } catch (\Exception $e) {
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
}