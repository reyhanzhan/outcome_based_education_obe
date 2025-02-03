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

    public function cpls()
    {
        return $this->belongsToMany(Cpl::class, 'cpl_pl');
    }
}

