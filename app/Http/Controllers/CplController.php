<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use App\Models\Mahasiswa;
use App\Models\NilaiCpl;
use App\Models\NilaiCpmk;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CplController extends Controller
{
    public function index()
    {
        $mahasiswas = Mahasiswa::all();
        $cpls = Cpl::with('cpmks')->get();
        return view('penilaian_cpl.index', compact('mahasiswas', 'cpls'));
    }

    public function list()
    {
        $cpls = Cpl::with('cpmks')->get();
        return view('cpl.index', compact('cpls')); // Buat view baru untuk CRUD CPL
    }

    public function create()
    {
        // Mengarahkan ke halaman create
        return view('cpl.create'); // Ubah ke 'cpl.create' untuk konsistensi
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
        Cpl::create([
            'kode_cpl' => $request->kode_cpl,
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori,
        ]);

        // Redirect ke halaman daftar dengan pesan sukses
        return redirect()->route('cpl.list')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        // Menemukan data berdasarkan ID dan mengarahkan ke halaman edit
        $cpl = Cpl::findOrFail($id);
        return view('cpl.edit', compact('cpl')); // Ubah ke 'cpl.edit' untuk konsistensi
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

        return redirect()->route('cpl.list')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy($id)
    {
        // Menemukan data berdasarkan ID dan menghapusnya
        $cpl = Cpl::findOrFail($id);
        $cpl->delete();

        // Redirect ke halaman daftar dengan pesan sukses
        return redirect()->route('cpl.list')->with('success', 'Data berhasil dihapus');
    }

    public function show($mahasiswa_id)
    {
        $mahasiswa = Mahasiswa::findOrFail($mahasiswa_id);
        $cpls = Cpl::with('cpmks')->get();
        $nilaiCpls = NilaiCpl::where('mahasiswa_id', $mahasiswa_id)->get();

        $cplScores = [];
        foreach ($cpls as $cpl) {
            $totalScore = 0;
            $totalBobot = 0;

            foreach ($cpl->cpmks as $cpmk) {
                $bobotCplCpmk = $cpmk->pivot->bobot ?? 0; // Ambil bobot dari cpmk_cpl
                $nilaiCpmk = NilaiCpmk::where('mahasiswa_id', $mahasiswa_id)
                    ->where('cpmk_id', $cpmk->id)
                    ->first();

                if ($nilaiCpmk) {
                    $nilai = $nilaiCpmk->nilai ?? 0;
                    $bobotMk = $cpmk->mks()->first()->pivot->bobot ?? 0; // Ambil bobot dari cpmk_mk
                    $score = ($bobotMk * $nilai) / 100; // Hitung kontribusi CPMK ke CPL
                    $totalScore += ($bobotCplCpmk * $score) / 100; // Bobot CPL-CPMK
                    $totalBobot += $bobotCplCpmk;
                }
            }

            $cplScores[$cpl->id] = $totalBobot > 0 ? ($totalScore / ($totalBobot / 100)) : 0;
            NilaiCpl::updateOrCreate(
                ['mahasiswa_id' => $mahasiswa_id, 'cpl_id' => $cpl->id],
                ['nilai' => round($cplScores[$cpl->id], 2)]
            );
        }

        return view('penilaian_cpl.show', compact('mahasiswa', 'cpls', 'cplScores', 'nilaiCpls'));
    }

    public function calculateCplScore($mahasiswa_id, $cpl_id)
    {
        $cpl = Cpl::findOrFail($cpl_id);
        $totalScore = 0;
        $totalBobot = 0;

        foreach ($cpl->cpmks as $cpmk) {
            $bobotCplCpmk = $cpmk->pivot->bobot ?? 0; // Ambil bobot dari cpmk_cpl
            $nilaiCpmk = NilaiCpmk::where('mahasiswa_id', $mahasiswa_id)
                ->where('cpmk_id', $cpmk->id)
                ->first();

            if ($nilaiCpmk) {
                $nilai = $nilaiCpmk->nilai ?? 0;
                $bobotMk = $cpmk->mks()->first()->pivot->bobot ?? 0; // Ambil bobot dari cpmk_mk
                $score = ($bobotMk * $nilai) / 100;
                $totalScore += ($bobotCplCpmk * $score) / 100;
                $totalBobot += $bobotCplCpmk;
            }
        }

        return $totalBobot > 0 ? round(($totalScore / ($totalBobot / 100)), 2) : 0;
    }
}