<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cpl extends Model
{
    use HasFactory;

    protected $table = 'cpl'; // Sesuaikan dengan nama tabel yang benar di database

    protected $fillable = [
        'kode_cpl',
        'deskripsi',
        'kategori',
    ];

    public function pl()
    {
        return $this->belongsToMany(Pl::class, 'pemetaan')->withPivot('checked')->withTimestamps();
    }

    public function pl2()
    {
        return $this->belongsToMany(Pl::class, 'pemetaan_cpmkpl')->withPivot('checked')->withTimestamps();
    }
}
