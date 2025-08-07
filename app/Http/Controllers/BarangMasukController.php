<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Satuan;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\BarangMasukDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\TransaksiBarangMasuk;
use Illuminate\Support\Facades\Validator;

class BarangMasukController extends Controller
{

    public function index()
    {
        try {
            $transaksis = TransaksiBarangMasuk::with(['supplier', 'details.barang'])->get()->map(function ($transaksi) {
                return [
                    'id'             => $transaksi->id,
                    'kode_transaksi' => $transaksi->kode_transaksi,
                    'tanggal_masuk'  => $transaksi->tanggal_masuk,
                    'supplier_id'    => $transaksi->supplier_id,
                    'supplier'       => $transaksi->supplier,
                    'details'        => $transaksi->details->map(function ($detail) {
                        return [
                            'id'           => $detail->id,
                            'barang_id'    => $detail->barang_id,
                            'jumlah_masuk' => $detail->jumlah_masuk,
                            'barang'       => $detail->barang,
                        ];
                    })->toArray(),
                ];
            });

            return response()->json([
                'success' => true,
                'data'    => $transaksis
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading barang masuk: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data transaksi barang masuk.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_masuk'          => 'required|date',
            'supplier_id'            => 'required|exists:suppliers,id',
            'details'                => 'required|array|min:1',
            'details.*.barang_id'    => 'required|exists:barangs,id',
            'details.*.jumlah_masuk' => 'required|integer|min:1',
        ], [
            'tanggal_masuk.required'          => 'Pilih tanggal masuk!',
            'supplier_id.required'            => 'Supplier wajib dipilih!',
            'details.required'                => 'Detail barang masuk wajib diisi!',
            'details.min'                     => 'Setidaknya harus ada satu barang yang dimasukkan!',
            'details.*.barang_id.required'    => 'Barang pada detail wajib dipilih!',
            'details.*.barang_id.exists'      => 'Barang pada detail tidak ditemukan!',
            'details.*.jumlah_masuk.required' => 'Jumlah masuk pada detail wajib diisi!',
            'details.*.jumlah_masuk.integer'  => 'Jumlah masuk harus berupa angka!',
            'details.*.jumlah_masuk.min'      => 'Jumlah masuk minimal 1!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {

            $lastTransaksiId = TransaksiBarangMasuk::max('id');
            $nextId = $lastTransaksiId ? $lastTransaksiId + 1 : 1;
            $kode_transaksi = 'TRX-IN-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);


            $transaksi = TransaksiBarangMasuk::create([
                'kode_transaksi' => $kode_transaksi,
                'tanggal_masuk'  => $request->tanggal_masuk,
                'supplier_id'    => $request->supplier_id,
            ]);

            foreach ($request->details as $detailData) {
                $barang = Barang::find($detailData['barang_id']);
                $harga_beli_saat_transaksi = $barang ? $barang->harga_beli : 0;
                BarangMasukDetail::create([
                    'transaksi_barang_masuk_id' => $transaksi->id,
                    'barang_id'                 => $detailData['barang_id'],
                    'jumlah_masuk'              => $detailData['jumlah_masuk'],
                    'harga_beli_saat_transaksi' => $harga_beli_saat_transaksi,

                ]);

                $barang = Barang::find($detailData['barang_id']);
                if ($barang) {
                    $barang->stok += $detailData['jumlah_masuk'];
                    $barang->save();
                }
            }

            DB::commit();

            $transaksi->load(['supplier', 'details.barang']);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi barang masuk berhasil ditambahkan',
                'data'    => $transaksi
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding barang masuk: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan transaksi barang masuk.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function show($id)
    {
        $transaksi = TransaksiBarangMasuk::with(['supplier', 'details.barang'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $transaksi
        ]);
    }


    public function getTransaksiDetails($id)
    {
        $transaksi = TransaksiBarangMasuk::with('details.barang')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $transaksi->details->map(function ($detail) {
                return [
                    'id'           => $detail->id,
                    'barang_id'    => $detail->barang_id,
                    'jumlah_masuk' => $detail->jumlah_masuk,
                    'barang'       => $detail->barang
                ];
            })
        ]);
    }

    /**
     * Memperbarui transaksi barang masuk beserta detail dan stok.
     */
    public function update(Request $request, $id)
    {
        $transaksi = TransaksiBarangMasuk::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'tanggal_masuk'            => 'required|date',
            'supplier_id'              => 'required|exists:suppliers,id',
            'details'                  => 'required|array|min:1',
            'details.*.barang_id'      => 'required|exists:barangs,id',
            'details.*.jumlah_masuk'   => 'required|integer|min:1',
            'details.*.detail_id'      => 'nullable|exists:barang_masuk_details,id',
        ], [
            'tanggal_masuk.required'          => 'Pilih tanggal masuk!',
            'supplier_id.required'            => 'Supplier wajib dipilih!',
            'details.required'                => 'Detail barang masuk wajib diisi!',
            'details.min'                     => 'Setidaknya harus ada satu barang yang dimasukkan!',
            'details.*.barang_id.required'    => 'Barang pada detail wajib dipilih!',
            'details.*.barang_id.exists'      => 'Barang pada detail tidak ditemukan!',
            'details.*.jumlah_masuk.required' => 'Jumlah masuk pada detail wajib diisi!',
            'details.*.jumlah_masuk.integer'  => 'Jumlah masuk harus berupa angka!',
            'details.*.jumlah_masuk.min'      => 'Jumlah masuk minimal 1!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $transaksi->update([
                'tanggal_masuk' => $request->tanggal_masuk,
                'supplier_id'   => $request->supplier_id,
            ]);

            $existingDetails = $transaksi->details->keyBy('id');
            $requestDetailIds = collect($request->details)->pluck('detail_id')->filter()->all();

            foreach ($existingDetails as $detailId => $detail) {
                if (!in_array($detailId, $requestDetailIds)) {
                    $barang = Barang::find($detail->barang_id);
                    if ($barang) {
                        $barang->stok -= $detail->jumlah_masuk;
                        $barang->stok = max(0, $barang->stok);
                        $barang->save();
                    }
                    $detail->delete();
                }
            }

            foreach ($request->details as $detailData) {
                if (isset($detailData['detail_id']) && $existingDetails->has($detailData['detail_id'])) {
                    $detail = $existingDetails->get($detailData['detail_id']);

                    $oldJumlah = $detail->jumlah_masuk;
                    $oldBarangId = $detail->barang_id;

                    $detail->update([
                        'barang_id'    => $detailData['barang_id'],
                        'jumlah_masuk' => $detailData['jumlah_masuk'],
                    ]);

                    if ($oldBarangId != $detailData['barang_id']) {
                        $oldBarang = Barang::find($oldBarangId);
                        if ($oldBarang) {
                            $oldBarang->stok -= $oldJumlah;
                            $oldBarang->stok = max(0, $oldBarang->stok);
                            $oldBarang->save();
                        }

                        $newBarang = Barang::find($detailData['barang_id']);
                        if ($newBarang) {
                            $newBarang->stok += $detailData['jumlah_masuk'];
                            $newBarang->save();
                        }
                    } else {
                        $barang = Barang::find($detailData['barang_id']);
                        if ($barang) {
                            $diff = $detailData['jumlah_masuk'] - $oldJumlah;
                            $barang->stok += $diff;
                            $barang->stok = max(0, $barang->stok);
                            $barang->save();
                        }
                    }
                } else {
                    $barang = Barang::find($detailData['barang_id']);
                    $harga_beli_saat_transaksi = $barang ? $barang->harga_beli : 0;

                    BarangMasukDetail::create([
                        'transaksi_barang_masuk_id' => $transaksi->id,
                        'barang_id'                 => $detailData['barang_id'],
                        'jumlah_masuk'              => $detailData['jumlah_masuk'],
                        'harga_beli_saat_transaksi' => $harga_beli_saat_transaksi,
                    ]);

                    if ($barang) {
                        $barang->stok += $detailData['jumlah_masuk'];
                        $barang->save();
                    }
                }
            }

            DB::commit();

            $transaksi->load(['supplier', 'details.barang']);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi barang masuk berhasil diperbarui',
                'data'    => $transaksi
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating barang masuk: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui transaksi barang masuk.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Menghapus transaksi barang masuk dan mengembalikan stok.
     */
    public function destroy($id)
    {
        $transaksi = TransaksiBarangMasuk::with('details')->findOrFail($id);

        DB::beginTransaction();

        try {
            foreach ($transaksi->details as $detail) {
                $barang = Barang::find($detail->barang_id);
                if ($barang) {
                    $barang->stok -= $detail->jumlah_masuk;
                    $barang->stok = max(0, $barang->stok);
                    $barang->save();
                }
                $detail->delete();
            }

            $transaksi->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi Barang Masuk berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting barang masuk: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi barang masuk.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function getAutoCompleteData(Request $request)
    {
        $barang = Barang::where('nama_barang', 'like', '%' . $request->nama_barang . '%')->first();

        if ($barang) {
            $satuan = Satuan::find($barang->satuan_id);
            return response()->json([
                'id'          => $barang->id,
                'nama_barang' => $barang->nama_barang,
                'kode_barang' => $barang->kode_barang,
                'stok'        => $barang->stok,
                'satuan_id'   => $barang->satuan_id,
                'nama_satuan' => $satuan ? $satuan->nama_satuan : null,
            ]);
        }

        return response()->json([
            'message' => 'Barang tidak ditemukan'
        ], 404);
    }

    public function getSatuan()
    {
        $satuans = Satuan::all();
        return response()->json($satuans);
    }
}
