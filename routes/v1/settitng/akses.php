<?php

use App\Http\Controllers\Api\Setting\MenuController;
use Illuminate\Support\Facades\Route;

Route::group([
  // 'middleware' => 'auth:api',
  'middleware' => 'auth:sanctum',
  'prefix' => 'setting/hak-akses'
], function () {
  Route::get('/get-user', [MenuController::class, 'index']);
  Route::post('/grant', [MenuController::class, 'grant']);
  Route::post('/revoke', [MenuController::class, 'revoke']);
});
