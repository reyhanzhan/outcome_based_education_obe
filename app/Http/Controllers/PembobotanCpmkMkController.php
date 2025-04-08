<?php

namespace App\Http\Controllers;

use App\Models\Mk;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PembobotanCpmkMkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
{
    $user = Auth::user();
    $mks = collect();

    if ($user->role === 'dosen') {
        $dosen = $user->dosen;
        if (!$dosen) {
            Log::warning('Dosen data not found for user: ' . $user->email);
            return redirect()->route('home')->with('error', 'Data dosen tidak ditemukan.');
        }

        // Ambil mata kuliah yang diajar oleh dosen
        $kelas = Kelas::where('nip', $dosen->nip)->with('mataKuliah')->get();
        $mks = $kelas->map(function ($item) {
            return $item->mataKuliah;
        })->filter()->unique('id');
    } elseif ($user->role === 'kps') {
        // KPS bisa mengakses semua mata kuliah
        $mks = Mk::whereHas('cpmks')->get();
    } else {
        Log::warning('Unauthorized role for user: ' . $user->email);
        return redirect()->route('home')->with('error', 'Role tidak diizinkan.');
    }

    $defaultMk = $mks->first();
    $cpmks = [];

    if ($defaultMk) {
        // Ambil jumlah penilaian untuk defaultMk
        $jumlahPenilaian = DB::table('cpmk_mk')
            ->where('mk_id', $defaultMk->id)
            ->value('jumlah_penilaian') ?? 3;

        // Jika jumlah_penilaian adalah 1 (data lama), update ke 3
        if ($jumlahPenilaian == 1) {
            DB::table('cpmk_mk')
                ->where('mk_id', $defaultMk->id)
                ->update(['jumlah_penilaian' => 3]);
            $jumlahPenilaian = 3;
        }

        // Set jumlah_penilaian ke defaultMk
        $defaultMk->jumlah_penilaian = $jumlahPenilaian;

        $cpmks = $defaultMk->cpmks()->withPivot('bobot')->get()->map(function ($cpmk) use ($defaultMk) {
            // Ambil teknik penilaian untuk CPMK ini
            $teknikPenilaian = DB::table('teknik_penilaian')
                ->where('mk_id', $defaultMk->id)
                ->where('cpmk_id', $cpmk->id)
                ->pluck('bobot', 'teknik')
                ->toArray();

            Log::info('CPMK Data (Index): ' . json_encode([
                'id' => $cpmk->id,
                'kode_cpmk' => $cpmk->kode_cpmk,
                'deskripsi' => $cpmk->deskripsi ?? 'Deskripsi tidak tersedia',
                'bobot' => $cpmk->pivot->bobot ?? 0,
                'teknik_penilaian' => $teknikPenilaian,
            ]));

            return (object) [
                'id' => $cpmk->id,
                'kode_cpmk' => $cpmk->kode_cpmk,
                'deskripsi' => $cpmk->deskripsi ?? 'Deskripsi tidak tersedia',
                'bobot' => $cpmk->pivot->bobot ?? 0,
                'teknik_penilaian' => $teknikPenilaian,
            ];
        });
    }

    return view('pembobotan_cpmk_mk.index', compact('mks', 'cpmks', 'defaultMk'));
}

    public function searchMk(Request $request)
    {
        $user = Auth::user();
        $query = $request->input('q'); // Parameter pencarian dari Select2

        $mksQuery = Mk::query()->whereHas('cpmks');

        // Filter berdasarkan role
        if ($user->role === 'dosen') {
            $dosen = $user->dosen;
            if (!$dosen) {
                return response()->json(['results' => []], 403);
            }

            $kelas = Kelas::where('nip', $dosen->nip)->with('mataKuliah')->get();
            $mkIds = $kelas->pluck('mataKuliah.id')->filter()->unique();
            $mksQuery->whereIn('id', $mkIds);
        }

        // Pencarian berdasarkan kode_mk atau deskripsi
        if ($query) {
            $mksQuery->where(function ($q) use ($query) {
                $q->where('kode_mk', 'like', "%{$query}%")
                  ->orWhere('deskripsi', 'like', "%{$query}%");
            });
        }

        $mks = $mksQuery->get()->map(function ($mk) {
            return [
                'id' => $mk->id,
                'text' => "{$mk->kode_mk} - {$mk->deskripsi}",
            ];
        });

        return response()->json(['results' => $mks]);
    }

    public function getJumlahPenilaian($mk_id)
{
    try {
        $user = Auth::user();
        $mk = Mk::findOrFail($mk_id);

        if ($user->role === 'dosen') {
            $dosen = $user->dosen;
            if (!$dosen) {
                Log::warning('Dosen data not found for user: ' . $user->email);
                return response()->json(['error' => 'Data dosen tidak ditemukan.'], 403);
            }

            $kelas = Kelas::where('nip', $dosen->nip)
                ->where('kode_matakuliah', $mk->kode_mk)
                ->exists();

            if (!$kelas) {
                Log::warning('Dosen ' . $dosen->nip . ' tidak mengajar MK: ' . $mk->kode_mk);
                return response()->json(['error' => 'Anda tidak berhak mengakses data ini.'], 403);
            }
        }

        // Ambil jumlah penilaian dari tabel pivot cpmk_mk
        // Karena jumlah_penilaian harus sama untuk semua CPMK dalam satu MK, kita ambil dari baris pertama
        $jumlahPenilaian = DB::table('cpmk_mk')
            ->where('mk_id', $mk_id)
            ->value('jumlah_penilaian') ?? 3;

        // Jika data di tabel cpmk_mk ada tetapi jumlah_penilaian adalah 1 (data lama), kita update ke 3
        if ($jumlahPenilaian == 1) {
            DB::table('cpmk_mk')
                ->where('mk_id', $mk_id)
                ->update(['jumlah_penilaian' => 3]);
            $jumlahPenilaian = 3;
        }

        return response()->json(['jumlah_penilaian' => $jumlahPenilaian]);
    } catch (\Exception $e) {
        Log::error('Error in getJumlahPenilaian for mk_id ' . $mk_id . ': ' . $e->getMessage());
        return response()->json(['error' => 'Gagal memuat jumlah penilaian: ' . $e->getMessage()], 500);
    }
}

    public function getCpmks($mk_id)
    {
        try {
            $user = Auth::user();
            $mk = Mk::with([
                'cpmks' => function ($query) {
                    $query->select('cpmk.id', 'cpmk.kode_cpmk', 'cpmk.deskripsi')->withPivot('bobot');
                }
            ])->findOrFail($mk_id);

            if ($user->role === 'dosen') {
                $dosen = $user->dosen;
                if (!$dosen) {
                    Log::warning('Dosen data not found for user: ' . $user->email);
                    return response()->json(['error' => 'Data dosen tidak ditemukan.'], 403);
                }

                // Validasi bahwa dosen mengajar mata kuliah ini
                $kelas = Kelas::where('nip', $dosen->nip)
                    ->where('kode_matakuliah', $mk->kode_mk)
                    ->exists();

                if (!$kelas) {
                    Log::warning('Dosen ' . $dosen->nip . ' tidak mengajar MK: ' . $mk->kode_mk);
                    return response()->json(['error' => 'Anda tidak berhak mengakses pembobotan untuk mata kuliah ini.'], 403);
                }
            }

            $cpmks = $mk->cpmks->map(function ($cpmk) use ($mk_id) {
                // Ambil teknik penilaian untuk CPMK ini
                $teknikPenilaian = DB::table('teknik_penilaian')
                    ->where('mk_id', $mk_id)
                    ->where('cpmk_id', $cpmk->id)
                    ->pluck('bobot', 'teknik')
                    ->toArray();

                Log::info('CPMK Data (getCpmks): ' . json_encode([
                    'id' => $cpmk->id,
                    'kode_cpmk' => $cpmk->kode_cpmk,
                    'deskripsi' => $cpmk->deskripsi ?? 'Deskripsi tidak tersedia',
                    'bobot' => $cpmk->pivot->bobot ?? 0,
                    'teknik_penilaian' => $teknikPenilaian,
                    'mk_id' => $mk_id,
                ]));

                return [
                    'id' => $cpmk->id,
                    'kode_cpmk' => $cpmk->kode_cpmk,
                    'deskripsi' => $cpmk->deskripsi ?? 'Deskripsi tidak tersedia',
                    'bobot' => $cpmk->pivot->bobot ?? 0,
                    'teknik_penilaian' => $teknikPenilaian,
                ];
            });

            Log::info('Returned CPMKs for MK ' . $mk_id . ': ' . json_encode($cpmks));
            return response()->json($cpmks);
        } catch (\Exception $e) {
            Log::error('Error in getCpmks for mk_id ' . $mk_id . ': ' . $e->getMessage() . ', Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Gagal memuat CPMK: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
{
    try {
        $request->validate([
            'mk_id' => 'required|exists:mk,id',
            'teknikData' => 'required|array',
            'teknikData.*.cpmk_id' => 'required|exists:cpmk,id',
            'teknikData.*.teknik' => 'required|string',
            'teknikData.*.bobot' => 'required|integer|min:0|max:100',
            'jumlah_penilaian' => 'required|integer|min:1|max:15', // Validasi jumlah penilaian
        ]);

        // Hitung total bobot per CPMK berdasarkan teknik penilaian
        $teknikDataGrouped = collect($request->teknikData)->groupBy('cpmk_id');
        $bobotCpmkMap = [];
        foreach ($teknikDataGrouped as $cpmk_id => $teknikItems) {
            $totalBobotTeknik = $teknikItems->sum('bobot');
            $bobotCpmkMap[$cpmk_id] = $totalBobotTeknik;
        }

        // Validasi total bobot semua CPMK
        $totalBobot = array_sum($bobotCpmkMap);
        if ($totalBobot != 100) {
            return response()->json(['error' => 'Total bobot semua CPMK harus 100%!'], 422);
        }

        $mk_id = $request->mk_id;
        $jumlahPenilaian = $request->jumlah_penilaian ?? 3; // Default ke 3 jika tidak ada input
        $user = Auth::user();
        $mk = Mk::findOrFail($mk_id);

        if ($user->role === 'dosen') {
            $dosen = $user->dosen;
            if (!$dosen) {
                Log::warning('Dosen data not found for user: ' . $user->email);
                return response()->json(['error' => 'Data dosen tidak ditemukan.'], 403);
            }

            // Validasi bahwa dosen mengajar mata kuliah ini
            $kelas = Kelas::where('nip', $dosen->nip)
                ->where('kode_matakuliah', $mk->kode_mk)
                ->exists();

            if (!$kelas) {
                Log::warning('Dosen ' . $dosen->nip . ' tidak mengajar MK: ' . $mk->kode_mk);
                return response()->json(['error' => 'Anda tidak berhak mengatur pembobotan untuk mata kuliah ini.'], 403);
            }
        }

        DB::beginTransaction();
        try {
            // Simpan bobot CPMK (dihitung dari total bobot teknik penilaian)
            foreach ($bobotCpmkMap as $cpmk_id => $bobot) {
                $existing = DB::table('cpmk_mk')
                    ->where('mk_id', $mk_id)
                    ->where('cpmk_id', $cpmk_id)
                    ->first();

                if ($existing) {
                    DB::table('cpmk_mk')
                        ->where('mk_id', $mk_id)
                        ->where('cpmk_id', $cpmk_id)
                        ->update([
                            'bobot' => $bobot,
                            'min_standard' => 50,
                            'jumlah_penilaian' => $jumlahPenilaian,
                            'updated_at' => now()
                        ]);
                    Log::info('Updated bobot for mk_id: ' . $mk_id . ', cpmk_id: ' . $cpmk_id . ', bobot: ' . $bobot . ', jumlah_penilaian: ' . $jumlahPenilaian);
                } else {
                    DB::table('cpmk_mk')->insert([
                        'mk_id' => $mk_id,
                        'cpmk_id' => $cpmk_id,
                        'bobot' => $bobot,
                        'min_standard' => 50,
                        'jumlah_penilaian' => $jumlahPenilaian,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    Log::info('Inserted bobot for mk_id: ' . $mk_id . ', cpmk_id: ' . $cpmk_id . ', bobot: ' . $bobot . ', jumlah_penilaian: ' . $jumlahPenilaian);
                }
            }

            // Simpan teknik penilaian
            // Hapus teknik penilaian lama untuk MK ini
            DB::table('teknik_penilaian')->where('mk_id', $mk_id)->delete();

            // Insert teknik penilaian baru
            foreach ($request->teknikData as $teknik) {
                $cpmk_id = $teknik['cpmk_id'];
                $teknikNama = $teknik['teknik'];
                $bobot = (int) $teknik['bobot'];

                if ($bobot > 0) { // Hanya simpan jika bobot lebih dari 0
                    DB::table('teknik_penilaian')->insert([
                        'mk_id' => $mk_id,
                        'cpmk_id' => $cpmk_id,
                        'teknik' => $teknikNama,
                        'bobot' => $bobot,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    Log::info('Inserted teknik penilaian for mk_id: ' . $mk_id . ', cpmk_id: ' . $cpmk_id . ', teknik: ' . $teknikNama . ', bobot: ' . $bobot);
                }
            }

            DB::commit();
            Log::info('Bobot CPMK-MK and Teknik Penilaian updated/inserted for mk_id: ' . $request->mk_id);
            return response()->json(['success' => 'Data pembobotan tersimpan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction error in update for mk_id ' . $mk_id . ': ' . $e->getMessage() . ', Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Gagal menyimpan pembobotan: ' . $e->getMessage()], 500);
        }
    } catch (\Exception $e) {
        Log::error('Error in update: ' . $e->getMessage() . ', Trace: ' . $e->getTraceAsString());
        return response()->json(['error' => 'Gagal menyimpan pembobotan: ' . $e->getMessage()], 500);
    }
}
}