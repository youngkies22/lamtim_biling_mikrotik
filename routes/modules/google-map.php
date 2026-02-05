<?php

use App\Http\Controllers\Master\GoogleMapController;

Route::prefix('google-map')->as('google-map.')->controller(GoogleMapController::class)->group(function () {
    // Views
    Route::get('/', 'index')->name('index');
    Route::get('/standalone', 'standalone')->name('standalone');

    // Data endpoints
    Route::get('/data', 'getMapData')->name('data');
    Route::get('/select-options', 'getSelectOptions')->name('select-options');

    // === MARKER ENDPOINTS ===
    Route::post('/marker', 'createMarker')->name('marker.create');
    Route::put('/marker/{id}/position', 'updateMarkerPosition')->name('marker.position');
    Route::put('/marker/{id}', 'updateMarker')->name('marker.update');
    Route::delete('/marker/{id}', 'deleteMarker')->name('marker.delete');
    Route::post('/marker/import', 'importMarkers')->name('marker.import');
    Route::get('/marker/available', 'getAvailableItems')->name('marker.available');
    Route::post('/marker/import-selected', 'importSelected')->name('marker.import-selected');
    Route::delete('/marker/clear/all', 'clearMarkers')->name('marker.clear');

    // === POLYLINE ENDPOINTS ===
    Route::post('/polyline', 'savePolyline')->name('polyline.save');
    Route::put('/polyline/{id}', 'updatePolyline')->name('polyline.update');
    Route::delete('/polyline/{id}', 'deletePolyline')->name('polyline.delete');
    Route::delete('/polyline/clear/all', 'clearPolylines')->name('polyline.clear');

    // Legacy route endpoints (backwards compatible)
    Route::post('/route', 'saveRoute')->name('route.save');
    Route::delete('/route/{id}', 'deleteRoute')->name('route.delete');
});
