<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangKeluarDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaksi_barang_keluar_id',
        'barang_id',
        'jumlah_keluar',
        'harga_jual_saat_transaksi',
    ];

     // relasi

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function transaksibarangKeluar()
    {
        return $this->belongsTo(TransaksiBarangKeluar::class, 'transaksi_barang_keluar_id');
    }
}
