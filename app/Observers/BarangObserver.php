<?php

namespace App\Observers;

use App\Models\Barang;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class BarangObserver
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Handle the Barang "updated" event.
     * Dipanggil setelah data produk diperbarui.
     *
     * @param  \App\Models\Barang  $barang
     * @return void
     */
    public function updated(Barang $barang): void
    {
        if (!$barang->isDirty('stok') && !$barang->isDirty('stok_minimum')) {
            return;
        }

        if ($barang->stok <= $barang->stok_minimum) {
            if ($barang->getOriginal('stok') > $barang->stok_minimum || $barang->stok < $barang->getOriginal('stok')) {
                $satuan = $barang->satuan ? $barang->satuan->nama_satuan : 'satuan tidak tersedia'; // Mengambil nama satuan

                $message = "🚨 Peringatan Stok Rendah! 🚨\n\n";
                $message .= "*Produk:* " . $barang->nama_barang . "\n";
                $message .= "*Stok Tersisa:* " . $barang->stok . " " . $satuan . "\n"; // Menambahkan satuan
                $message .= "*Batas Minimum:* " . $barang->stok_minimum . " " . $satuan . "\n"; // Menambahkan satuan
                $message .= "Segera lakukan pemesanan ulang dengan supplier terkait!";

                $this->whatsAppService->sendNotification($message);
            } else {
                Log::info('Stok produk sudah di bawah batas minimum, tetapi tidak ada perubahan stok yang memicu notifikasi baru.', [
                    'barang_id' => $barang->id,
                    'stok' => $barang->stok,
                    'stok_minimum' => $barang->stok_minimum
                ]);
            }
        }
    }
}
