<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $fillable = [
        'kode_prodi',
        'kurikulum_id',
        'tahun',
        'kode_mk',
        'periode',
        'nip_dosen',
    ];

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id', 'id');
    }
    
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'nip_dosen', 'nip');
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'nip_dosen','nip');
    }

    // Relasi ke Program Studi
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'kode_prodi', 'kode_prodi');
    }

    // Relasi ke Mata Kuliah (Mk)
    public function mk()
    {
        return $this->belongsTo(Mk::class, 'kode_mk', 'kode_mk');
    }

    // Relasi ke KRS (jika ada)
    public function krs()
    {
        return $this->hasMany(Krs::class, 'kode_mk', 'kode_mk')
                    ->whereColumn('krs.periode', 'kelas.periode');
    }
}