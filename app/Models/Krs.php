<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Krs extends Model
{
    protected $table = 'krs';
    protected $fillable = ['periode', 'kode_prodi', 'kode_mk', 'tahun', 'nama_kelas', 'nim','kurikulum_id'];

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id', 'id');
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

    // Relasi ke Mahasiswa
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    // Relasi ke Kelas
    public function kelas()
    {
        return $this->hasOne(Kelas::class, 'kode_mk', 'kode_mk')
                    ->where('periode', $this->periode);
    }
    
}