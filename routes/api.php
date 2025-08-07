<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JenisController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\HakAksesController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\LaporanPendapatanController;

// AUTH ROUTES

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// RUN SCHEDULE (bebas autentikasi)

Route::get('/run-schedule', function () {
    Artisan::call('schedule:run');
    return response()->json([
        'status' => true,
        'message' => 'Schedule dijalankan'
    ]);
});

// GET USER DATA

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// LIST USERS (admin only)

Route::middleware(['auth:sanctum', 'checkRole:admin'])->get('/users', function () {
    return User::with('role')->get();
});

// PROTECTED ROUTES

Route::middleware('auth:sanctum')->group(function () {

    /**
     *  Admin & Kasir
     */
    Route::middleware('checkRole:admin,kasir')->group(function () {
        // Barang
        Route::get('/barang', [BarangController::class, 'index']);
        Route::get('/barang/{barang}', [BarangController::class, 'show']);

        // Transaksi
        Route::apiResource('/barangkeluar', BarangKeluarController::class);
        Route::get('/barangmasuk', [BarangMasukController::class, 'index']);

        // Master data (readonly)
        Route::get('/jenis', [JenisController::class, 'index']);
        Route::get('/jenis/{jenis}', [JenisController::class, 'show']);

        Route::get('/satuan', [SatuanController::class, 'index']);
        Route::get('/satuan/{satuan}', [SatuanController::class, 'show']);

        // Laporan
        Route::get('/laporan-pendapatan', [LaporanPendapatanController::class, 'getPendapatan']);
        Route::get('/laporan/pendapatan', [BarangKeluarController::class, 'laporanPendapatan']);
    });

    /**
     *  Admin Only
     */
    Route::middleware('checkRole:admin')->group(function () {
        // Pengguna
        Route::get('/data-pengguna/get-data', [UserController::class, 'getDataPengguna']);
        Route::get('/role', [UserController::class, 'getRoles']);
        Route::apiResource('/data-pengguna', UserController::class);

        // Hak Akses
        Route::get('/hak-akses/get-data', [HakAksesController::class, 'getDataRole']);
        Route::apiResource('/hak-akses', HakAksesController::class);

        // Data Master
        Route::post('/barang', [BarangController::class, 'store']);
        Route::put('/barang/{barang}', [BarangController::class, 'update']);
        Route::delete('/barang/{barang}', [BarangController::class, 'destroy']);

        Route::post('/jenis', [JenisController::class, 'store']);
        Route::put('/jenis/{jenis}', [JenisController::class, 'update']);
        Route::delete('/jenis/{jenis}', [JenisController::class, 'destroy']);

        Route::post('/satuan', [SatuanController::class, 'store']);
        Route::put('/satuan/{satuan}', [SatuanController::class, 'update']);
        Route::delete('/satuan/{satuan}', [SatuanController::class, 'destroy']);

        Route::apiResource('/supplier', SupplierController::class);

        // Barang Masuk (full access)
        Route::get('/barangmasuk/{id}', [BarangMasukController::class, 'show']);
        Route::post('/barangmasuk', [BarangMasukController::class, 'store']);
        Route::put('/barangmasuk/{barangmasuk}', [BarangMasukController::class, 'update']);
        Route::delete('/barangmasuk/{barangmasuk}', [BarangMasukController::class, 'destroy']);
    });

});
