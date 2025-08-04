<?php

use App\Http\Controllers\Api\Master\SatuanController;
use Illuminate\Support\Facades\Route;

Route::group([
    // 'middleware' => 'auth:api',
    'middleware' => 'auth:sanctum',
    'prefix' => 'master/satuan'
], function () {
    Route::get('/get-list', [SatuanController::class, 'index']);
    Route::post('/simpan', [SatuanController::class, 'store']);
    Route::post('/delete', [SatuanController::class, 'hapus']);
});
