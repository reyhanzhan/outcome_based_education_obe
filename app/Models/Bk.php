<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bk extends Model
{
    use HasFactory;

    protected $table = 'bk'; // Sesuaikan dengan nama tabel yang benar di database

    protected $fillable = [
        'kode_bk',
        'deskripsi',
    ];

    public function cpls()
    {
        return $this->belongsToMany(Cpl::class, 'cpl_bk');
    }
}
