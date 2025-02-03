<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use Illuminate\Http\Request;

class CplController extends Controller
{
    public function index()
    {
        // Menampilkan semua data PL
        $cpl = Cpl::all();
        return view('CPL.index', compact('cpl'));
    }

    public function create()
    {
        // Mengarahkan ke halaman create
        return view('CPL.create');
    }

    public function store(Request $request)
    {
        // Validasi input
        
        $request->validate([
            'kode_cpl' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'kategori' => 'required|string',
        ]);

        // Menyimpan data ke database
        CPl::create([
            'kode_cpl' => $request->kode_cpl,
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori,
        ]);

        // Redirect ke halaman daftar dengan pesan sukses
        return redirect()->route('cpl.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        // Menemukan data berdasarkan ID dan mengarahkan ke halaman edit
        $cpl = Cpl::findOrFail($id);
        return view('CPL.edit', compact('cpl'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'kode_cpl' => 'required|string',
            'deskripsi' => 'required|string',
            'kategori' => 'required|string',
        ]);

        $cpl = Cpl::findOrFail($id);
        $cpl->update([
            'kode_cpl' => $request->kode_cpl,
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori,
        ]);

        return redirect()->route('cpl.index')->with('success', 'Data berhasil diperbarui');
    }


    public function destroy($id)
    {
        // Menemukan data berdasarkan ID dan menghapusnya
        $cpl = Cpl::findOrFail($id);
        $cpl->delete();

        // Redirect ke halaman daftar dengan pesan sukses
        return redirect()->route('cpl.index')->with('success', 'Data berhasil dihapus');
    }
}
