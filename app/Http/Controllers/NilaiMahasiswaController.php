<?php

namespace App\Http\Controllers;

use App\Models\Mk;
use App\Models\Cpmk;
use App\Models\Mahasiswa;
use App\Models\NilaiCpmk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class NilaiMahasiswaController extends Controller
{
    public function chooseMk()
    {
        $mks = Mk::all(); // Ambil semua MK untuk dropdown
        return view('nilai_mahasiswa.choose', compact('mks'));
    }

    public function index($mk_id)
    {
        $mk = Mk::findOrFail($mk_id); // Pastikan mk_id valid dan ada data
        $mahasiswas = Mahasiswa::all();
        $cpmks = Cpmk::whereHas('mks', function ($query) use ($mk_id) {
            $query->where('mk_id', $mk_id);
        })->with([
                    'mks' => function ($query) use ($mk_id) {
                        $query->where('mk_id', $mk_id)->withPivot('bobot', 'min_standard');
                    }
                ])->get();

        // Ambil min_standard dari session atau default 55
        $minStandard = session('min_standard_' . $mk_id, 55);

        // Kembalikan view dengan data
        return view('nilai_mahasiswa.index', compact('mk', 'mahasiswas', 'cpmks', 'minStandard'));
    }

    public function store(Request $request)
    {
        try {
            Log::info('Data yang diterima:', $request->all()); // Logging untuk debugging

            $mk_id = $request->input('mk_id');
            $mk = Mk::findOrFail($mk_id);

            // Ambil min_standard dari request jika ada
            $minStandard = $request->input('min_standard', 55); // Default 55 jika tidak ada
            session(['min_standard_' . $mk_id => $minStandard]); // Simpan ke session berdasarkan mk_id

            // Perbarui min_standard untuk semua entri cpmk_mk terkait MK ini
            DB::table('cpmk_mk')
                ->where('mk_id', $mk_id)
                ->update(['min_standard' => $minStandard]);

            // Validasi input untuk semua mahasiswa dan CPMK
            $data = $request->except(['_token', 'mk_id', 'min_standard']);
            $errors = [];

            foreach ($data as $key => $value) {
                if (strpos($key, 'nilai_') === 0) {
                    $parts = explode('_', $key);
                    $mahasiswa_id = $parts[1];
                    $cpmk_id = $parts[2];

                    $request->validate([
                        "nilai_{$mahasiswa_id}_{$cpmk_id}" => 'nullable|numeric|min:0|max:100'
                    ]);

                    $nilai = $value ? (float) $value : 0;
                    $bobot = Cpmk::find($cpmk_id)->mks()->where('mk_id', $mk_id)->first()->pivot->bobot ?? 0;

                    if ($bobot <= 0) {
                        Log::warning('Bobot CPMK belum disetel untuk CPMK: ' . $cpmk_id . ', MK: ' . $mk_id);
                        $errors[] = "Bobot CPMK untuk CPMK {$cpmk_id} belum disetel!";
                        continue;
                    }

                    NilaiCpmk::updateOrCreate(
                        [
                            'mahasiswa_id' => $mahasiswa_id,
                            'mk_id' => $mk_id,
                            'cpmk_id' => $cpmk_id
                        ],
                        ['nilai' => $nilai]
                    );
                }
            }

            if (!empty($errors)) {
                return redirect()->back()->with('error', implode(' ', $errors));
            }

            return redirect()->back()->with('success', 'Semua nilai dan standar minimum berhasil disimpan!');
        } catch (\Exception $e) {
            Log::error('Error menyimpan nilai: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan nilai: ' . $e->getMessage());
        }
    }
}