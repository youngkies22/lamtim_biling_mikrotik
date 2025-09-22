<?php
// 📡 Mikrotik Routes
use App\Http\Controllers\Mikrotik\MikrotikController;

Route::prefix('mikrotik')->as('mikrotik.')->controller(MikrotikController::class)->group(function () {
  Route::get('/', 'index')->name('index');
  Route::get('/create', 'create')->name('create');
  Route::post('/', 'store')->name('store');
  Route::get('/{id}', 'show')->name('show');
  Route::get('/{id}/edit', 'edit')->name('edit');
  Route::put('/{id}', 'update')->name('update');
  Route::delete('/{id}', 'destroy')->name('destroy');
  Route::get('/data/json', 'json')->name('json');
});
