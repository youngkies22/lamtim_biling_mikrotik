<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Master\AddressListController;

Route::get('/address-list/json', [AddressListController::class, 'json'])->name('address-list.json');
Route::post('/address-list/pull', [AddressListController::class, 'pull'])->name('address-list.pull');
Route::resource('/address-list', AddressListController::class);
