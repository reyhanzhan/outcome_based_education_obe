<?php

namespace App\Models;

use App\Models\Cpl;
use App\Models\Cpmk;
use App\Models\CpmkCpl;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\NilaiCpmk;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Mk extends Model
{
    use HasFactory;

    protected $table = 'mk'; // Sesuaikan dengan nama tabel yang benar di database

    protected $fillable = [
        'kode_mk',
        'deskripsi',
        'sks',
        'wptwp',
    ];

    public function cpl()
    {
        return $this->belongsToMany(Cpl::class, 'cpl_mk');
    }



    public function cplsbelongsto()
    {
        return $this->belongsTo(Cpl::class, 'cpl_id');
    }

    public function dosens()
    {
        return $this->belongsToMany(Dosen::class, 'mk_dosen')->withTimestamps();
    }


    public function cpmks()
    {
        return $this->belongsToMany(Cpmk::class, 'cpmk_mk')->withPivot('bobot', 'min_standard')->withTimestamps();
    }


    public function mksThroughCpmk()
    {
        return $this->hasManyThrough(Mk::class, Cpmk::class, 'cpl_id', 'id', 'id', 'id');
    }

    public function cpls()
    {
        return $this->belongsToMany(Cpl::class, 'cpl_mk', 'mk_id', 'cpl_id')->withPivot('bobot')->withTimestamps();
    }

    // Relasi ke CPL melalui CPMK (Many to Many)
    public function cplsThroughCpmk()
    {
        return $this->hasManyThrough(Cpl::class, CpmkCpl::class, 'cpmk_id', 'id', 'id', 'cpl_id')
            ->join('cpmk_mk', 'cpmk.id', '=', 'cpmk_mk.cpmk_id')
            ->select('cpl.*');
    }

    public function nilaiCpmks()
    {
        return $this->hasMany(NilaiCpmk::class, 'mk_id');
    }

    public function mahasiswas()
    {
        return $this->belongsToMany(Mahasiswa::class, 'nilai_cpmk', 'mk_id', 'mahasiswa_id')
            ->withPivot('cpmk_id', 'nilai')
            ->withTimestamps();
    }

    // fungsi untuk menghitung nilai MK dari seorang mahasiswa
    public function calculateMkScore($mahasiswa_id)
    {
        $nilaiCpmks = $this->nilaiCpmks()->where('mahasiswa_id', $mahasiswa_id)->with('cpmk')->get();

        $totalScore = 0;
        $maxScore = 100; // Total bobot CPMK harus 100%

        foreach ($nilaiCpmks as $nilaiCpmk) {
            $bobot = $nilaiCpmk->cpmk->mks()->where('mk_id', $this->id)->first()->pivot->bobot ?? 0;
            if ($bobot <= 0) {
                Log::warning('Bobot CPMK belum disetel untuk CPMK: ' . $nilaiCpmk->cpmk_id . ', MK: ' . $this->id);
                continue; // Lewati jika bobot tidak ada
            }
            $totalScore += ($bobot * $nilaiCpmk->nilai) / 100;
        }

        return $totalScore;
    }
}
