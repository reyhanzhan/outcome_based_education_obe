<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa'; // Sesuaikan dengan nama tabel yang benar di database

    protected $fillable = ['nama', 'nim'];

    public function nilaiCpmks()
    {
        return $this->hasMany(NilaiCpmk::class, 'mahasiswa_id');
    }

    public function cpls()
    {
        return $this->belongsToMany(Cpl::class, 'nilai_cpl')->withPivot('nilai')->withTimestamps();
    }

    public function mks()
    {
        return $this->belongsToMany(Mk::class, 'nilai_cpmk', 'mahasiswa_id', 'mk_id')
                    ->withPivot('cpmk_id', 'nilai')
                    ->withTimestamps();
    }
}