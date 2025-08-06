<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObeEvaluation extends Model
{
    use HasFactory;

    protected $table = 'obe_evaluations';

    protected $fillable = [
        'mahasiswa_id',
        'mk_id',
        'nilai_cpmk',
        'total_score',
        'periode',
        'tahun',
    ];


    protected $casts = [
        'nilai_cpmk' => 'array',
        'total_score' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo('App\Models\Mahasiswa', 'mahasiswa_id');
    }

    public function mk()
    {
        return $this->belongsTo('App\Models\MataKuliah', 'mk_id');
    }
}
