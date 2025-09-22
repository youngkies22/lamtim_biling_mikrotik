<?php

use App\Http\Controllers\Select\SelectController;

Route::prefix('select')->as('select.')->controller(SelectController::class)->group(function () {
  Route::get('/secret-api/{id}', 'SelectSecretApiMikrotik')->name('secret.api');
});
