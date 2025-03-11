<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa'; // Sesuaikan dengan nama tabel yang benar di database

    protected $fillable = ['nama', 'nim'];

    public function nilaiCpmks()
    {
        return $this->hasMany(NilaiCpmk::class, 'mahasiswa_id');
    }

    public function cpls()
    {
        return $this->belongsToMany(Cpl::class, 'nilai_cpl')->withPivot('nilai')->withTimestamps();
    }

    public function mks()
    {
        return $this->belongsToMany(Mk::class, 'nilai_cpmk', 'mahasiswa_id', 'mk_id')
            ->withPivot('cpmk_id', 'nilai')
            ->withTimestamps();
    }


    public function calculateCpmkScore($cpmk_id, $mk_id)
    {
        $nilai = $this->nilaiCpmks()->where('cpmk_id', $cpmk_id)->first();

        if (!$nilai) {
            return 0; // Jika nilai tidak ditemukan, kembalikan 0
        }

        $cpmk = Cpmk::find($cpmk_id);

        if (!$cpmk) {
            return 0; // Jika CPMK tidak ditemukan, kembalikan 0
        }

        $mkPivot = $cpmk->mks()->where('mk_id', $mk_id)->first();

        if (!$mkPivot || !isset($mkPivot->pivot->bobot)) {
            return 0; // Jika relasi CPMK-MK tidak ada atau bobot tidak tersedia, kembalikan 0
        }

        $bobot = $mkPivot->pivot->bobot;

        return round(($nilai->nilai * $bobot) / 100, 2);
    }


    public function calculateCplScore($cpl_id, $mk_id)
    {
        $cpl = Cpl::find($cpl_id);
        if (!$cpl)
            return 0; // Jika CPL tidak ditemukan, return 0

        $totalScore = 0;
        $totalMaxWeight = 0;

        foreach ($cpl->cpmks as $cpmk) {
            $nilaiCpmk = $this->nilaiCpmks()->where('cpmk_id', $cpmk->id)->where('mk_id', $mk_id)->first();

            if ($nilaiCpmk) {
                $bobot = $cpmk->mks()->where('mk_id', $mk_id)->first()->pivot->bobot ?? 0;
                $totalScore += ($nilaiCpmk->nilai * $bobot) / 100;
                $totalMaxWeight += $bobot;
            }
        }

        return $totalMaxWeight > 0 ? round(($totalScore / $totalMaxWeight) * 100, 2) : 0;
    }




    public function calculateCombinedCplScore($cpl_id, array $mk_ids)
    {
        if (!is_array($mk_ids)) {
            $mk_ids = [$mk_ids]; // Jika hanya satu ID diberikan, ubah menjadi array
        }

        $totalScore = 0;
        $totalMaxWeight = 0;

        foreach ($mk_ids as $mk_id) {
            $cplScore = $this->calculateCplScore($cpl_id, $mk_id);
            $mk = Mk::find($mk_id);
            $weight = $mk ? ($mk->bobot ?? 100) / 100 : 1; // Bobot MK, default 1 (100%) jika tidak ada
            $totalScore += $cplScore * $weight;
            $totalMaxWeight += $weight;
        }

        return $totalMaxWeight > 0 ? round($totalScore / $totalMaxWeight, 2) : 0;
    }



    public function calculateCplAchievement($cpl_id, $mk_id)
    {
        $cplScore = $this->calculateCplScore($cpl_id, $mk_id);
        return $cplScore >= 80 ? 100 : round(($cplScore / 80) * 100, 2); // Standar pencapaian 80%
    }




}