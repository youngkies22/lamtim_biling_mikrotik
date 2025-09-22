<?php

use App\Http\Controllers\Master\TagihanController;

Route::prefix('tagihan')->as('tagihan.')->controller(TagihanController::class)->group(function () {
  Route::get('/', 'index')->name('index');
  Route::get('/lunas', 'lunas')->name('lunas');
  Route::get('/generate-tagihan-bulanan', 'generateTagihanBulanan')->name('generate-tagihan-bulanan');
  Route::get('/data/json', 'json')->name('json');

  Route::post('/bayar/{id}', 'bayar')->name('bayar');
  // Route untuk batal pembayaran
  Route::post('/batal-pembayaran/{id}', 'batalPembayaran')->name('batal-pembayaran');

  // Route untuk CRUD standar
  // Route::get('/create', 'create')->name('create');
  // Route::post('/', 'store')->name('store');
  Route::get('/{id}', 'show')->name('show');
  Route::get('/{id}/edit', 'edit')->name('edit');
  // Route::put('/{id}', 'update')->name('update');
  Route::delete('/destroy/{id}', 'destroy')->name('destroy');
});
