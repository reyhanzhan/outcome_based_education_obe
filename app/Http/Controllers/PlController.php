<?php

namespace App\Http\Controllers;

use App\Models\Pl;
use Illuminate\Http\Request;

class PlController extends Controller
{
    public function index()
    {
        // Menampilkan semua data PL
        $pl = Pl::all();
        return view('PL.index', compact('pl'));
    }

    public function create()
    {
        // Mengarahkan ke halaman create
        return view('PL.create');
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'kode_pl' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'unsur' => 'required|string',
        ]);

        // Menyimpan data ke database
        Pl::create([
            'kode_pl' => $request->kode_pl,
            'deskripsi' => $request->deskripsi,
            'unsur' => $request->unsur,
        ]);

        // Redirect ke halaman daftar dengan pesan sukses
        return redirect()->route('pl.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        // Menemukan data berdasarkan ID dan mengarahkan ke halaman edit
        $pl = Pl::findOrFail($id);
        return view('PL.edit', compact('pl'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'kode_pl' => 'required|string',
            'deskripsi' => 'required|string',
            'unsur' => 'required|string',
        ]);

        $pl = Pl::findOrFail($id);
        $pl->update([
            'kode_pl' => $request->kode_pl,
            'deskripsi' => $request->deskripsi,
            'unsur' => $request->unsur,
        ]);

        return redirect()->route('pl.index')->with('success', 'Data berhasil diperbarui');
    }


    public function destroy($id)
    {
        // Menemukan data berdasarkan ID dan menghapusnya
        $pl = Pl::findOrFail($id);
        $pl->delete();

        // Redirect ke halaman daftar dengan pesan sukses
        return redirect()->route('pl.index')->with('success', 'Data berhasil dihapus');
    }
}
