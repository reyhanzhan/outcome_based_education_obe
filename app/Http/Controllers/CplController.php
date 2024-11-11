<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use Illuminate\Http\Request;

class CplController extends Controller
{
    // Method untuk menampilkan semua data CPL
    public function index()
    {
        // Mengambil semua data CPL dari model
        $cpl = Cpl::all();

        // Mengirim data ke view CPL.index
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
            
        ]);
    
        // Menyimpan data ke database
        Cpl::create([
            'kode_cpl' => $request->kode_cpl,
            'deskripsi' => $request->deskripsi,
           
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
        // Validasi input
        $request->validate([
            'kode_cpl' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            
        ]);
    
        // Menemukan data berdasarkan ID dan memperbarui data
        $cpl = Cpl::findOrFail($id);
        $cpl->update([
            'kode_cpl' => $request->kode_cpl,
            'deskripsi' => $request->deskripsi,
            
        ]);

        // Redirect ke halaman daftar dengan pesan sukses
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
