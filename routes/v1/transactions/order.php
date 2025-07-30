<?php

use App\Http\Controllers\Api\Transactions\OrderController;
use Illuminate\Support\Facades\Route;

Route::group([
    // 'middleware' => 'auth:api',
    'middleware' => 'auth:sanctum',
    'prefix' => 'transactions/order'
], function () {
    // List Order
    Route::get('/get-list', [OrderController::class, 'index']);

    // Create Or Update Order
    Route::post('/simpan', [OrderController::class, 'simpan']);

    // Lock And Unlock
    Route::post('/lock-order', [OrderController::class, 'lock_order']);
    Route::post('/unlock-order', [OrderController::class, 'open_lock_order']);    
    
    // Hapus Order (Jika belum ada penerimaan)
    Route::post('/delete', [OrderController::class, 'hapus']);
    Route::post('/delete-record', [OrderController::class, 'record_hapus']);

    // // Route::get('/get-list', [OrderController::class, 'indexOld']);
    // Route::get('/header/get-list', [OrderController::class, 'header_get_list']);
    // Route::get('/record/get-list', [OrderController::class, 'record_get_list']);

    // // Route::post('/simpan', [OrderController::class, 'store']);
    // Route::post('/header/simpan', [OrderController::class, 'header_store']);
    // Route::post('/record/simpan', [OrderController::class, 'record_store']);

    // Route::post('/header/delete', [OrderController::class, 'header_hapus']);
});
