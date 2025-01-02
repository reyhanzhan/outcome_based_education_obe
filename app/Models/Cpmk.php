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
    ];

    public function pl()
    {
        return $this->belongsToMany(Pl::class, 'pemetaan_cpmkpl', 'cpmk_id', 'pl_id')->withPivot('checked')->withTimestamps();
    }


    public function cpl()
    {
        return $this->belongsToMany(Cpl::class, 'pemetaan_cpmkpl')->withPivot('checked')->withTimestamps();
    }

    // Relasi dengan MK
    public function mk()
    {
        return $this->belongsToMany(Mk::class, 'pemetaan_cpmkmk', 'cpmk_id', 'mk_id')->withTimestamps();
    }

    public function cpl2()
    {
        return $this->belongsToMany(Cpl::class, 'pemetaan_cpmk_cpl_mk')
                    ->withPivot('mk_id', 'checked')
                    ->withTimestamps();
    }
}
