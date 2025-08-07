<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $barangs = Barang::with(['jenis', 'satuan'])->get()->map(function ($barang) {
            return [
                'id'            => $barang->id,
                'kode_barang'   => $barang->kode_barang,
                'nama_barang'   => $barang->nama_barang,
                'harga_beli'    => $barang->harga_beli,
                'harga_jual'    => $barang->harga_jual,
                'stok'          => $barang->stok,
                'stok_minimum'  => $barang->stok_minimum,
                'jenis_id'      => $barang->jenis_id,
                'satuan_id'     => $barang->satuan_id,
                'jenis_nama'    => $barang->jenis->nama_jenis ?? null,
                'satuan_nama'   => $barang->satuan->nama_satuan ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $barangs
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_barang' => 'required|string|max:100|unique:barangs,nama_barang',
            'jenis_id'     => 'required|exists:jenis,id',
            'satuan_id'    => 'required|exists:satuans,id',
            'harga_beli'   => 'required|numeric|min:0',
            'harga_jual'   => 'required|numeric|min:0',
            'stok_minimum' => 'required|integer|min:0',
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi',
            'jenis_id.required' => 'Pilih Jenis Barang !',
            'jenis_id.exists' => 'Pilih Jenis Barang !',
            'satuan_id.required' => 'Pilih Satuan Barang !',
            'satuan_id.exists' => 'Pilih Satuan Barang !',
            'harga_beli.required' => 'Harga beli wajib diisi',
            'harga_jual.required' => 'Harga jual wajib diisi',
            'stok_minimum.required' => 'Stok minimum wajib diisi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $kode_barang = 'BRG-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $barang = Barang::create([
            'nama_barang'  => $request->nama_barang,
            'kode_barang'  => $kode_barang,
            'jenis_id'     => $request->jenis_id,
            'satuan_id'    => $request->satuan_id,
            'harga_beli'   => $request->harga_beli,
            'harga_jual'   => $request->harga_jual,
            'stok'         => 0,
            'stok_minimum' => $request->stok_minimum,
        ]);

        $barang->load(['jenis', 'satuan']);


        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil ditambahkan',
            'data' => $barang
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $barang = Barang::with(['jenis', 'satuan'])->find($id);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $barang
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_barang'  => 'required|string|max:100|unique:barangs,nama_barang,' . $id,
            'jenis_id'     => 'required|exists:jenis,id',
            'satuan_id'    => 'required|exists:satuans,id',
            'harga_beli'   => 'required|numeric|min:0',
            'harga_jual'   => 'required|numeric|min:0',
            'stok_minimum' => 'required|integer|min:0',
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi',
            'jenis_id.required' => 'Jenis barang wajib dipilih',
            'jenis_id.exists' => 'Jenis barang tidak valid',
            'satuan_id.required' => 'Satuan wajib dipilih',
            'satuan_id.exists' => 'Satuan tidak valid',
            'harga_beli.required' => 'Harga beli wajib diisi',
            'harga_jual.required' => 'Harga jual wajib diisi',
            'stok_minimum.required' => 'Stok minimum wajib diisi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $kode_barang = 'BRG-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $barang->update([
            'nama_barang'  => $request->nama_barang,
            'jenis_id'     => $request->jenis_id,
            'satuan_id'    => $request->satuan_id,
            'harga_beli'   => $request->harga_beli,
            'harga_jual'   => $request->harga_jual,
            'stok_minimum' => $request->stok_minimum,
        ]);

        $barang->load(['jenis', 'satuan']);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => $barang
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $barang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus'
        ]);
    }
}
