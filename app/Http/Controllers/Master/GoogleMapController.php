<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\GoogleMapService;
use App\Services\GoogleMapPolylineService;
use App\Services\GoogleMapMarkerService;
use Illuminate\Http\Request;

class GoogleMapController extends Controller
{
    protected GoogleMapService $googleMapService;
    protected GoogleMapPolylineService $polylineService;
    protected GoogleMapMarkerService $markerService;

    public function __construct(
        GoogleMapService $googleMapService,
        GoogleMapPolylineService $polylineService,
        GoogleMapMarkerService $markerService
    ) {
        $this->googleMapService = $googleMapService;
        $this->polylineService = $polylineService;
        $this->markerService = $markerService;
    }

    /**
     * Display Google Map page
     */
    public function index()
    {
        $mapCenter = $this->googleMapService->getMapCenter();
        return view('content.google-map.index', compact('mapCenter'));
    }

    /**
     * Display standalone Google Map page (full-featured)
     */
    public function standalone()
    {
        $mapCenter = $this->googleMapService->getMapCenter();
        $apiKey = config('services.google.maps_api_key');
        $selectOptions = $this->markerService->getSelectOptions();

        return view('content.google-map.standalone', compact('mapCenter', 'apiKey', 'selectOptions'));
    }

    /**
     * Get all map data as JSON (from markers table)
     */
    public function getMapData()
    {
        $markers = $this->markerService->getAll();
        $polylines = $this->polylineService->getAll();
        $statistics = $this->markerService->getStatistics();
        $serverMarker = $this->googleMapService->getServerMarker();

        return response()->json([
            'status' => true,
            'data' => [
                'markers' => $markers,
                'polylines' => $polylines,
                'statistics' => $statistics,
                'center' => $this->googleMapService->getMapCenter(),
                'server' => $serverMarker,
            ]
        ]);
    }

    /**
     * Get select options for dropdowns
     */
    public function getSelectOptions()
    {
        $options = $this->markerService->getSelectOptions();

        return response()->json([
            'status' => true,
            'data' => $options
        ]);
    }

    // === MARKER ENDPOINTS ===

    /**
     * Create a new marker
     */
    public function createMarker(Request $request)
    {
        $validated = $request->validate([
            'tipe' => 'required|string|in:odc,odp,user,custom',
            'ref_id' => 'nullable|integer',
            'nama' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'icon' => 'nullable|string|max:100',
            'warna' => 'nullable|string|max:20',
            'extra_data' => 'nullable|array',
        ]);

        try {
            $marker = $this->markerService->create($validated);

            return response()->json([
                'status' => true,
                'message' => 'Marker berhasil dibuat',
                'data' => $marker->toMapData()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat marker: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update marker position (drag)
     */
    public function updateMarkerPosition(Request $request, $id)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $marker = $this->markerService->updatePosition($id, $validated['latitude'], $validated['longitude']);

        if ($marker) {
            return response()->json([
                'status' => true,
                'message' => 'Posisi marker berhasil diperbarui',
                'data' => $marker->toMapData()
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Marker tidak ditemukan'
        ], 404);
    }

    /**
     * Update marker data
     */
    public function updateMarker(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'icon' => 'nullable|string|max:100',
            'warna' => 'nullable|string|max:20',
            'extra_data' => 'nullable|array',
            'is_visible' => 'nullable|boolean',
        ]);

        $marker = $this->markerService->update($id, $validated);

        if ($marker) {
            return response()->json([
                'status' => true,
                'message' => 'Marker berhasil diperbarui',
                'data' => $marker->toMapData()
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Marker tidak ditemukan'
        ], 404);
    }

    /**
     * Delete marker
     */
    public function deleteMarker($id)
    {
        $deleted = $this->markerService->delete($id);

        if ($deleted) {
            return response()->json([
                'status' => true,
                'message' => 'Marker berhasil dihapus'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Marker tidak ditemukan'
        ], 404);
    }

    /**
     * Import markers from existing database (bulk all)
     */
    public function importMarkers()
    {
        $result = $this->markerService->importAll();

        if ($result['success']) {
            return response()->json([
                'status' => true,
                'message' => 'Markers berhasil di-import',
                'data' => $result['imported']
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Gagal import markers: ' . ($result['error'] ?? 'Unknown error')
        ], 500);
    }

    /**
     * Get available (unmapped) items from database
     */
    public function getAvailableItems()
    {
        $items = $this->markerService->getAvailableItems();

        return response()->json([
            'status' => true,
            'data' => $items
        ]);
    }

    /**
     * Import selected markers by IDs
     */
    public function importSelected(Request $request)
    {
        $validated = $request->validate([
            'odc_ids' => 'nullable|array',
            'odc_ids.*' => 'integer',
            'odp_ids' => 'nullable|array',
            'odp_ids.*' => 'integer',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer',
        ]);

        $result = $this->markerService->importSelected($validated);

        if ($result['success']) {
            return response()->json([
                'status' => true,
                'message' => 'Markers berhasil di-import',
                'data' => $result['imported']
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Gagal import markers: ' . ($result['error'] ?? 'Unknown error')
        ], 500);
    }

    /**
     * Clear all markers
     */
    public function clearMarkers()
    {
        $count = $this->markerService->clearAll();

        return response()->json([
            'status' => true,
            'message' => "Berhasil menghapus {$count} markers"
        ]);
    }

    // === POLYLINE ENDPOINTS ===

    /**
     * Save polyline
     */
    public function savePolyline(Request $request)
    {
        $validated = $request->validate([
            'tipe' => 'required|string|in:odc_to_odp,odp_to_odp,odp_to_user,custom',
            'nama' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'warna' => 'nullable|string|max:20',
            'ketebalan' => 'nullable|integer|min:1|max:10',
            'animasi' => 'nullable|boolean',
            // Marker IDs (from lamtim_google_map_markers table)
            'marker_from_id' => 'nullable|integer',
            'marker_to_id' => 'nullable|integer',
            // Legacy relation IDs (optional)
            'id_odc_from' => 'nullable|integer',
            'id_odp_to' => 'nullable|integer',
            'id_odp_from' => 'nullable|integer',
            'id_user' => 'nullable|integer',
            // Koordinat
            'koordinat' => 'required|array|min:2',
            'koordinat.*.lat' => 'required|numeric',
            'koordinat.*.lng' => 'required|numeric',
        ]);

        try {
            $polyline = $this->polylineService->save($validated);

            return response()->json([
                'status' => true,
                'message' => 'Polyline berhasil disimpan',
                'data' => [
                    'id' => $polyline->id,
                    'tipe' => $polyline->tipe,
                    'koordinat' => $polyline->getResolvedCoordinates(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menyimpan polyline: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update polyline coordinates
     */
    public function updatePolyline(Request $request, $id)
    {
        $validated = $request->validate([
            'koordinat' => 'required|array|min:2',
            'koordinat.*.lat' => 'required|numeric',
            'koordinat.*.lng' => 'required|numeric',
            'warna' => 'nullable|string|max:20',
            'ketebalan' => 'nullable|integer|min:1|max:10',
        ]);

        $polyline = $this->polylineService->update($id, $validated);

        if ($polyline) {
            return response()->json([
                'status' => true,
                'message' => 'Polyline berhasil diperbarui',
                'data' => [
                    'id' => $polyline->id,
                    'koordinat' => $polyline->getResolvedCoordinates(),
                ]
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Polyline tidak ditemukan'
        ], 404);
    }

    /**
     * Delete polyline
     */
    public function deletePolyline($id)
    {
        $deleted = $this->polylineService->delete($id);

        if ($deleted) {
            return response()->json([
                'status' => true,
                'message' => 'Polyline berhasil dihapus'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Polyline tidak ditemukan'
        ], 404);
    }

    /**
     * Clear all polylines
     */
    public function clearPolylines()
    {
        $this->polylineService->clearAll();

        return response()->json([
            'status' => true,
            'message' => 'Semua polyline berhasil dihapus'
        ]);
    }

    // === LEGACY ENDPOINTS ===

    public function saveRoute(Request $request)
    {
        $request->validate([
            'coordinates' => 'required|array|min:2',
            'coordinates.*.lat' => 'required|numeric',
            'coordinates.*.lng' => 'required|numeric',
        ]);

        $polyline = $this->polylineService->createCustom($request->coordinates, [
            'nama' => $request->name,
            'warna' => $request->color ?? '#3388ff',
            'ketebalan' => $request->weight ?? 3,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Route berhasil disimpan',
            'data' => $polyline
        ]);
    }

    public function deleteRoute($id)
    {
        return $this->deletePolyline($id);
    }
}
