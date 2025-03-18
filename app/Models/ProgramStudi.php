<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramStudi extends Model
{
    protected $table = 'program_studi';
    protected $primaryKey = 'kode_prodi';
    public $incrementing = false; // Karena kode_prodi bukan auto-increment
    protected $keyType = 'string'; // Tipe data primary key adalah string
    protected $fillable = ['kode_prodi', 'nama_prodi'];

    // Relasi ke Kelas
    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'kode_prodi', 'kode_prodi');
    }

    // Relasi ke Krs
    public function krs()
    {
        return $this->hasMany(Krs::class, 'kode_prodi', 'kode_prodi');
    }
}