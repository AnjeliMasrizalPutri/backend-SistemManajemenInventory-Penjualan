<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransaksiBarangKeluar extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_transaksi',
        'tanggal_keluar',
        'nama_pelanggan',
    ];


    //relasi

    public function details()
    {
        return $this->hasMany(BarangKeluarDetail::class, 'transaksi_barang_keluar_id');
    }
}
