<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Master\IpPoolController;

Route::get('/ip-pool/json', [IpPoolController::class, 'json'])->name('ip-pool.json');
Route::post('/ip-pool/pull', [IpPoolController::class, 'pull'])->name('ip-pool.pull');
Route::resource('/ip-pool', IpPoolController::class);
