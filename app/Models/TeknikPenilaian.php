<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeknikPenilaian extends Model
{
    use HasFactory;

    // Tentukan nama tabel secara eksplisit
    protected $table = 'teknik_penilaian';

    protected $fillable = ['mk_id', 'cpmk_id', 'teknik', 'bobot','kurikulum_id'];

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }
    
    public function mataKuliah()
    {
        return $this->belongsTo(Mk::class, 'mk_id');
    }

    public function cpmk()
    {
        return $this->belongsTo(Cpmk::class, 'cpmk_id');
    }
}