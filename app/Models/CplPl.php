<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CplPl extends Model
{
    use HasFactory;

    protected $table = 'cpl_pl'; // Nama tabel pivot di database

    protected $fillable = [
        'pl_id',
        'cpl_id',
        'created_at',
        'updated_at',
    ];

    public function cpl()
    {
        return $this->belongsTo(Cpl::class, 'cpl_id');
    }
    
    public function pl()
    {
        return $this->belongsTo(Pl::class, 'pl_id');
    }
}
