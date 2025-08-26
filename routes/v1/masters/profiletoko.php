<?php

use App\Http\Controllers\Api\Master\ProfileTokoController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'master/profile-toko'
], function () {
  Route::get('/get-profile', [ProfileTokoController::class, 'index']);
  Route::post('/simpan', [ProfileTokoController::class, 'store']);
});
