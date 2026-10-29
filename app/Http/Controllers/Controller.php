<?php

namespace App\Http\Controllers;

use App\Models\Kurikulum;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Tentukan kurikulum_id yang dipakai untuk membaca bobot CPMK/teknik penilaian sebuah MK.
     *
     * kurikulum_id pada KRS bisa berbeda dengan kurikulum_id tempat bobot disimpan
     * (pembobotan memakai kurikulum prodi+tahun, KRS bisa null/basi setelah kurikulum diubah),
     * sehingga bobot terbaca 0. Pilih kandidat yang benar-benar punya data bobot untuk MK ini.
     */
    protected function resolveKurikulumId($krs, $mkId, $kodeProdi, $tahun)
    {
        $candidates = collect([$krs->kurikulum_id])
            ->merge(Kurikulum::where('kode_prodi', $kodeProdi)->where('tahun', $tahun)->orderBy('id')->pluck('id'))
            ->filter()
            ->unique()
            ->values();

        foreach (['teknik_penilaian', 'cpmk_mk'] as $table) {
            $found = $candidates->first(function ($id) use ($table, $mkId) {
                return DB::table($table)->where('mk_id', $mkId)->where('kurikulum_id', $id)->exists();
            });
            if ($found) {
                return $found;
            }
        }

        return $krs->kurikulum_id ?? $candidates->first();
    }
}
