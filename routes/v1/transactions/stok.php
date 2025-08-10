<?php

use App\Http\Controllers\Api\Transactions\StokController;
use Illuminate\Support\Facades\Route;

Route::group([
    // 'middleware' => 'auth:api',
    // 'middleware' => 'auth:sanctum',
    'prefix' => 'transactions/stok'
], function () {
    Route::get('/get-list', [StokController::class, 'index']);
});
