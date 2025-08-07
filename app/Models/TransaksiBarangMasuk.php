<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransaksiBarangMasuk extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_transaksi',
        'tanggal_masuk',
        'supplier_id',
    ];


    //relasi

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details()
    {
        return $this->hasMany(BarangMasukDetail::class, 'transaksi_barang_masuk_id');
    }
}
