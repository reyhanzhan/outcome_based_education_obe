<?php

namespace App\Models;

use App\Models\Cpmk;
use App\Models\CpmkCpl;
use App\Models\Mk;
use App\Models\Kurikulum;
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
        'kode_prodi',
        'kurikulum_id',
    ];


    public function cpmks()
    {
        return $this->belongsToMany(Cpmk::class, 'cpmk_cpl', 'cpl_id', 'cpmk_id')
            ->withPivot('kurikulum_id', 'bobot')
            ->withTimestamps();
    }


    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'kode_prodi', 'kode_prodi');
    }

    public function pls()
    {
        return $this->belongsToMany(Pl::class, 'cpl_pl', 'cpl_id', 'pl_id');
    }




    public function bks()
    {
        return $this->belongsToMany(Bk::class, 'cpl_bk')->withTimestamps();
    }

    public function mks()
    {
        return $this->belongsToMany(Mk::class, 'cpl_mk')->withPivot('bobot')->withTimestamps();
    }

    public function cpmkshasmany()
    {
        return $this->hasMany(Cpmk::class, 'cpl_id');
    }

    public function mkshasmany()
    {
        return $this->hasMany(Mk::class, 'cpl_id');
    }

    // Relasi langsung CPL-MK jika ada
    public function directMks()
    {
        return $this->belongsToMany(Mk::class, 'cpl_mk', 'cpl_id', 'mk_id');
    }

    public function mahasiswas()
    {
        return $this->belongsToMany(Mahasiswa::class, 'nilai_cpl')->withPivot('nilai')->withTimestamps();
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
