<?php

namespace App\Http\Controllers;

use App\Models\Cpmk;
use Illuminate\Http\Request;

class CpmkController extends Controller
{
    // Method untuk menampilkan semua data CPL
    public function index()
    {
        // Mengambil semua data CPL dari model
        $cpmk = Cpmk::all();

        // Mengirim data ke view CPL.index
        return view('CPMK.index', compact('cpmk'));
    }

    public function create()
    {
        // Mengarahkan ke halaman create
        return view('CPMK.create');
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'kode_cpmk' => 'required|string|max:255',
            'deskripsi' => 'required|string',

        ]);

        // Menyimpan data ke database
        Cpmk::create([
            'kode_cpmk' => $request->kode_cpmk,
            'deskripsi' => $request->deskripsi,

        ]);

        // Redirect ke halaman daftar dengan pesan sukses
        return redirect()->route('cpmk.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        // Menemukan data berdasarkan ID dan mengarahkan ke halaman edit
        $cpmk = Cpmk::findOrFail($id);
        return view('CPMK.edit', compact('cpmk'));
    }

    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'kode_cpmk' => 'required|string|max:255',
            'deskripsi' => 'required|string',

        ]);

        // Menemukan data berdasarkan ID dan memperbarui data
        $cpmk = Cpmk::findOrFail($id);
        $cpmk->update([
            'kode_cpmk' => $request->kode_cpmk,
            'deskripsi' => $request->deskripsi,

        ]);

        // Redirect ke halaman daftar dengan pesan sukses
        return redirect()->route('cpmk.index')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy($id)
    {
        // Menemukan data berdasarkan ID dan menghapusnya
        $cpmk = Cpmk::findOrFail($id);
        $cpmk->delete();

        // Redirect ke halaman daftar dengan pesan sukses
        return redirect()->route('cpmk.index')->with('success', 'Data berhasil dihapus');
    }
}
