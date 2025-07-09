<?php

use App\Http\Controllers\Api\Master\PelangganController;
use Illuminate\Support\Facades\Route;

Route::group([
    // 'middleware' => 'auth:api',
    // 'middleware' => 'jwt.verify',
    'prefix' => 'master/pelanggan'
], function () {
    Route::get('/get-list', [PelangganController::class, 'index']);
    Route::post('/simpan', [PelangganController::class, 'store']);
    Route::post('/delete', [PelangganController::class, 'hapus']);
});
