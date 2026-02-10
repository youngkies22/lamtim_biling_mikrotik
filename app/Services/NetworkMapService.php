<?php

namespace App\Services;

use App\Models\Lamtim_olt;
use App\Models\Lamtim_odc;
use App\Models\Lamtim_odp;
use App\Models\Lamtim_user_mikrotik_details;
use App\Models\Lamtim_area;
use App\Models\Lamtim_kategori;
use App\Models\Lamtim_paket;
use App\Models\Lamtim_mikrotik;

class NetworkMapService
{
    public function getMapData(): array
    {
        return [
            'server' => $this->getServerMarker(),
            'olts'   => $this->getOlts(),
            'odcs'   => $this->getOdcs(),
            'odps'   => $this->getOdps(),
            'clients' => $this->getClients(),
            'routes' => $this->getAutoRoutes(),
            'areas'  => $this->getAreas(),
        ];
    }

    public function getServerMarker(): array
    {
        return [
            'lat' => (float) config('services.mapping.latitude-server', -5.137373),
            'lng' => (float) config('services.mapping.longitude-server', 105.667153),
            'nama' => 'SERVER UTAMA',
            'type' => 'server',
        ];
    }

    public function getOlts(): array
    {
        return Lamtim_olt::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($olt) {
                return [
                    'id' => $olt->id,
                    'nama' => $olt->nama,
                    'kode' => $olt->kode,
                    'ip' => $olt->ip,
                    'teknologi' => $olt->teknologi,
                    'port_pon' => $olt->port_pon,
                    'port_uplink' => $olt->port_uplink,
                    'lat' => (float) $olt->latitude,
                    'lng' => (float) $olt->longitude,
                    'status' => $olt->status ?? 'active',
                    'keterangan' => $olt->keterangan,
                ];
            })->toArray();
    }

    public function getOdcs(): array
    {
        return Lamtim_odc::with('olt:id,nama,latitude,longitude')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($odc) {
                return [
                    'id' => $odc->id,
                    'nama' => $odc->nama,
                    'kode' => $odc->kode,
                    'port' => $odc->port ?? 0,
                    'portSisa' => $odc->portSisa ?? 0,
                    'portOlt' => $odc->portOlt,
                    'idOlt' => $odc->idOlt,
                    'olt_nama' => $odc->olt->nama ?? null,
                    'lat' => (float) $odc->latitude,
                    'lng' => (float) $odc->longitude,
                    'route_waypoints' => $odc->route_waypoints,
                    'status' => $odc->status ?? 'active',
                    'keterangan' => $odc->keterangan,
                ];
            })->toArray();
    }

    public function getOdps(): array
    {
        return Lamtim_odp::with(['odc:id,nama,latitude,longitude', 'parentOdp:id,nama,latitude,longitude'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($odp) {
                return [
                    'id' => $odp->id,
                    'nama' => $odp->nama,
                    'kode' => $odp->kode,
                    'port' => $odp->port ?? 0,
                    'portSisa' => $odp->portSisa ?? 0,
                    'portOdc' => $odp->portOdc,
                    'idOdc' => $odp->idOdc,
                    'odc_nama' => $odp->odc->nama ?? null,
                    'idOdp' => $odp->idOdp,
                    'odp_parent_nama' => $odp->parentOdp->nama ?? null,
                    'tipe' => $odp->tipe ?? 'HTB',
                    'fo_a' => $odp->fo_a,
                    'fo_b' => $odp->fo_b,
                    'kabel' => $odp->kabel,
                    'lat' => (float) $odp->latitude,
                    'lng' => (float) $odp->longitude,
                    'route_waypoints' => $odp->route_waypoints,
                    'status' => $odp->status ?? 'active',
                    'keterangan' => $odp->keterangan,
                ];
            })->toArray();
    }

    public function getClients(): array
    {
        return Lamtim_user_mikrotik_details::with(['user:id,name', 'odp:id,nama,latitude,longitude'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'nama' => $client->user->name ?? 'Unknown',
                    'idOdp' => $client->idOdp,
                    'odp_nama' => $client->odp->nama ?? null,
                    'ip' => $client->localAdress,
                    'status_client' => $client->status ?? 'offline',
                    'lat' => (float) $client->latitude,
                    'lng' => (float) $client->longitude,
                    'route_waypoints' => $client->route_waypoints,
                    'keterangan' => $client->keterangan ?? null,
                ];
            })->toArray();
    }

    public function getAreas(): array
    {
        return Lamtim_area::whereNotNull('coordinates')
            ->get()
            ->map(function ($area) {
                return [
                    'id' => $area->id,
                    'name' => $area->name,
                    'address' => $area->address,
                    'code_area' => $area->code_area,
                    'coordinates' => $area->coordinates,
                    'color' => $area->color ?? '#696cff',
                ];
            })->toArray();
    }

    public function getAutoRoutes(): array
    {
        $routes = [];

        // Server -> OLT routes
        $server = $this->getServerMarker();
        $olts = Lamtim_olt::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        foreach ($olts as $olt) {
            $routes[] = [
                'type' => 'server_to_olt',
                'from_id' => 0,
                'to_id' => $olt->id,
                'to_type' => 'olt',
                'color' => '#e91e63',
                'coords' => [
                    ['lat' => $server['lat'], 'lng' => $server['lng']],
                    ['lat' => (float) $olt->latitude, 'lng' => (float) $olt->longitude],
                ],
            ];
        }

        // OLT -> ODC routes
        $odcs = Lamtim_odc::with('olt')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNotNull('idOlt')
            ->get();

        foreach ($odcs as $odc) {
            if ($odc->olt && $odc->olt->latitude && $odc->olt->longitude) {
                $routes[] = [
                    'type' => 'olt_to_odc',
                    'from_id' => $odc->idOlt,
                    'to_id' => $odc->id,
                    'to_type' => 'odc',
                    'color' => '#ff9800',
                    'coords' => $this->buildRouteCoords(
                        $odc->olt->latitude, $odc->olt->longitude,
                        $odc->latitude, $odc->longitude,
                        $odc->route_waypoints
                    ),
                ];
            }
        }

        // ODC -> ODP routes (hanya ODP yang parent-nya ODC, bukan ODP)
        $odps = Lamtim_odp::with('odc')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNotNull('idOdc')
            ->whereNull('idOdp')
            ->get();

        foreach ($odps as $odp) {
            if ($odp->odc && $odp->odc->latitude && $odp->odc->longitude) {
                $routes[] = [
                    'type' => 'odc_to_odp',
                    'from_id' => $odp->idOdc,
                    'to_id' => $odp->id,
                    'to_type' => 'odp',
                    'color' => '#4caf50',
                    'coords' => $this->buildRouteCoords(
                        $odp->odc->latitude, $odp->odc->longitude,
                        $odp->latitude, $odp->longitude,
                        $odp->route_waypoints
                    ),
                ];
            }
        }

        // ODP -> ODP routes (estafet)
        $odpEstafet = Lamtim_odp::with('parentOdp')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNotNull('idOdp')
            ->get();

        foreach ($odpEstafet as $odp) {
            if ($odp->parentOdp && $odp->parentOdp->latitude && $odp->parentOdp->longitude) {
                $routes[] = [
                    'type' => 'odp_to_odp',
                    'from_id' => $odp->idOdp,
                    'to_id' => $odp->id,
                    'to_type' => 'odp',
                    'color' => '#4caf50',
                    'coords' => $this->buildRouteCoords(
                        $odp->parentOdp->latitude, $odp->parentOdp->longitude,
                        $odp->latitude, $odp->longitude,
                        $odp->route_waypoints
                    ),
                ];
            }
        }

        // ODP -> Client routes
        $clients = Lamtim_user_mikrotik_details::with('odp')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNotNull('idOdp')
            ->get();

        foreach ($clients as $client) {
            if ($client->odp && $client->odp->latitude && $client->odp->longitude) {
                $isOnline = ($client->status ?? 'offline') === 'online';
                $routes[] = [
                    'type' => 'odp_to_client',
                    'from_id' => $client->idOdp,
                    'to_id' => $client->id,
                    'to_type' => 'client',
                    'color' => $isOnline ? '#2196f3' : '#f44336',
                    'client_status' => $client->status ?? 'offline',
                    'coords' => $this->buildRouteCoords(
                        $client->odp->latitude, $client->odp->longitude,
                        $client->latitude, $client->longitude,
                        $client->route_waypoints
                    ),
                ];
            }
        }

        return $routes;
    }

    private function buildRouteCoords($fromLat, $fromLng, $toLat, $toLng, $waypoints): array
    {
        $coords = [['lat' => (float) $fromLat, 'lng' => (float) $fromLng]];

        if (!empty($waypoints) && is_array($waypoints)) {
            foreach ($waypoints as $wp) {
                $coords[] = ['lat' => (float) $wp['lat'], 'lng' => (float) $wp['lng']];
            }
        }

        $coords[] = ['lat' => (float) $toLat, 'lng' => (float) $toLng];
        return $coords;
    }

    public function getUnmappedItems(): array
    {
        $olts = Lamtim_olt::where(function ($q) {
            $q->whereNull('latitude')->orWhereNull('longitude');
        })->select('id', 'kode', 'nama', 'ip')->get();

        $odcs = Lamtim_odc::where(function ($q) {
            $q->whereNull('latitude')->orWhereNull('longitude');
        })->select('id', 'kode', 'nama', 'port')->get();

        $odps = Lamtim_odp::where(function ($q) {
            $q->whereNull('latitude')->orWhereNull('longitude');
        })->select('id', 'kode', 'nama', 'port', 'tipe')->get();

        $clients = Lamtim_user_mikrotik_details::with('user:id,name')
            ->where(function ($q) {
                $q->whereNull('latitude')->orWhereNull('longitude');
            })->get()->map(function ($c) {
                return [
                    'id' => $c->id,
                    'nama' => $c->user->name ?? 'User #' . $c->idUser,
                    'info' => $c->localAdress ?? '-',
                ];
            });

        return [
            'olts' => $olts,
            'odcs' => $odcs,
            'odps' => $odps,
            'clients' => $clients,
        ];
    }

    public function getSelectOptions(): array
    {
        $olts = Lamtim_olt::select('id', 'nama', 'kode')->get();
        $odcs = Lamtim_odc::select('id', 'nama', 'kode', 'port', 'portSisa')->get();
        $odps = Lamtim_odp::select('id', 'nama', 'kode', 'port', 'portSisa')->get();
        $kategoris = Lamtim_kategori::select('id', 'nama')->get();
        $pakets = Lamtim_paket::select('id', 'nama', 'kode', 'idKategori', 'price')->get();
        $mikrotiks = Lamtim_mikrotik::where('isActive', 1)->select('id', 'nama', 'kode', 'ip')->get();
        $areas = Lamtim_area::select('id', 'name', 'code_area')->get();

        return [
            'olts' => $olts,
            'odcs' => $odcs,
            'odps' => $odps,
            'kategoris' => $kategoris,
            'pakets' => $pakets,
            'mikrotiks' => $mikrotiks,
            'areas' => $areas,
        ];
    }
}
