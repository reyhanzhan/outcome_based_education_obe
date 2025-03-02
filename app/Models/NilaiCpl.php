<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiCpl extends Model
{
    use HasFactory;

    protected $table = 'nilai_cpl'; // Tentukan nama tabel secara eksplisite

    protected $fillable = ['mahasiswa_id', 'cpl_id', 'nilai'];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function cpl()
    {
        return $this->belongsTo(Cpl::class);
    }
}