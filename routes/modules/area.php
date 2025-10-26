<?php

use App\Http\Controllers\Master\AreaController;

Route::prefix('area')->as('area.')->controller(AreaController::class)->group(function () {
  Route::get('/json', 'json')->name('json');
  Route::get('/', 'index')->name('index');
  Route::get('/create', 'create')->name('create');
  Route::post('/', 'store')->name('store');
  Route::get('/{id}', 'show')->name('show');
  Route::get('/{id}/edit', 'edit')->name('edit');

  // Custom routes untuk update dan delete yang sesuai dengan frontend
  Route::post('/{id}/update', 'update')->name('custom.update');
  Route::delete('/{id}/delete', 'destroy')->name('custom.delete');

  // Standard REST routes
  Route::put('/{id}', 'update')->name('update');
  Route::delete('/{id}', 'destroy')->name('destroy');
});
