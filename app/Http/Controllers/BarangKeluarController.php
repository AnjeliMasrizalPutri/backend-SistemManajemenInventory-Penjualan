<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Satuan;
use App\Models\BarangKeluarDetail;
use App\Models\TransaksiBarangKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BarangKeluarController extends Controller
{
    public function index()
    {
        try {
            $transaksis = TransaksiBarangKeluar::with(['details.barang'])->get()->map(function ($transaksi) {
                $transaksi->showDetails = false;
                return [
                    'id'             => $transaksi->id,
                    'kode_transaksi' => $transaksi->kode_transaksi,
                    'tanggal_keluar' => $transaksi->tanggal_keluar,
                    'nama_pelanggan' => $transaksi->nama_pelanggan,
                    'details'        => $transaksi->details->map(function ($detail) {
                        return [
                            'id'            => $detail->id,
                            'barang_id'     => $detail->barang_id,
                            'jumlah_keluar' => $detail->jumlah_keluar,
                            'barang'        => $detail->barang,
                        ];
                    })->toArray(),
                    'showDetails' => false,
                ];
            });

            return response()->json([
                'success' => true,
                'data'    => $transaksis
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading barang keluar: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data transaksi barang keluar.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_keluar'          => 'required|date',
            'nama_pelanggan'          => 'required|string|max:255',
            'details'                 => 'required|array|min:1',
            'details.*.barang_id'     => 'required|exists:barangs,id',
            'details.*.jumlah_keluar' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1];
                    $detailData = $request->details[$index];

                    $barang = Barang::find($detailData['barang_id']);
                    if (!$barang) {
                        $fail("Barang tidak ditemukan untuk detail ke-" . ($index + 1) . ".");
                    } elseif ($value > $barang->stok) {
                        $fail("Stok untuk barang '{$barang->nama_barang}' tidak cukup! Stok tersedia: " . $barang->stok);
                    }
                },
            ],
        ], [
            'tanggal_keluar.required'          => 'Pilih tanggal keluar!',
            'nama_pelanggan.required'          => 'Nama Pelanggan wajib diisi!',
            'nama_pelanggan.string'            => 'Nama Pelanggan harus berupa teks!',
            'nama_pelanggan.max'               => 'Nama Pelanggan terlalu panjang!',
            'details.required'                 => 'Detail barang keluar wajib diisi!',
            'details.array'                    => 'Format detail barang keluar tidak valid!',
            'details.min'                      => 'Setidaknya harus ada satu barang yang dikeluarkan!',
            'details.*.barang_id.required'     => 'Barang pada detail wajib dipilih!',
            'details.*.barang_id.exists'       => 'Barang yang dipilih pada detail tidak valid!',
            'details.*.jumlah_keluar.required' => 'Jumlah keluar pada detail wajib diisi!',
            'details.*.jumlah_keluar.integer'  => 'Jumlah keluar pada detail harus berupa angka bulat!',
            'details.*.jumlah_keluar.min'      => 'Jumlah keluar pada detail minimal 1!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $lastTransaksiId = TransaksiBarangKeluar::max('id');
            $nextId = $lastTransaksiId ? $lastTransaksiId + 1 : 1;
            $kode_transaksi = 'TRX-OUT-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

            $transaksi = TransaksiBarangKeluar::create([
                'kode_transaksi' => $kode_transaksi,
                'tanggal_keluar' => $request->tanggal_keluar,
                'nama_pelanggan' => $request->nama_pelanggan,
            ]);

            foreach ($request->details as $detailData) {
                $barang = Barang::find($detailData['barang_id']);
                $harga_jual_saat_transaksi = $barang ? $barang->harga_jual : 0;

                BarangKeluarDetail::create([
                    'transaksi_barang_keluar_id' => $transaksi->id,
                    'barang_id'                  => $detailData['barang_id'],
                    'jumlah_keluar'              => $detailData['jumlah_keluar'],
                    'harga_jual_saat_transaksi'  => $harga_jual_saat_transaksi,
                ]);


                $barang = Barang::find($detailData['barang_id']);
                if ($barang) {
                    $barang->stok -= $detailData['jumlah_keluar'];
                    $barang->stok = max(0, $barang->stok);
                    $barang->save();
                } else {
                    Log::warning("Barang dengan ID {$detailData['barang_id']} tidak ditemukan saat mengurangi stok setelah transaksi keluar.");
                }
            }

            DB::commit();

            $transaksi->load(['details.barang']);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi barang keluar berhasil ditambahkan',
                'data'    => $transaksi
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding barang keluar (multi-item): ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan transaksi barang keluar.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail satu transaksi barang keluar.
     */
    public function show($id)
    {
        try {
            $transaksi = TransaksiBarangKeluar::with(['details.barang'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data'    => $transaksi
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data transaksi keluar tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error showing barang keluar: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data transaksi keluar.'
            ], 500);
        }
    }

    /**
     * Memperbarui transaksi barang keluar dan menyesuaikan stok.
     */
    public function update(Request $request, $id)
    {
        try {
            $transaksi = TransaksiBarangKeluar::with('details')->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data transaksi keluar tidak ditemukan.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tanggal_keluar'          => 'required|date',
            'nama_pelanggan'          => 'required|string|max:255',
            'details'                 => 'required|array|min:1',
            'details.*.id'            => 'nullable|exists:barang_keluar_details,id',
            'details.*.barang_id'     => 'required|exists:barangs,id',
            'details.*.jumlah_keluar' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($request, $transaksi) {
                    $index = explode('.', $attribute)[1];
                    $detailData = $request->details[$index];
                    $currentDetailId = $detailData['id'] ?? null;

                    $barang = Barang::find($detailData['barang_id']);
                    if (!$barang) {
                        $fail("Barang tidak ditemukan untuk detail ke-" . ($index + 1) . ".");
                    } else {
                        $oldJumlah = 0;
                        if ($currentDetailId) {
                            $existingDetail = $transaksi->details->find($currentDetailId);
                            if ($existingDetail && $existingDetail->barang_id == $detailData['barang_id']) {
                                $oldJumlah = $existingDetail->jumlah_keluar;
                            }
                        }

                        $stokTersediaSaatIni = $barang->stok + $oldJumlah;

                        if ($value > $stokTersediaSaatIni) {
                            $fail("Stok untuk barang '{$barang->nama_barang}' tidak cukup! Stok tersedia: " . $stokTersediaSaatIni);
                        }
                    }
                },
            ],
        ], [
            'tanggal_keluar.required'          => 'Pilih tanggal keluar!',
            'nama_pelanggan.required'          => 'Nama Pelanggan wajib diisi!',
            'nama_pelanggan.string'            => 'Nama Pelanggan harus berupa teks!',
            'nama_pelanggan.max'               => 'Nama Pelanggan terlalu panjang!',
            'details.required'                 => 'Detail barang keluar wajib diisi!',
            'details.array'                    => 'Format detail barang keluar tidak valid!',
            'details.min'                      => 'Setidaknya harus ada satu barang yang dikeluarkan!',
            'details.*.barang_id.required'     => 'Barang pada detail wajib dipilih!',
            'details.*.barang_id.exists'       => 'Barang yang dipilih pada detail tidak valid!',
            'details.*.jumlah_keluar.required' => 'Jumlah keluar pada detail wajib diisi!',
            'details.*.jumlah_keluar.integer'  => 'Jumlah keluar pada detail harus berupa angka bulat!',
            'details.*.jumlah_keluar.min'      => 'Jumlah keluar pada detail minimal 1!',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $transaksi->update([
                'tanggal_keluar' => $request->tanggal_keluar,
                'nama_pelanggan' => $request->nama_pelanggan
            ]);

            $existingDetails = $transaksi->details->keyBy('id');
            $requestedDetailIds = collect($request->details)->pluck('id')->filter()->all();

            foreach ($existingDetails as $detailId => $detail) {
                if (!in_array($detailId, $requestedDetailIds)) {
                    $barang = Barang::find($detail->barang_id);
                    if ($barang) {
                        $barang->stok += $detail->jumlah_keluar;
                        $barang->save();
                    } else {
                        Log::warning("Barang lama dengan ID {$detail->barang_id} tidak ditemukan saat mengembalikan stok sebelum penghapusan detail.");
                    }
                    $detail->delete();
                }
            }

            foreach ($request->details as $detailData) {
                if (isset($detailData['id']) && $existingDetails->has($detailData['id'])) {
                    $detail = $existingDetails->get($detailData['id']);

                    $oldJumlah = $detail->jumlah_keluar;
                    $oldBarangId = $detail->barang_id;

                    $detail->update([
                        'barang_id'    => $detailData['barang_id'],
                        'jumlah_keluar' => $detailData['jumlah_keluar'],
                    ]);

                    if ($oldBarangId != $detailData['barang_id']) {
                        $oldBarang = Barang::find($oldBarangId);
                        if ($oldBarang) {
                            $oldBarang->stok += $oldJumlah;
                            $oldBarang->save();
                        }
                        $newBarang = Barang::find($detailData['barang_id']);
                        if ($newBarang) {
                            $newBarang->stok -= $detailData['jumlah_keluar'];
                            $newBarang->stok = max(0, $newBarang->stok);
                            $newBarang->save();
                        }
                    } else {
                        $barang = Barang::find($detailData['barang_id']);
                        if ($barang) {
                            $diffJumlah = $detailData['jumlah_keluar'] - $oldJumlah;
                            $barang->stok -= $diffJumlah;
                            $barang->stok = max(0, $barang->stok);
                            $barang->save();
                        }
                    }
                } else {
                    $newDetail = BarangKeluarDetail::create([
                        'transaksi_barang_keluar_id' => $transaksi->id,
                        'barang_id'                  => $detailData['barang_id'],
                        'jumlah_keluar'              => $detailData['jumlah_keluar'],
                    ]);
                    $barang = Barang::find($detailData['barang_id']);
                    if ($barang) {
                        $barang->stok -= $detailData['jumlah_keluar'];
                        $barang->stok = max(0, $barang->stok);
                        $barang->save();
                    } else {
                        Log::warning("Barang baru dengan ID {$detailData['barang_id']} tidak ditemukan saat mengurangi stok.");
                    }
                }
            }

            DB::commit();
            $transaksi->load(['details.barang']);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi barang keluar berhasil diperbarui',
                'data'    => $transaksi
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating barang keluar (multi-item): ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui transaksi barang keluar.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus transaksi barang keluar dan mengembalikan stok.
     */
    public function destroy($id)
    {
        try {
            $transaksi = TransaksiBarangKeluar::with('details')->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data transaksi keluar tidak ditemukan!'
            ], 404);
        }

        DB::beginTransaction();

        try {
            foreach ($transaksi->details as $detail) {
                $barang = Barang::find($detail->barang_id);
                if ($barang) {
                    $barang->stok += $detail->jumlah_keluar;
                    $barang->save();
                } else {
                    Log::warning("Barang dengan ID {$detail->barang_id} tidak ditemukan saat mengembalikan stok setelah penghapusan transaksi.");
                }
                $detail->delete();
            }

            $transaksi->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi Barang Keluar berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting barang keluar: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi barang keluar.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan data barang untuk autocomplete berdasarkan nama barang.
     * Mengembalikan ID, nama, kode, stok, dan nama satuan.
     */
    public function getAutoCompleteData(Request $request)
    {
        $query = $request->input('q');
        $barang = Barang::with('satuan')
            ->where('nama_barang', 'like', '%' . $query . '%')
            ->first();

        if ($barang) {
            return response()->json([
                'id'          => $barang->id,
                'nama_barang' => $barang->nama_barang,
                'kode_barang' => $barang->kode_barang,
                'stok'        => $barang->stok,
                'satuan_id'   => $barang->satuan_id,
                'nama_satuan' => $barang->satuan ? $barang->satuan->nama_satuan : null,
            ]);
        }

        return response()->json([
            'message' => 'Barang tidak ditemukan'
        ], 404);
    }

    /**
     * Mendapatkan stok dan satuan barang berdasarkan ID barang.
     */
    public function getStok(Request $request)
    {
        $barangId = $request->input('barang_id');
        $barang = Barang::with('satuan')->find($barangId);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan.'
            ], 404);
        }

        $response = [
            'stok'        => $barang->stok,
            'satuan_id'   => $barang->satuan_id,
            'nama_satuan' => $barang->satuan ? $barang->satuan->nama_satuan : null,
        ];

        return response()->json($response);
    }

    /**
     * Mendapatkan semua data satuan.
     */
    public function getSatuan()
    {
        $satuans = Satuan::all();
        return response()->json($satuans);
    }

    /**
     * Mendapatkan daftar barang berdasarkan query pencarian.
     */
    public function getBarangs(Request $request)
    {
        if ($request->has('q')) {
            $barangs = Barang::where('nama_barang', 'like', '%' . $request->input('q') . '%')->get();
            return response()->json($barangs);
        }
        return response()->json([]);
    }

    public function laporanPendapatan(Request $request)
    {
        $query = BarangKeluarDetail::query()
            ->join('barangs', 'barang_keluar_details.barang_id', '=', 'barangs.id')
            ->selectRaw('
            SUM(barang_keluar_details.jumlah_keluar * barang_keluar_details.harga_jual_saat_transaksi) as total_penjualan,
            SUM(barang_keluar_details.jumlah_keluar * barangs.harga_beli) as total_modal
        ');

        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->join('transaksi_barang_keluars', 'barang_keluar_details.transaksi_barang_keluar_id', '=', 'transaksi_barang_keluars.id')
                ->whereBetween('transaksi_barang_keluars.tanggal_keluar', [
                    $request->tanggal_awal,
                    $request->tanggal_akhir
                ]);
        }

        $result = $query->first();

        $laba = $result->total_penjualan - $result->total_modal;

        return response()->json([
            'success' => true,
            'data' => [
                'total_penjualan' => $result->total_penjualan,
                'total_modal'     => $result->total_modal,
                'laba_kotor'      => $laba
            ]
        ]);
    }
}
