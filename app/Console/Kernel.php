<?php

namespace App\Console;

use App\Models\Barang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // $schedule->command('activitylog:clean --days=7')->daily();

        // Ambil dari .env di luar closure
        $schedule->call(function () {
            Log::info('⚠️ Menjalankan notifikasi stok minimum...');

            $barangMinimum = Barang::whereColumn('stok', '<=', 'stok_minimum')
                ->where('stok_minimum', '>', 0)
                ->get();

            Log::debug('Jumlah barang minimum: ' . $barangMinimum->count());

            if ($barangMinimum->count() > 0) {
                $text = "*📦 Reminder Stok Minimum*\n\n";
                foreach ($barangMinimum as $item) {
                    $text .= "- {$item->nama_barang} (Stok: {$item->stok})\n";
                }

                $text .= "\nSegera lakukan pembelian ulang untuk mencegah kehabisan stok.";

                $token = env('FONNTE_API_KEY');
                $target = env('ADMIN_WHATSAPP_NUMBER');

                Log::debug('Token:', [$token]);
                Log::debug('Target:', [$target]);
                Log::debug('Message:', [$text]);

                $response = Http::withHeaders([
                    'Authorization' => $token,
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $text,
                    'countryCode' => '62',
                    'delay' => 1,
                ]);

                Log::info('📤 Notifikasi WhatsApp berhasil dikirim.', ['response' => $response->json()]);
            } else {
                Log::info('✅ Tidak ada barang dengan stok minimum.');
            }
        })->dailyAt('07:00');
        //  ->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
