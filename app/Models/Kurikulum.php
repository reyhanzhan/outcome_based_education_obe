<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kurikulum extends Model
{
    protected $table = 'kurikulum';
    protected $fillable = ['tahun', 'kode_prodi', 'kode_mk', 'semester', 'kurikulum_id',];


    public function TeknikPenilaian()
    {
        return $this->hasMany(TeknikPenilaian::class, 'kurikulum_id', 'id');
    }
    
    
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'kode_prodi', 'kode_prodi');
    }

    // Relasi ke Mata Kuliah (Mk)
    public function mk()
    {
        return $this->belongsTo(Mk::class, 'kode_mk', 'kode_mk');
    }

    // Relasi ke Krs (jika diperlukan)
    public function krs()
    {
        return $this->hasMany(Krs::class, 'tahun', 'tahun')
                    ->where('kode_prodi', $this->kode_prodi)
                    ->where('kode_mk', $this->kode_mk);
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'kurikulum_id', 'id');
    }
}