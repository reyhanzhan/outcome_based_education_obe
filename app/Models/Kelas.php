<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';
    protected $fillable = ['kode_matakuliah', 'nip', 'kelas', 'periode'];

    public function mk()
    {
        return $this->belongsTo(Mk::class, 'kode_matakuliah', 'kode_mk');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'nip', 'nip');
    }

    public function mataKuliah()
    {
        return $this->belongsTo(Mk::class, 'kode_matakuliah', 'kode_mk');
    }
}