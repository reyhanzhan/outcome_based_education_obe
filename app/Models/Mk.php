<?php

namespace App\Models;

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
}
