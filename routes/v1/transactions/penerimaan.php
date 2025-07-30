<?php

use App\Http\Controllers\Api\Transactions\PenerimaanController;
use Illuminate\Support\Facades\Route;

Route::group([
    // 'middleware' => 'auth:api',
    // 'middleware' => 'auth:sanctum',
    'prefix' => 'transactions/penerimaan'
], function () {
    // List Order
    Route::get('/get-list', [PenerimaanController::class, 'index']);

    // Create Or Update Order
    Route::post('/simpan', [PenerimaanController::class, 'simpan']);

    // Lock And Unlock
    Route::post('/lock-order', [PenerimaanController::class, 'lock_order']);
    Route::post('/unlock-order', [PenerimaanController::class, 'open_lock_order']);

    // Hapus Order (Jika belum ada penerimaan)
    Route::post('/delete', [PenerimaanController::class, 'hapus']);
    Route::post('/delete-record', [PenerimaanController::class, 'record_hapus']);

    // // Route::get('/get-list', [OrderController::class, 'indexOld']);
    // Route::get('/header/get-list', [OrderController::class, 'header_get_list']);
    // Route::get('/record/get-list', [OrderController::class, 'record_get_list']);

    // // Route::post('/simpan', [OrderController::class, 'store']);
    // Route::post('/header/simpan', [OrderController::class, 'header_store']);
    // Route::post('/record/simpan', [OrderController::class, 'record_store']);

    // Route::post('/header/delete', [OrderController::class, 'header_hapus']);
});
