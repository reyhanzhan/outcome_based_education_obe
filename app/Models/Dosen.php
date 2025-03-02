<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Dosen extends Authenticatable
{
    use HasFactory;

    protected $table = 'dosen'; // Sesuaikan dengan nama tabel yang benar di database

    protected $fillable = ['nama', 'email', 'role', 'password'];
    protected $hidden = ['password', 'remember_token'];

    public function mks()
    {
        return $this->belongsToMany(Mk::class, 'mk_dosen')->withTimestamps();
    }
}