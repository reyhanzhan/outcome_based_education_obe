<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CpmkMk extends Model
{
    use HasFactory;

    protected $table = 'cpmk_mk'; // Sesuaikan dengan tabel pivot
    public $timestamps = false;

    protected $fillable = ['cpmk_id', 'mk_id', 'bobot', 'min_standard'];
}
