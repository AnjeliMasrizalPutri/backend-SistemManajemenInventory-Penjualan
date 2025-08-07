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
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jenis_id');
            $table->unsignedBigInteger('satuan_id');
            $table->string('nama_barang');
            $table->string('kode_barang');
            $table->bigInteger('stok')->nullable()->default(0);
            $table->bigInteger('stok_minimum');
            $table->bigInteger('harga_beli');
            $table->bigInteger('harga_jual');
            $table->timestamps();

            $table->foreign('jenis_id')
                ->references('id')
                ->on('jenis')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->foreign('satuan_id')
                ->references('id')
                ->on('satuans')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
