<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pl extends Model
{
    use HasFactory;

    protected $table = 'pl'; // Sesuaikan dengan nama tabel yang benar di database

    protected $fillable = [
        'kode_pl',
        'deskripsi',
        'kategori',
    ];

    public function cpl()
    {
        return $this->belongsToMany(Cpl::class, 'pemetaan')->withPivot('checked')->withTimestamps();
    }

    public function cpl2()
    {
        return $this->belongsToMany(Cpl::class, 'pemetaan_cpmkpl')->withPivot('checked')->withTimestamps();
    }
}

