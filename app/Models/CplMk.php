<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CplMk extends Model
{
    use HasFactory;

    protected $table = 'cpl_mk'; // Nama tabel pivot di database

    protected $fillable = [
        'cpl_id',
        'mk_id',
        'created_at',
        'updated_at',
        'bobot',
        'kurikulum_id',
    ];

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    public function cpl()
    {
        return $this->belongsTo(Cpl::class, 'cpl_id');
    }

    public function mk()
    {
        return $this->belongsTo(Mk::class, 'mk_id');
    }
}
