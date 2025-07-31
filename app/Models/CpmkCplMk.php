<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CpmkCplMk extends Model
{
    use HasFactory;

    protected $fillable = ['cpl_id', 'cpmk_id', 'mk_id', 'kurikulum_id', 'created_at', 'updated_at'];

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }
}
