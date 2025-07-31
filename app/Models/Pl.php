<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pl extends Model
{
    use HasFactory;

    protected $table = 'pl'; // Sesuaikan dengan nama tabel yang benar di database

    protected $fillable = [
        'kode_pl',
        'deskripsi',
        'kategori',
        'kode_prodi',
        'kurikulum_id',
    ];

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'kode_prodi', 'kode_prodi');
    }

    public function cpls()
    {
        return $this->belongsToMany(Cpl::class, 'cpl_pl', 'pl_id', 'cpl_id');
    }

}

