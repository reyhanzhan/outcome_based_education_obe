<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cpmk extends Model
{
    use HasFactory;

    protected $table = 'cpmk'; // Sesuaikan dengan nama tabel yang benar di database

    protected $fillable = [
        'kode_cpmk',
        'deskripsi',
        'min_standard',
        'kode_prodi',
        'cpl_id',
        'kurikulum_id',
    ];

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id', 'id');
    }

    public function mks()
    {
        return $this->belongsToMany(Mk::class, 'cpmk_mk', 'cpmk_id', 'mk_id')
            ->withPivot('kurikulum_id', 'bobot', 'min_standard')
            ->withTimestamps();
    }
    public function cplCpmks()
    {
        return $this->belongsToMany(Cpl::class, 'cpmk_cpl', 'cpmk_id', 'cpl_id')
            ->withPivot('kurikulum_id', 'bobot')
            ->withTimestamps();
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'kode_prodi', 'kode_prodi');
    }

    public function cpls()
    {
        return $this->belongsToMany(Cpl::class, 'cpmk_cpl', 'cpmk_id', 'cpl_id')->withPivot('bobot')->withTimestamps();
    }

    public function cplsbelongsto()
    {
        return $this->belongsTo(Cpl::class, 'cpl_id');
    }



    public function nilaiCpmks()
    {
        return $this->hasMany(NilaiCpmk::class, 'cpmk_id');
    }


    public function teknikPenilaian()
    {
        return $this->hasMany(TeknikPenilaian::class, 'cpmk_id');
    }

    public function nilaiTeknikPenilaian()
    {
        return $this->hasMany(NilaiTeknikPenilaian::class, 'cpmk_id');
    }

}
