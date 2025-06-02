<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DosenController extends Controller
{
    public function index()
    {
        $kodeProdi = Auth::user()->kode_prodi;
        $dosens = User::where('role', 'dosen')->where('kode_prodi', $kodeProdi)->get();
        return view('dosen.index', compact('dosens'));
    }

    public function create()
    {
        return view('dosen.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|unique:users,nip',
            'password' => 'required|string|min:8',
            'kode_prodi' => 'required|string',
        ]);

        User::create([
            'name' => $request->name,
            'nip' => $request->nip,
            'password' => Hash::make($request->password),
            'role' => 'dosen',
            'kode_prodi' => Auth::user()->kode_prodi, // Tetap menggunakan kode_prodi KPS
        ]);

        return redirect()->route('dosen.index')->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $dosen = User::where('id', $id)->where('kode_prodi', Auth::user()->kode_prodi)->firstOrFail();
        return response()->json($dosen);
    }

    public function update(Request $request, $id)
    {
        $dosen = User::where('id', $id)->where('kode_prodi', Auth::user()->kode_prodi)->firstOrFail();
        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|unique:users,nip,' . $id,
            'kode_prodi' => 'required|string',
        ]);

        $dosen->update([
            'name' => $request->name,
            'nip' => $request->nip,
            'kode_prodi' => Auth::user()->kode_prodi, // Tetap menggunakan kode_prodi KPS
        ]);

        if ($request->filled('password')) {
            $dosen->password = Hash::make($request->password);
            $dosen->save();
        }

        return redirect()->route('dosen.index')->with('success', 'Dosen berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $dosen = User::where('id', $id)->where('kode_prodi', Auth::user()->kode_prodi)->firstOrFail();
        $dosen->delete();
        return redirect()->route('dosen.index')->with('success', 'Dosen berhasil dihapus.');
    }
}