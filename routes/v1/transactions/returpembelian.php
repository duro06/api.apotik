<?php

use App\Http\Controllers\Api\Transactions\ReturPembelianController;
use Illuminate\Support\Facades\Route;


Route::group([
    // 'middleware' => 'auth:api',
    'middleware' => 'auth:sanctum',
    'prefix' => 'transactions/returpembelian'
], function () {

    Route::get('/get-pembelian', [ReturPembelianController::class, 'getpenerimaan']);
    Route::get('/get-list', [ReturPembelianController::class, 'index']);
    // Route::post('/simpan', [ReturPembelianController::class, 'simpan']);
    // Route::post('/lock-retur-penjualan', [ReturPembelianController::class, 'lock_retur_penjualan']);
    // Route::post('/delete', [ReturPembelianController::class, 'hapus']);
    // Route::post('/delete-rinci', [ReturPembelianController::class, 'hapus_rincian_tidak_dikunci']);
});
