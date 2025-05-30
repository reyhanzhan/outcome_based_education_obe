<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CpmkCpl extends Model
{
    use HasFactory;

    protected $table = 'cpmk_cpl'; // Nama tabel pivot di database

    protected $fillable = [
        'cpmk_id',
        'cpl_id',
        'kode_prodi',
    ];

    public $timestamps = false; // Jika tabel pivot tidak memiliki timestamps

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'kode_prodi', 'kode_prodi');
    }

    // Relasi ke CPMK
    public function cpmk()
    {
        return $this->belongsTo(Cpmk::class, 'cpmk_id');
    }

    // Relasi ke CPL
    public function cpl()
    {
        return $this->belongsTo(Cpl::class, 'cpl_id');
    }
}
