<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BarangMasukDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaksi_barang_masuk_id',
        'barang_id',
        'jumlah_masuk',
        'harga_beli_saat_transaksi',
    ];


    // relasi

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function transaksibarangMasuk()
    {
        return $this->belongsTo(TransaksiBarangMasuk::class, 'transaksi_barang_masuk_id');
    }
}
