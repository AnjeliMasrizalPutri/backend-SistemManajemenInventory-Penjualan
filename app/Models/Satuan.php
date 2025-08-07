<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Satuan extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'nama_satuan'
    ];

    public function barangs()
    {
        return $this->hasMany(Barang::class);
    }
}
