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

    public function cpls()
    {
        return $this->belongsToMany(Cpl::class, 'cpmk_cpl', 'cpmk_id', 'cpl_id');
    }

    public function mks()
    {
        return $this->belongsToMany(Mk::class, 'cpmk_mk', 'cpmk_id', 'mk_id');
    }

}
