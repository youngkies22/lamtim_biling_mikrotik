<?php

namespace App\Services;

use App\Models\Lamtim_odc;
use App\Models\Lamtim_odp;
use App\Models\Lamtim_map_route;
use App\Models\Lamtim_user_mikrotik_details;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GoogleMapService
{
    protected GoogleMapPolylineService $polylineService;

    public function __construct(GoogleMapPolylineService $polylineService)
    {
        $this->polylineService = $polylineService;
    }

    /**
     * Get map center coordinates from config
     */
    public function getMapCenter(): array
    {
        return [
            'latitude' => config('services.mapping.latitude-server'),
            'longitude' => config('services.mapping.longitude-server')
        ];
    }

    /**
     * Get server marker data
     */
    public function getServerMarker(): array
    {
        return [
            'id' => 'server',
            'tipe' => 'server',
            'nama' => 'Server Utama',
            'latitude' => (float) config('services.mapping.latitude-server'),
            'longitude' => (float) config('services.mapping.longitude-server'),
            'icon' => 'server',
            'warna' => '#e91e63',
            'extra_data' => [
                'info' => 'Titik Pusat Server'
            ]
        ];
    }

    /**
     * Get all ODC data for map with user counts (optimized - single query)
     */
    public function getOdcData(): array
    {
        // Use subquery for counting to avoid N+1
        $odcs = Lamtim_odc::select([
            'lamtim_odcs.id',
            'lamtim_odcs.nama',
            'lamtim_odcs.latitude',
            'lamtim_odcs.longitude',
            'lamtim_odcs.port',
            'lamtim_odcs.portSisa',
            'lamtim_odcs.portOlt',
            DB::raw('(SELECT COUNT(*) FROM lamtim_odps WHERE lamtim_odps.idOdc = lamtim_odcs.id) as total_odp'),
            DB::raw('(SELECT COUNT(*) FROM lamtim_user_mikrotik_details WHERE lamtim_user_mikrotik_details.idOdc = lamtim_odcs.id) as total_user'),
        ])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return $odcs->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'latitude' => (float) $item->latitude,
                'longitude' => (float) $item->longitude,
                'port' => $item->port,
                'portSisa' => $item->portSisa,
                'portOlt' => $item->portOlt,
                'totalOdp' => $item->total_odp,
                'totalUser' => $item->total_user,
                'type' => 'ODC',
            ];
        })->toArray();
    }

    /**
     * Get all ODP data for map with user counts (optimized - single query with eager load)
     */
    public function getOdpData(): array
    {
        $odps = Lamtim_odp::select([
            'lamtim_odps.id',
            'lamtim_odps.nama',
            'lamtim_odps.latitude',
            'lamtim_odps.longitude',
            'lamtim_odps.idOdc',
            'lamtim_odps.idOdp',
            'lamtim_odps.port',
            'lamtim_odps.portSisa',
            'lamtim_odps.portOdc',
            DB::raw('(SELECT COUNT(*) FROM lamtim_user_mikrotik_details WHERE lamtim_user_mikrotik_details.idOdp = lamtim_odps.id) as total_user'),
        ])
            ->with(['odc:id,nama', 'parentOdp:id,nama'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return $odps->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'latitude' => (float) $item->latitude,
                'longitude' => (float) $item->longitude,
                'idOdc' => $item->idOdc,
                'idOdp' => $item->idOdp,
                'odcNama' => $item->odc->nama ?? null,
                'parentOdpNama' => $item->parentOdp->nama ?? null,
                'port' => $item->port,
                'portSisa' => $item->portSisa,
                'portOdc' => $item->portOdc,
                'totalUser' => $item->total_user,
                'type' => 'ODP',
            ];
        })->toArray();
    }

    /**
     * Get all User data with location for map (optimized - single query with joins)
     */
    public function getUserData(): array
    {
        $users = Lamtim_user_mikrotik_details::select([
            'lamtim_user_mikrotik_details.id',
            'lamtim_user_mikrotik_details.idUser',
            'lamtim_user_mikrotik_details.idOdp',
            'lamtim_user_mikrotik_details.idOdc',
            'lamtim_user_mikrotik_details.latitude',
            'lamtim_user_mikrotik_details.longitude',
            'users.name',
            'users.julukan',
            'lamtim_pakets.nama as paket_nama',
            'lamtim_kategoris.nama as kategori_nama',
            'lamtim_odps.nama as odp_nama',
        ])
            ->join('users', 'users.id', '=', 'lamtim_user_mikrotik_details.idUser')
            ->leftJoin('lamtim_pakets', 'lamtim_pakets.id', '=', 'lamtim_user_mikrotik_details.idPaket')
            ->leftJoin('lamtim_kategoris', 'lamtim_kategoris.id', '=', 'lamtim_user_mikrotik_details.idKategori')
            ->leftJoin('lamtim_odps', 'lamtim_odps.id', '=', 'lamtim_user_mikrotik_details.idOdp')
            ->whereNotNull('lamtim_user_mikrotik_details.latitude')
            ->whereNotNull('lamtim_user_mikrotik_details.longitude')
            ->get();

        return $users->map(function ($item) {
            return [
                'id' => $item->idUser,
                'nama' => $item->name,
                'julukan' => $item->julukan,
                'latitude' => (float) $item->latitude,
                'longitude' => (float) $item->longitude,
                'idOdp' => $item->idOdp,
                'idOdc' => $item->idOdc,
                'odpNama' => $item->odp_nama,
                'paket' => $item->paket_nama,
                'kategori' => $item->kategori_nama,
                'type' => 'USER',
            ];
        })->toArray();
    }

    /**
     * Get all polylines (new system)
     */
    public function getPolylines(): array
    {
        return $this->polylineService->getAll();
    }

    /**
     * Get legacy map routes (old system - for backwards compatibility)
     */
    public function getLegacyMapRoutes(): array
    {
        return Lamtim_map_route::select('id', 'name', 'description', 'color', 'weight', 'coordinates', 'type')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($route) {
                return [
                    'id' => $route->id,
                    'name' => $route->name,
                    'description' => $route->description,
                    'color' => $route->color,
                    'weight' => $route->weight,
                    'coordinates' => $route->coordinates,
                    'type' => $route->type,
                    'legacy' => true,
                ];
            })->toArray();
    }

    /**
     * Get statistics for the map
     */
    public function getStatistics(): array
    {
        return [
            'total_odc' => Lamtim_odc::whereNotNull('latitude')->whereNotNull('longitude')->count(),
            'total_odp' => Lamtim_odp::whereNotNull('latitude')->whereNotNull('longitude')->count(),
            'total_user' => Lamtim_user_mikrotik_details::whereNotNull('latitude')->whereNotNull('longitude')->count(),
            'total_polylines' => \App\Models\Lamtim_google_map_polyline::active()->count(),
        ];
    }

    /**
     * Get all map data (ODC, ODP, User, Polylines)
     */
    public function getAllMapData(): array
    {
        return [
            'odcs' => $this->getOdcData(),
            'odps' => $this->getOdpData(),
            'users' => $this->getUserData(),
            'polylines' => $this->getPolylines(),
            'legacyRoutes' => $this->getLegacyMapRoutes(),
            'center' => $this->getMapCenter(),
            'statistics' => $this->getStatistics(),
        ];
    }

    /**
     * Get select options for dropdowns
     */
    public function getSelectOptions(): array
    {
        $odcs = Lamtim_odc::select('id', 'nama')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('nama')
            ->get();

        $odps = Lamtim_odp::select('id', 'nama', 'idOdc')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('nama')
            ->get();

        $users = User::select('users.id', 'users.name')
            ->join('lamtim_user_mikrotik_details', 'users.id', '=', 'lamtim_user_mikrotik_details.idUser')
            ->whereNotNull('lamtim_user_mikrotik_details.latitude')
            ->whereNotNull('lamtim_user_mikrotik_details.longitude')
            ->orderBy('users.name')
            ->get();

        return [
            'odcs' => $odcs->toArray(),
            'odps' => $odps->toArray(),
            'users' => $users->toArray(),
        ];
    }
}
