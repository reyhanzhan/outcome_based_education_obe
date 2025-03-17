<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Krs extends Model
{
    use HasFactory;

    protected $table = 'krs';
    protected $fillable = ['nim', 'kode_matakuliah', 'kelas', 'periode'];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    public function mk()
    {
        return $this->belongsTo(Mk::class, 'kode_matakuliah', 'kode_mk');
    }
}