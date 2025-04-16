<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiTeknikPenilaian extends Model
{
    use HasFactory;

    protected $table = 'nilai_teknik_penilaian';

    protected $fillable = ['mahasiswa_id', 'mk_id', 'cpmk_id', 'teknik', 'nilai'];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function mk()
    {
        return $this->belongsTo(Mk::class, 'mk_id');
    }

    public function cpmk()
    {
        return $this->belongsTo(Cpmk::class, 'cpmk_id');
    }
}