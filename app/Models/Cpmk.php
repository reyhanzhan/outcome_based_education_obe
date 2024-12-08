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
}
