<?php

namespace App\Models;

use App\Models\Jenis;
use App\Models\Satuan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Barang extends Model
{
    use HasFactory;
    protected $table = 'barangs';
    protected $fillable =
    [
        'nama_barang',
        'kode_barang',
        'jenis_id',
        'satuan_id',
        'harga_beli',
        'harga_jual',
        'stok',
        'stok_minimum'
    ];

    protected $hidden = ['jenis_id', 'satuan_id'];

    public function jenis()
    {
        return $this->belongsTo(Jenis::class, 'jenis_id');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    public function barangMasukDetails()
    {
        return $this->hasMany(BarangMasukDetail::class, 'barang_id');
    }

    public function barangKeluarDetails()
    {
        return $this->hasMany(BarangKeluarDetail::class, 'barang_id');
    }
}
