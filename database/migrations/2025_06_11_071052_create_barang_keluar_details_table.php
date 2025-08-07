<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barang_keluar_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_barang_keluar_id')
                ->constrained('transaksi_barang_keluars')
                ->onDelete('cascade');

            $table->foreignId('barang_id')
                ->constrained('barangs')
                ->onDelete('cascade');

            $table->integer('jumlah_keluar');
            $table->decimal('harga_jual_saat_transaksi', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_keluar_details');
    }
};
