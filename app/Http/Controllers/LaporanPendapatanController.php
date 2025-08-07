<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangKeluarDetail;
use App\Models\BarangMasukDetail;
use App\Models\TransaksiBarangKeluar;
use App\Models\TransaksiBarangMasuk;
use App\Models\Barang;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Pastikan DB Facade di-import

class LaporanPendapatanController extends Controller
{
    public function getPendapatan(Request $request)
    {
        // 1. Ambil Input Tanggal atau Gunakan Default (Bulan Berjalan)
        $startDateInput = $request->input('tanggal_awal');
        $endDateInput = $request->input('tanggal_akhir');

        Log::info("Request received for Laporan Pendapatan. Start Date: {$startDateInput}, End Date: {$endDateInput}");

        try {
            // Jika tanggal awal tidak disediakan, gunakan awal bulan ini sebagai default
            if (empty($startDateInput)) {
                $startDate = Carbon::now()->startOfMonth()->startOfDay();
                $startDateInput = $startDate->toDateString(); // Untuk logging
            } else {
                $startDate = Carbon::parse($startDateInput)->startOfDay();
            }

            // Jika tanggal akhir tidak disediakan, gunakan akhir bulan ini sebagai default
            if (empty($endDateInput)) {
                $endDate = Carbon::now()->endOfMonth()->endOfDay();
                $endDateInput = $endDate->toDateString(); // Untuk logging
            } else {
                $endDate = Carbon::parse($endDateInput)->endOfDay();
            }

            // Validasi tambahan: Pastikan tanggal awal tidak melebihi tanggal akhir
            if ($startDate->greaterThan($endDate)) {
                Log::warning('Validation failed: Tanggal awal tidak boleh melebihi tanggal akhir.');
                return response()->json(['message' => 'Tanggal awal tidak boleh melebihi tanggal akhir.'], 400);
            }

            Log::info("Parsed Dates: Start: {$startDate}, End: {$endDate}");
        } catch (\Exception $e) {
            Log::error('Date parsing failed: ' . $e->getMessage(), ['input_start' => $startDateInput, 'input_end' => $endDateInput]);
            return response()->json(['message' => 'Format tanggal tidak valid.'], 400);
        }

        // Inisialisasi array untuk laporan per tanggal.
        // Array ini akan diisi HANYA dengan tanggal yang memiliki data transaksi.
        $laporanPerTanggal = [];

        // 2. Agregasi Penjualan (Barang Keluar) per Tanggal
        // Menggunakan Query Builder untuk agregasi langsung di database
        $penjualanHarian = DB::table('transaksi_barang_keluars as tbk')
            ->join('barang_keluar_details as bkd', 'tbk.id', '=', 'bkd.transaksi_barang_keluar_id')
            ->select(
                DB::raw('DATE(tbk.tanggal_keluar) as tanggal'),
                // Hitung total penjualan bersih (jumlah * harga jual)
                // Kolom diskon_per_item tidak ditemukan sebelumnya, jadi dihilangkan dari perhitungan ini.
                DB::raw('SUM(bkd.jumlah_keluar * bkd.harga_jual_saat_transaksi) as total_penjualan_harian')
            )
            ->whereBetween('tbk.tanggal_keluar', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(tbk.tanggal_keluar)'))
            ->orderBy('tanggal', 'asc')
            ->get();

        Log::info('Penjualan Harian Fetched: ' . $penjualanHarian->count() . ' items.');

        // Masukkan data penjualan ke dalam array laporan per tanggal
        foreach ($penjualanHarian as $data) {
            // Inisialisasi entri jika tanggal belum ada, lalu tambahkan penjualan
            if (!isset($laporanPerTanggal[$data->tanggal])) {
                $laporanPerTanggal[$data->tanggal] = [
                    'tanggal'          => $data->tanggal,
                    'total_penjualan'  => 0,
                    'total_pembelian'  => 0,
                ];
            }
            $laporanPerTanggal[$data->tanggal]['total_penjualan'] = (float) $data->total_penjualan_harian;
        }

        // 3. Agregasi Pembelian (Barang Masuk) per Tanggal
        $pembelianHarian = DB::table('transaksi_barang_masuks as tbm')
            ->join('barang_masuk_details as bmd', 'tbm.id', '=', 'bmd.transaksi_barang_masuk_id')
            ->join('barangs as b', 'bmd.barang_id', '=', 'b.id') // Join ke tabel barangs untuk mendapatkan harga_beli
            ->select(
                DB::raw('DATE(tbm.tanggal_masuk) as tanggal'),
                // Hitung total biaya pembelian (jumlah_masuk * harga_beli dari tabel barangs)
                DB::raw('SUM(bmd.jumlah_masuk * b.harga_beli) as total_pembelian_harian')
            )
            ->whereBetween('tbm.tanggal_masuk', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(tbm.tanggal_masuk)'))
            ->orderBy('tanggal', 'asc')
            ->get();

        Log::info('Pembelian Harian Fetched: ' . $pembelianHarian->count() . ' items.');

        // Gabungkan data pembelian ke dalam array laporan per tanggal
        foreach ($pembelianHarian as $data) {
            // Inisialisasi entri jika tanggal belum ada, lalu tambahkan pembelian
            if (!isset($laporanPerTanggal[$data->tanggal])) {
                $laporanPerTanggal[$data->tanggal] = [
                    'tanggal'          => $data->tanggal,
                    'total_penjualan'  => 0,
                    'total_pembelian'  => 0,
                ];
            }
            $laporanPerTanggal[$data->tanggal]['total_pembelian'] = (float) $data->total_pembelian_harian;
        }

        // Konversi associative array ke indexed array dan pastikan urutan
        $finalLaporanPerTanggal = array_values($laporanPerTanggal);
        usort($finalLaporanPerTanggal, function($a, $b) {
            return strtotime($a['tanggal']) - strtotime($b['tanggal']);
        });


        // 4. Hitung Rekap Total Global dari data agregasi harian
        $totalPenjualanBersihGlobal = array_sum(array_column($finalLaporanPerTanggal, 'total_penjualan'));
        $totalBiayaPembelianGlobal = array_sum(array_column($finalLaporanPerTanggal, 'total_pembelian'));
        $totalLabaRugiBersihGlobal = $totalPenjualanBersihGlobal - $totalBiayaPembelianGlobal;

        Log::info('Laporan generated successfully.');

        // Mengembalikan respons JSON yang sesuai dengan struktur komponen Vue.js
        return response()->json([
            'status' => 'success',
            'start_date' => $startDate->format('Y-m-d H:i:s'),
            'end_date' => $endDate->format('Y-m-d H:i:s'),
            'laporan_per_tanggal' => $finalLaporanPerTanggal, // Ini adalah data untuk tabel harian
            'rekap_total' => [
                // Dalam laporan harian yang disederhanakan ini, total kotor dan diskon tidak diagregasi secara terpisah.
                // Oleh karena itu, nilainya disetel sama dengan total_penjualan_bersih_global atau 0.
                'total_penjualan_kotor_global' => $totalPenjualanBersihGlobal, // Asumsi 'kotor' di sini mengacu pada total 'penjualan' yang dalam laporan sederhana ini adalah 'bersih'
                'total_diskon_global' => 0, // Tidak ada perhitungan diskon dalam laporan harian yang disederhanakan ini
                'total_penjualan_bersih_global' => $totalPenjualanBersihGlobal,
                'total_biaya_pembelian_global' => $totalBiayaPembelianGlobal,
                'total_laba_rugi_bersih_global' => $totalLabaRugiBersihGlobal,
            ]
        ]);
    }
}
