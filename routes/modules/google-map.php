<?php

use App\Http\Controllers\Master\GoogleMapController;

Route::prefix('google-map')->as('google-map.')->controller(GoogleMapController::class)->group(function () {
    // Views
    Route::get('/', 'index')->name('index');
    Route::get('/standalone', 'standalone')->name('standalone');

    // Data endpoints
    Route::get('/data', 'getMapData')->name('data');
    Route::get('/select-options', 'getSelectOptions')->name('select-options');

    // === POSITION & WAYPOINTS ===
    Route::put('/position/{type}/{id}', 'updatePosition')->name('position.update');
    Route::put('/route-waypoints/{type}/{id}', 'updateRouteWaypoints')->name('route-waypoints.update');

    // === DEVICE CRUD ===
    // OLT
    Route::get('/olts', 'getOLTs')->name('olts');
    Route::get('/olt/{id}', 'getOLT')->name('olt.show');
    Route::post('/olt', 'createOLT')->name('olt.create');
    Route::put('/olt/{id}', 'updateOLT')->name('olt.update');
    Route::delete('/olt/{id}', 'deleteOLT')->name('olt.delete');

    // ODC
    Route::get('/odcs', 'getODCs')->name('odcs');
    Route::get('/odc/{id}', 'getODC')->name('odc.show');
    Route::post('/odc', 'createODC')->name('odc.create');
    Route::put('/odc/{id}', 'updateODC')->name('odc.update');
    Route::delete('/odc/{id}', 'deleteODC')->name('odc.delete');

    // ODP
    Route::get('/odps', 'getODPs')->name('odps');
    Route::get('/odp/{id}', 'getODP')->name('odp.show');
    Route::post('/odp', 'createODP')->name('odp.create');
    Route::put('/odp/{id}', 'updateODP')->name('odp.update');
    Route::delete('/odp/{id}', 'deleteODP')->name('odp.delete');

    // Client
    Route::get('/clients', 'getClients')->name('clients');
    Route::get('/client/{id}', 'getClient')->name('client.show');
    Route::post('/client', 'createClient')->name('client.create');
    Route::put('/client/{id}', 'updateClient')->name('client.update');
    Route::delete('/client/{id}', 'deleteClient')->name('client.delete');

    // === IMPORT ===
    Route::get('/unmapped-items', 'getUnmappedItems')->name('unmapped-items');
    Route::post('/set-coordinates', 'setItemCoordinates')->name('set-coordinates');
});
