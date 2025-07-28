<?php

use App\Http\Controllers\Api\Transactions\OrderController;
use Illuminate\Support\Facades\Route;

Route::group([
    // 'middleware' => 'auth:api',
    // 'middleware' => 'auth:sanctum',
    'prefix' => 'transactions/order'
], function () {
    Route::get('/get-list', [OrderController::class, 'index']);
    // Route::get('/get-list', [OrderController::class, 'indexOld']);
    Route::get('/header/get-list', [OrderController::class, 'header_get_list']);
    Route::get('/record/get-list', [OrderController::class, 'record_get_list']);

    // Route::post('/simpan', [OrderController::class, 'store']);
    Route::post('/simpan', [OrderController::class, 'simpan']);
    Route::post('/header/simpan', [OrderController::class, 'header_store']);
    Route::post('/record/simpan', [OrderController::class, 'record_store']);

    Route::post('/delete', [OrderController::class, 'hapus']);
    Route::post('/header/delete', [OrderController::class, 'header_hapus']);
    Route::post('/record/delete', [OrderController::class, 'record_hapus']);
});
