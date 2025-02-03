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


    public function pls()
    {
        return $this->belongsToMany(Pl::class, 'cpl_pl');
    }



    public function bks()
    {
        return $this->belongsToMany(Bk::class, 'cpl_bk');
    }


    public function cpmks()
    {
        return $this->belongsToMany(Cpmk::class, 'cpmk_cpl', 'cpl_id', 'cpmk_id');
    }

    public function mks()
    {
        return $this->belongsToMany(Mk::class, 'cpl_mk', 'cpl_id', 'mk_id');
    }

    public function mksThroughCpmk()
    {
        return $this->hasManyThrough(Mk::class, Cpmk::class, 'cpl_id', 'id', 'id', 'id');
    }


}
