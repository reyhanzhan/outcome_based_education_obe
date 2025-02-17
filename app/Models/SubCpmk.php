<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCpmk extends Model
{
    use HasFactory;

    protected $table = 'subcpmk';

    protected $fillable = [
        'cpmk_id',
        'kode_subcpmk',
        'uraian',
    ];
    

    public function cpmk()
    {
        return $this->belongsTo(Cpmk::class, 'cpmk_id');
    }
}
