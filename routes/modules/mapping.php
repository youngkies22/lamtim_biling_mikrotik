<?php

use App\Http\Controllers\Master\MappingController;

Route::prefix('mapping')->as('mapping.')->controller(MappingController::class)->group(function () {
  Route::get('/', 'server')->name('server');
  Route::post('/store-server', 'storeServer')->name('store.server');
  Route::get('/json/server', [MappingController::class, 'json'])->name('mapping.json');
  Route::get('/json/server', 'jsonServer')->name('json.server');
  Route::get('/json/mapping/user/{id}', 'jsonGetMapDataByUser')->name('json.mapping.user');
  Route::get('/json/mapping/show/user', 'jsonGetShowMapUser')->name('json.show.mapping.user');
});
