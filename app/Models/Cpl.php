<?php

namespace App\Models;

use App\Models\Cpmk;
use App\Models\CpmkCpl;
use App\Models\Mk;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cpl extends Model
{
    use HasFactory;

    protected $table = 'cpl';

    protected $fillable = [
        'kode_cpl',
        'deskripsi',
        'kategori',
    ];

    public function pls()
    {
        return $this->belongsToMany(Pl::class, 'cpl_pl', 'cpl_id', 'pl_id');
    }


    // Relasi ke CPMK
    public function cpmks()
    {
        return $this->belongsToMany(Cpmk::class, 'cpmk_cpl', 'cpl_id', 'cpmk_id');
    }

    // Relasi ke MK (Melalui CPMK)
    public function mks()
    {
        return $this->belongsToMany(Mk::class, 'cpl_mk', 'cpl_id', 'mk_id');
    }

    public function bks()
    {
        return $this->belongsToMany(Mk::class, 'cpl_bk', 'cpl_id', 'bk_id');
    }

    // Relasi langsung CPL-MK jika ada
    public function directMks()
    {
        return $this->belongsToMany(Mk::class, 'cpl_mk', 'cpl_id', 'mk_id');
    }
    

    // Relasi ke MK melalui CPMK
    public function mksThroughCpmk()
    {
        return $this->hasManyThrough(
            Mk::class,
            CpmkMk::class, // Model Pivot CPMK-MK
            'cpmk_id', // Foreign Key di tabel `cpmk_mk`
            'id', // Primary Key di tabel `mk`
            'id', // Primary Key di `cpl`
            'mk_id' // Foreign Key yang menghubungkan ke `mk`
        );
    }
}
