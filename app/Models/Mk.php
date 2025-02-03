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
        'sks',
        'wptwp',
    ];

    public function cpl()
    {
        return $this->belongsToMany(Cpl::class, 'cpl_mk');
    }

    public function cpmks()
    {
        return $this->belongsToMany(Cpmk::class, 'cpmk_mk', 'mk_id', 'cpmk_id');
    }

    public function mksThroughCpmk()
    {
        return $this->hasManyThrough(Mk::class, Cpmk::class, 'cpl_id', 'id', 'id', 'id');
    }

    public function cpls()
    {
        return $this->belongsToMany(Cpl::class, 'cpl_mk', 'mk_id', 'cpl_id');
    }
}
