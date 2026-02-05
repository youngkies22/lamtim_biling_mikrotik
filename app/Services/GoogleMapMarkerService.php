<?php

namespace App\Services;

use App\Models\Lamtim_google_map_marker;
use App\Models\Lamtim_odc;
use App\Models\Lamtim_odp;
use App\Models\Lamtim_user_mikrotik_details;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GoogleMapMarkerService
{
    /**
     * Get all visible markers
     * Always fetch fresh portSisa from source tables (ODC/ODP) to avoid stale cache
     */
    public function getAll(): array
    {
        $markers = Lamtim_google_map_marker::visible()
            ->orderBy('tipe')
            ->orderBy('nama')
            ->get();

        // Get all ODC and ODP IDs to batch load
        $odcIds = $markers->where('tipe', 'odc')->pluck('ref_id')->filter()->unique()->toArray();
        $odpIds = $markers->where('tipe', 'odp')->pluck('ref_id')->filter()->unique()->toArray();
        
        // Batch load ODC and ODP data
        $odcs = Lamtim_odc::whereIn('id', $odcIds)->get()->keyBy('id');
        $odps = Lamtim_odp::whereIn('id', $odpIds)->get()->keyBy('id');

        return $markers->map(function($m) use ($odcs, $odps) {
            $data = $m->toMapData();
            
            // Update portSisa from source table (not from cached extra_data)
            if ($m->ref_id) {
                if ($m->tipe === 'odc' && isset($odcs[$m->ref_id])) {
                    $odc = $odcs[$m->ref_id];
                    if (isset($data['extra_data'])) {
                        $extraData = $data['extra_data'];
                        $extraData['portSisa'] = $odc->portSisa;
                        $extraData['port'] = $odc->port;
                        $extraData['portOlt'] = $odc->portOlt;
                        $data['extra_data'] = $extraData;
                        // Update the marker's extra_data in database
                        $m->update(['extra_data' => $extraData]);
                    }
                } elseif ($m->tipe === 'odp' && isset($odps[$m->ref_id])) {
                    $odp = $odps[$m->ref_id];
                    if (isset($data['extra_data'])) {
                        $extraData = $data['extra_data'];
                        $extraData['portSisa'] = $odp->portSisa;
                        $extraData['port'] = $odp->port;
                        $extraData['portOdc'] = $odp->portOdc;
                        $data['extra_data'] = $extraData;
                        // Update the marker's extra_data in database
                        $m->update(['extra_data' => $extraData]);
                    }
                }
            }
            
            return $data;
        })->toArray();
    }

    /**
     * Get markers by type
     */
    public function getByTipe(string $tipe): array
    {
        $markers = Lamtim_google_map_marker::visible()
            ->byTipe($tipe)
            ->orderBy('nama')
            ->get();

        return $markers->map(fn($m) => $m->toMapData())->toArray();
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        return [
            'odc' => Lamtim_google_map_marker::visible()->odc()->count(),
            'odp' => Lamtim_google_map_marker::visible()->odp()->count(),
            'user' => Lamtim_google_map_marker::visible()->user()->count(),
            'total' => Lamtim_google_map_marker::visible()->count(),
        ];
    }

    /**
     * Create a new marker
     */
    public function create(array $data): Lamtim_google_map_marker
    {
        return Lamtim_google_map_marker::create([
            'tipe' => $data['tipe'],
            'ref_id' => $data['ref_id'] ?? null,
            'nama' => $data['nama'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'icon' => $data['icon'] ?? null,
            'warna' => $data['warna'] ?? $this->getDefaultColor($data['tipe']),
            'extra_data' => $data['extra_data'] ?? null,
            'is_visible' => $data['is_visible'] ?? true,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Update marker position (for drag)
     */
    public function updatePosition(int $id, float $latitude, float $longitude): ?Lamtim_google_map_marker
    {
        $marker = Lamtim_google_map_marker::find($id);
        if (!$marker) {
            return null;
        }

        $marker->update([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'updated_by' => Auth::id(),
        ]);

        return $marker->fresh();
    }

    /**
     * Update marker data
     */
    public function update(int $id, array $data): ?Lamtim_google_map_marker
    {
        $marker = Lamtim_google_map_marker::find($id);
        if (!$marker) {
            return null;
        }

        $updateData = array_filter([
            'nama' => $data['nama'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'icon' => $data['icon'] ?? null,
            'warna' => $data['warna'] ?? null,
            'extra_data' => $data['extra_data'] ?? null,
            'is_visible' => $data['is_visible'] ?? null,
        ], fn($v) => $v !== null);

        $updateData['updated_by'] = Auth::id();

        $marker->update($updateData);
        return $marker->fresh();
    }

    /**
     * Delete marker
     */
    public function delete(int $id): bool
    {
        $marker = Lamtim_google_map_marker::find($id);
        if (!$marker) {
            return false;
        }

        return $marker->delete();
    }

    /**
     * Import markers from existing ODC data
     */
    public function importFromOdc(): int
    {
        $imported = 0;

        $odcs = Lamtim_odc::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        foreach ($odcs as $odc) {
            // Check if already imported
            $exists = Lamtim_google_map_marker::where('tipe', 'odc')
                ->where('ref_id', $odc->id)
                ->exists();

            if (!$exists) {
                // Count total ODP and User for this ODC
                $totalOdp = Lamtim_odp::where('idOdc', $odc->id)->count();
                $totalUser = Lamtim_user_mikrotik_details::where('idOdc', $odc->id)->count();

                $this->create([
                    'tipe' => 'odc',
                    'ref_id' => $odc->id,
                    'nama' => $odc->nama,
                    'latitude' => $odc->latitude,
                    'longitude' => $odc->longitude,
                    'extra_data' => [
                        'port' => $odc->port,
                        'portSisa' => $odc->portSisa,
                        'portOlt' => $odc->portOlt,
                        'totalOdp' => $totalOdp,
                        'totalUser' => $totalUser,
                    ],
                ]);
                $imported++;
            }
        }

        return $imported;
    }

    /**
     * Import markers from existing ODP data
     */
    public function importFromOdp(): int
    {
        $imported = 0;

        $odps = Lamtim_odp::with('odc:id,nama')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        foreach ($odps as $odp) {
            $exists = Lamtim_google_map_marker::where('tipe', 'odp')
                ->where('ref_id', $odp->id)
                ->exists();

            if (!$exists) {
                // Count total User for this ODP
                $totalUser = Lamtim_user_mikrotik_details::where('idOdp', $odp->id)->count();

                $this->create([
                    'tipe' => 'odp',
                    'ref_id' => $odp->id,
                    'nama' => $odp->nama,
                    'latitude' => $odp->latitude,
                    'longitude' => $odp->longitude,
                    'extra_data' => [
                        'port' => $odp->port,
                        'portSisa' => $odp->portSisa,
                        'portOdc' => $odp->portOdc,
                        'idOdc' => $odp->idOdc,
                        'odcNama' => $odp->odc->nama ?? null,
                        'totalUser' => $totalUser,
                    ],
                ]);
                $imported++;
            }
        }

        return $imported;
    }

    /**
     * Import markers from existing User data
     */
    public function importFromUsers(): int
    {
        $imported = 0;

        $users = Lamtim_user_mikrotik_details::with(['user:id,name', 'odp:id,nama', 'paket:id,nama', 'kategori:id,nama'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        foreach ($users as $detail) {
            $exists = Lamtim_google_map_marker::where('tipe', 'user')
                ->where('ref_id', $detail->idUser)
                ->exists();

            if (!$exists) {
                $this->create([
                    'tipe' => 'user',
                    'ref_id' => $detail->idUser,
                    'nama' => $detail->user->name ?? 'User #' . $detail->idUser,
                    'latitude' => $detail->latitude,
                    'longitude' => $detail->longitude,
                    'extra_data' => [
                        'idUser' => $detail->idUser,
                        'idOdp' => $detail->idOdp,
                        'odpNama' => $detail->odp->nama ?? null,
                        'kategori' => $detail->kategori->nama ?? null,
                        'paket' => $detail->paket->nama ?? null,
                    ],
                ]);
                $imported++;
            }
        }

        return $imported;
    }

    /**
     * Import all markers from existing data
     */
    public function importAll(): array
    {
        DB::beginTransaction();
        try {
            $result = [
                'odc' => $this->importFromOdc(),
                'odp' => $this->importFromOdp(),
                'user' => $this->importFromUsers(),
            ];
            $result['total'] = $result['odc'] + $result['odp'] + $result['user'];

            DB::commit();
            return ['success' => true, 'imported' => $result];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to import markers: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Clear all markers
     */
    public function clearAll(): int
    {
        $count = Lamtim_google_map_marker::count();
        Lamtim_google_map_marker::truncate();
        return $count;
    }

    /**
     * Clear markers by type
     */
    public function clearByTipe(string $tipe): int
    {
        return Lamtim_google_map_marker::where('tipe', $tipe)->delete();
    }

    /**
     * Get default color by type
     */
    private function getDefaultColor(string $tipe): string
    {
        return match ($tipe) {
            'odc' => '#ff9800',
            'odp' => '#4caf50',
            'user' => '#2196f3',
            default => '#9c27b0',
        };
    }

    /**
     * Get select options for dropdowns (from markers table)
     */
    public function getSelectOptions(): array
    {
        return [
            'odcs' => Lamtim_google_map_marker::visible()
                ->odc()
                ->select('id', 'ref_id', 'nama', 'latitude', 'longitude')
                ->orderBy('nama')
                ->get()
                ->toArray(),
            'odps' => Lamtim_google_map_marker::visible()
                ->odp()
                ->select('id', 'ref_id', 'nama', 'latitude', 'longitude')
                ->orderBy('nama')
                ->get()
                ->toArray(),
            'users' => Lamtim_google_map_marker::visible()
                ->user()
                ->select('id', 'ref_id', 'nama', 'latitude', 'longitude')
                ->orderBy('nama')
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Get available (unmapped) items from database
     */
    public function getAvailableItems(): array
    {
        // Get already mapped ref_ids
        $mappedOdcIds = Lamtim_google_map_marker::where('tipe', 'odc')->pluck('ref_id')->toArray();
        $mappedOdpIds = Lamtim_google_map_marker::where('tipe', 'odp')->pluck('ref_id')->toArray();
        $mappedUserIds = Lamtim_google_map_marker::where('tipe', 'user')->pluck('ref_id')->toArray();

        // Get unmapped ODCs
        $odcs = Lamtim_odc::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNotIn('id', $mappedOdcIds)
            ->select('id', 'nama', 'latitude', 'longitude', 'port', 'portSisa')
            ->orderBy('nama')
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'nama' => $o->nama,
                'latitude' => $o->latitude,
                'longitude' => $o->longitude,
                'info' => "Port: {$o->port}, Sisa: {$o->portSisa}"
            ]);

        // Get unmapped ODPs
        $odps = Lamtim_odp::with('odc:id,nama')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNotIn('id', $mappedOdpIds)
            ->select('id', 'nama', 'latitude', 'longitude', 'port', 'portSisa', 'idOdc')
            ->orderBy('nama')
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'nama' => $o->nama,
                'latitude' => $o->latitude,
                'longitude' => $o->longitude,
                'info' => "Port: {$o->port}, ODC: " . ($o->odc->nama ?? '-')
            ]);

        // Get unmapped Users
        $users = Lamtim_user_mikrotik_details::with(['user:id,name', 'odp:id,nama'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNotIn('idUser', $mappedUserIds)
            ->get()
            ->map(fn($u) => [
                'id' => $u->idUser,
                'nama' => $u->user->name ?? 'User #' . $u->idUser,
                'latitude' => $u->latitude,
                'longitude' => $u->longitude,
                'info' => "ODP: " . ($u->odp->nama ?? '-')
            ]);

        return [
            'odcs' => $odcs->toArray(),
            'odps' => $odps->toArray(),
            'users' => $users->toArray(),
        ];
    }

    /**
     * Import selected items by IDs
     * Jika koordinat jauh dari server (> 0.1 degree ~ 11km), tempatkan di dekat server
     */
    public function importSelected(array $data): array
    {
        $imported = ['odc' => 0, 'odp' => 0, 'user' => 0];

        // Get server coordinates
        $serverLat = (float) config('services.mapping.latitude-server');
        $serverLng = (float) config('services.mapping.longitude-server');
        $maxDistance = 0.05; // ~5.5km radius dari server

        DB::beginTransaction();
        try {
            // Import selected ODCs
            if (!empty($data['odc_ids'])) {
                $odcs = Lamtim_odc::whereIn('id', $data['odc_ids'])->get();

                foreach ($odcs as $idx => $odc) {
                    $exists = Lamtim_google_map_marker::where('tipe', 'odc')
                        ->where('ref_id', $odc->id)
                        ->exists();

                    if (!$exists) {
                        // Check if coordinates are valid and near server
                        $lat = $odc->latitude;
                        $lng = $odc->longitude;

                        if (!$lat || !$lng || $this->isCoordinateFar($lat, $lng, $serverLat, $serverLng, $maxDistance)) {
                            // Place near server with slight offset
                            $lat = $serverLat + (0.002 * ($idx + 1));
                            $lng = $serverLng + (0.002 * ($idx + 1));
                        }

                        // Count total ODP and User for this ODC
                        $totalOdp = Lamtim_odp::where('idOdc', $odc->id)->count();
                        $totalUser = Lamtim_user_mikrotik_details::where('idOdc', $odc->id)->count();

                        $this->create([
                            'tipe' => 'odc',
                            'ref_id' => $odc->id,
                            'nama' => $odc->nama,
                            'latitude' => $lat,
                            'longitude' => $lng,
                            'extra_data' => [
                                'port' => $odc->port,
                                'portSisa' => $odc->portSisa,
                                'portOlt' => $odc->portOlt,
                                'totalOdp' => $totalOdp,
                                'totalUser' => $totalUser,
                            ],
                        ]);
                        $imported['odc']++;
                    }
                }
            }

            // Import selected ODPs
            if (!empty($data['odp_ids'])) {
                $odps = Lamtim_odp::with('odc:id,nama')
                    ->whereIn('id', $data['odp_ids'])
                    ->get();

                foreach ($odps as $idx => $odp) {
                    $exists = Lamtim_google_map_marker::where('tipe', 'odp')
                        ->where('ref_id', $odp->id)
                        ->exists();

                    if (!$exists) {
                        $lat = $odp->latitude;
                        $lng = $odp->longitude;

                        if (!$lat || !$lng || $this->isCoordinateFar($lat, $lng, $serverLat, $serverLng, $maxDistance)) {
                            $lat = $serverLat + (0.001 * ($idx + 1)) + 0.003;
                            $lng = $serverLng + (0.001 * ($idx + 1)) + 0.003;
                        }

                        // Count total User for this ODP
                        $totalUser = Lamtim_user_mikrotik_details::where('idOdp', $odp->id)->count();

                        $this->create([
                            'tipe' => 'odp',
                            'ref_id' => $odp->id,
                            'nama' => $odp->nama,
                            'latitude' => $lat,
                            'longitude' => $lng,
                            'extra_data' => [
                                'port' => $odp->port,
                                'portSisa' => $odp->portSisa,
                                'portOdc' => $odp->portOdc,
                                'idOdc' => $odp->idOdc,
                                'odcNama' => $odp->odc->nama ?? null,
                                'totalUser' => $totalUser,
                            ],
                        ]);
                        $imported['odp']++;
                    }
                }
            }

            // Import selected Users
            if (!empty($data['user_ids'])) {
                $users = Lamtim_user_mikrotik_details::with(['user:id,name', 'odp:id,nama', 'paket:id,nama', 'kategori:id,nama'])
                    ->whereIn('idUser', $data['user_ids'])
                    ->get();

                foreach ($users as $idx => $detail) {
                    $exists = Lamtim_google_map_marker::where('tipe', 'user')
                        ->where('ref_id', $detail->idUser)
                        ->exists();

                    if (!$exists) {
                        $lat = $detail->latitude;
                        $lng = $detail->longitude;

                        if (!$lat || !$lng || $this->isCoordinateFar($lat, $lng, $serverLat, $serverLng, $maxDistance)) {
                            $lat = $serverLat + (0.0005 * ($idx + 1)) + 0.005;
                            $lng = $serverLng + (0.0005 * ($idx + 1)) + 0.005;
                        }

                        $this->create([
                            'tipe' => 'user',
                            'ref_id' => $detail->idUser,
                            'nama' => $detail->user->name ?? 'User #' . $detail->idUser,
                            'latitude' => $lat,
                            'longitude' => $lng,
                            'extra_data' => [
                                'idUser' => $detail->idUser,
                                'idOdp' => $detail->idOdp,
                                'odpNama' => $detail->odp->nama ?? null,
                                'kategori' => $detail->kategori->nama ?? null,
                                'paket' => $detail->paket->nama ?? null,
                            ],
                        ]);
                        $imported['user']++;
                    }
                }
            }

            DB::commit();
            $imported['total'] = $imported['odc'] + $imported['odp'] + $imported['user'];
            return ['success' => true, 'imported' => $imported];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to import selected markers: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if coordinate is far from reference point
     */
    private function isCoordinateFar(float $lat, float $lng, float $refLat, float $refLng, float $maxDistance): bool
    {
        $distance = sqrt(pow($lat - $refLat, 2) + pow($lng - $refLng, 2));
        return $distance > $maxDistance;
    }
}
