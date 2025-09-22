<?php

// 🧵 ODP Routes
use App\Http\Controllers\Mikrotik\OdpController;

Route::prefix('odp')->as('odp.')->controller(OdpController::class)->group(function () {
  Route::get('/', 'index')->name('index');
  Route::post('/', 'store')->name('store');
  Route::put('/{id}', 'update')->name('update');
  Route::delete('/{id}', 'destroy')->name('destroy');
  Route::get('/data/json', 'json')->name('json');
});
