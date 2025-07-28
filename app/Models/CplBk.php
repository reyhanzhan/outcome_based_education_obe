<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CplBk extends Model
{
    use HasFactory;

    protected $table = 'cpl_bk'; // Nama tabel pivot di database

    protected $fillable = [
        'cpl_id',
        'bk_id',
        'created_at',
        'updated_at',
    ];

    public function cpl()
    {
        return $this->belongsTo(Cpl::class, 'cpl_id');
    }
    
    public function bk()
    {
        return $this->belongsTo(Bk::class, 'bk_id');
    }
}
