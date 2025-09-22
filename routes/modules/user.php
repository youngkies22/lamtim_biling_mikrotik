<?php

use App\Http\Controllers\Master\UserController;

Route::prefix('user')->as('user.')->controller(UserController::class)->group(function () {
  Route::get('/data/json', 'json')->name('json');
  Route::get('/list/mapping', 'mapping')->name('mapping');
  Route::get('/data/mapping/json', 'jsonMapping')->name('mapping.json');
  Route::get('/edit/mapping/{id}', 'editMapping')->name('edit.mapping');
  Route::post('/store/mapping', 'storeMapping')->name('mapping.store');
  Route::get('/show/map', 'showMapping')->name('show.mapping');

  Route::get('/', 'index')->name('index');
  Route::get('/create', 'create')->name('create');
  Route::post('/', 'store')->name('store');
  Route::get('/{id}', 'show')->name('show');
  Route::get('/{id}/edit', 'edit')->name('edit');
  Route::post('/{id}', 'update')->name('update');
  Route::delete('/{id}', 'destroy')->name('destroy');
});
