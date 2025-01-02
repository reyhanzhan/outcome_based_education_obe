<?php

namespace App\Models;

use App\Models\Cpmk;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mk extends Model
{
    use HasFactory;

    protected $table = 'mk'; // Sesuaikan dengan nama tabel yang benar di database

    protected $fillable = [
        'kode_mk',
        'deskripsi',
    ];

    // Relasi dengan CPMK
    public function cpmk()
    {
        return $this->belongsToMany(Cpmk::class, 'pemetaan_cpmkmk', 'mk_id', 'cpmk_id')->withTimestamps();
    }

    public function pemetaan()
    {
        return $this->hasMany(Pemetaan::class);
    }
}
