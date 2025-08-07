<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jenis extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'nama_jenis'
    ];

    public function barangs()
    {
        return $this->hasMany(Barang::class);
    }
}
