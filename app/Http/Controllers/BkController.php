<?php

namespace App\Http\Controllers;

use App\Models\Bk;
use Illuminate\Http\Request;

class BkController extends Controller
{
    // Method untuk menampilkan semua data CPL
    public function index()
    {
        // Mengambil semua data CPL dari model
        $bk = Bk::all();

        // Mengirim data ke view CPL.index
        return view('BK.index', compact('bk'));
    }

    public function create()
    {
        // Mengarahkan ke halaman create
        return view('BK.create');
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'kode_bk' => 'required|string|max:255',
            'deskripsi' => 'required|string',

        ]);

        // Menyimpan data ke database
        Bk::create([
            'kode_bk' => $request->kode_bk,
            'deskripsi' => $request->deskripsi,

        ]);

        // Redirect ke halaman daftar dengan pesan sukses
        return redirect()->route('bk.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        // Menemukan data berdasarkan ID dan mengarahkan ke halaman edit
        $bk = Bk::findOrFail($id);
        return view('BK.edit', compact('bk'));
    }

    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'kode_bk' => 'required|string|max:255',
            'deskripsi' => 'required|string',

        ]);

        // Menemukan data berdasarkan ID dan memperbarui data
        $bk = Bk::findOrFail($id);
        $bk->update([
            'kode_bk' => $request->kode_bk,
            'deskripsi' => $request->deskripsi,

        ]);

        // Redirect ke halaman daftar dengan pesan sukses
        return redirect()->route('bk.index')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy($id)
    {
        // Menemukan data berdasarkan ID dan menghapusnya
        $bk = Bk::findOrFail($id);
        $bk->delete();

        // Redirect ke halaman daftar dengan pesan sukses
        return redirect()->route('bk.index')->with('success', 'Data berhasil dihapus');
    }
}
