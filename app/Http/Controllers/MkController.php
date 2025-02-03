<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mk;

class MkController extends Controller
{
    /// Method untuk menampilkan semua data CPL
    public function index(Request $request)
    {
        $mk = Mk::all();
        return view('MK.index', compact('mk'));
    }


    public function create()
    {
        // Mengarahkan ke halaman create
        return view('MK.create');
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'kode_mk' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'sks' => 'required|string',
            'wptwp' => 'required|string',

        ]);

        // Menyimpan data ke database
        // Mk::create([
        //     'kode_mk' => $request->kode_mk,
        //     'deskripsi' => $request->deskripsi,
        //     'sks' => $request->sks,
        //     'wptwp' => $request->wptwp,

        // ]);

        $mk = Mk::create([
            'kode_mk' => $request->kode_mk,
            'deskripsi' => $request->deskripsi,
            'sks' => $request->sks,
            'wptwp' => $request->wptwp,
        ]);

        // Redirect ke halaman daftar dengan pesan sukses
        // Redirect ke halaman daftar dengan pesan sukses dan ID baru
        return redirect()->route('mk.index')->with([
            'success' => 'Data MK berhasil ditambahkan!',
            'new_id' => $mk->id // Mendapatkan ID dari data yang baru dibuat
        ]);
    }

    public function edit($id)
    {
        // Menemukan data berdasarkan ID dan mengarahkan ke halaman edit
        $mk = Mk::findOrFail($id);
        return view('MK.edit', compact('mk'));
    }

    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'kode_mk' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'sks' => 'required|string',
            'wptwp' => 'required|string',

        ]);

        // Menemukan data berdasarkan ID dan memperbarui data
        $mk = Mk::findOrFail($id);
        $mk->update([
            'kode_mk' => $request->kode_mk,
            'deskripsi' => $request->deskripsi,
            'sks' => $request->sks,
            'wptwp' => $request->wptwp,
        ]);

        // Redirect ke halaman daftar dengan pesan sukses
        return redirect()->route('mk.index')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy($id)
    {
        // Menemukan data berdasarkan ID dan menghapusnya
        $mk = Mk::findOrFail($id);
        $mk->delete();

        // Redirect ke halaman daftar dengan pesan sukses
        return redirect()->route('mk.index')->with('success', 'Data berhasil dihapus');
    }
}
