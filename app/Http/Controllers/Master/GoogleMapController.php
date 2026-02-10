<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\NetworkMapService;
use App\Models\Lamtim_olt;
use App\Models\Lamtim_odc;
use App\Models\Lamtim_odp;
use App\Models\Lamtim_user_mikrotik_details;
use App\Models\Lamtim_user_details;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoogleMapController extends Controller
{
    protected NetworkMapService $networkMapService;

    public function __construct(NetworkMapService $networkMapService)
    {
        $this->networkMapService = $networkMapService;
    }

    public function index()
    {
        return redirect()->route('google-map.standalone');
    }

    public function standalone()
    {
        return view('content.google-map.standalone');
    }

    // ==================== DATA ENDPOINTS ====================

    public function getMapData()
    {
        $data = $this->networkMapService->getMapData();
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function getSelectOptions()
    {
        $options = $this->networkMapService->getSelectOptions();
        return response()->json(['success' => true, 'data' => $options]);
    }

    // ==================== POSITION & WAYPOINTS ====================

    public function updatePosition(Request $request, $type, $id)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $model = $this->resolveModel($type);
        $device = $model::findOrFail($id);
        $device->latitude = $request->latitude;
        $device->longitude = $request->longitude;
        $device->save();

        return response()->json(['success' => true, 'message' => 'Posisi berhasil diperbarui']);
    }

    public function updateRouteWaypoints(Request $request, $type, $id)
    {
        $request->validate([
            'route_waypoints' => 'nullable|array',
            'route_waypoints.*.lat' => 'required|numeric',
            'route_waypoints.*.lng' => 'required|numeric',
        ]);

        if (!in_array($type, ['odc', 'odp', 'client'])) {
            return response()->json(['success' => false, 'message' => 'Tipe tidak valid'], 422);
        }

        $model = $this->resolveModel($type);
        $device = $model::findOrFail($id);
        $device->route_waypoints = $request->route_waypoints;
        $device->save();

        return response()->json(['success' => true, 'message' => 'Waypoints berhasil disimpan']);
    }

    // ==================== OLT CRUD ====================

    public function getOLTs()
    {
        $olts = Lamtim_olt::select('id', 'nama', 'kode', 'ip', 'teknologi', 'port_pon', 'port_uplink', 'latitude', 'longitude', 'status')
            ->get();

        return response()->json(['success' => true, 'data' => $olts]);
    }

    public function getOLT($id)
    {
        $olt = Lamtim_olt::find($id);
        if (!$olt) return response()->json(['success' => false, 'message' => 'OLT tidak ditemukan'], 404);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $olt->id,
                'nama' => $olt->nama,
                'kode' => $olt->kode,
                'ip' => $olt->ip,
                'teknologi' => $olt->teknologi ?? 'EPON',
                'port_pon' => $olt->port_pon,
                'port_uplink' => $olt->port_uplink,
                'lat' => $olt->latitude,
                'lng' => $olt->longitude,
                'status' => $olt->status,
                'keterangan' => $olt->keterangan,
            ]
        ]);
    }

    public function createOLT(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:100',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'ip' => 'nullable|string|max:50',
            'teknologi' => 'nullable|string',
            'port_pon' => 'nullable|integer|min:0',
            'port_uplink' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $olt = Lamtim_olt::create($validated);
            return response()->json(['success' => true, 'message' => 'OLT berhasil ditambahkan', 'data' => $olt]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function updateOLT(Request $request, $id)
    {
        $olt = Lamtim_olt::findOrFail($id);
        $validated = $request->validate([
            'nama' => 'nullable|string|max:255',
            'kode' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'ip' => 'nullable|string|max:50',
            'teknologi' => 'nullable|string',
            'port_pon' => 'nullable|integer|min:0',
            'port_uplink' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $olt->update($validated);
        return response()->json(['success' => true, 'message' => 'OLT berhasil diupdate', 'data' => $olt]);
    }

    public function deleteOLT($id)
    {
        $olt = Lamtim_olt::findOrFail($id);
        $olt->delete();
        return response()->json(['success' => true, 'message' => 'OLT berhasil dihapus']);
    }

    // ==================== ODC CRUD ====================

    public function getODCs()
    {
        $odcs = Lamtim_odc::select('id', 'nama', 'kode', 'port', 'portSisa', 'idOlt', 'portOlt', 'latitude', 'longitude')
            ->get();
        return response()->json(['success' => true, 'data' => $odcs]);
    }

    public function getODC($id)
    {
        $odc = Lamtim_odc::with('olt:id,nama')->find($id);
        if (!$odc) return response()->json(['success' => false, 'message' => 'ODC tidak ditemukan'], 404);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $odc->id,
                'nama' => $odc->nama,
                'kode' => $odc->kode,
                'port' => $odc->port,
                'portSisa' => $odc->portSisa,
                'portOlt' => $odc->portOlt,
                'idOlt' => $odc->idOlt,
                'olt_nama' => $odc->olt->nama ?? null,
                'lat' => $odc->latitude,
                'lng' => $odc->longitude,
                'status' => $odc->status,
                'keterangan' => $odc->keterangan,
            ]
        ]);
    }

    public function createODC(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:100',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'port' => 'nullable|integer|min:0',
            'idOlt' => 'nullable|integer|exists:lamtim_olts,id',
            'portOlt' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $port = $validated['port'] ?? 0;
            $odc = Lamtim_odc::create([
                'nama' => $validated['nama'],
                'kode' => $validated['kode'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'port' => $port,
                'portSisa' => $port,
                'idOlt' => $validated['idOlt'] ?? null,
                'portOlt' => $validated['portOlt'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            return response()->json(['success' => true, 'message' => 'ODC berhasil ditambahkan', 'data' => $odc]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function updateODC(Request $request, $id)
    {
        $odc = Lamtim_odc::findOrFail($id);
        $validated = $request->validate([
            'nama' => 'nullable|string|max:255',
            'kode' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'port' => 'nullable|integer|min:0',
            'idOlt' => 'nullable|integer',
            'portOlt' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        if (isset($validated['port'])) {
            $currentUsed = $odc->port - $odc->portSisa;
            $validated['portSisa'] = max(0, $validated['port'] - $currentUsed);
        }

        $odc->update($validated);
        return response()->json(['success' => true, 'message' => 'ODC berhasil diupdate', 'data' => $odc]);
    }

    public function deleteODC($id)
    {
        $odc = Lamtim_odc::findOrFail($id);
        $odc->delete();
        return response()->json(['success' => true, 'message' => 'ODC berhasil dihapus']);
    }

    // ==================== ODP CRUD ====================

    public function getODPs()
    {
        $odps = Lamtim_odp::select('id', 'nama', 'kode', 'port', 'portSisa', 'idOdc', 'portOdc', 'tipe', 'latitude', 'longitude')
            ->get();
        return response()->json(['success' => true, 'data' => $odps]);
    }

    public function getODP($id)
    {
        $odp = Lamtim_odp::with(['odc:id,nama', 'parentOdp:id,nama'])->find($id);
        if (!$odp) return response()->json(['success' => false, 'message' => 'ODP tidak ditemukan'], 404);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $odp->id,
                'nama' => $odp->nama,
                'kode' => $odp->kode,
                'port' => $odp->port,
                'portSisa' => $odp->portSisa,
                'portOdc' => $odp->portOdc,
                'idOdc' => $odp->idOdc,
                'odc_nama' => $odp->odc->nama ?? null,
                'idOdp' => $odp->idOdp,
                'odp_parent_nama' => $odp->parentOdp->nama ?? null,
                'tipe' => $odp->tipe ?? 'HTB',
                'fo_a' => $odp->fo_a,
                'fo_b' => $odp->fo_b,
                'kabel' => $odp->kabel,
                'lat' => $odp->latitude,
                'lng' => $odp->longitude,
                'status' => $odp->status,
                'keterangan' => $odp->keterangan,
            ]
        ]);
    }

    public function createODP(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:100',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'port' => 'nullable|integer|min:0',
            'tipe' => 'nullable|string',
            'idOdc' => 'nullable|integer|exists:lamtim_odcs,id',
            'idOdp' => 'nullable|integer|exists:lamtim_odps,id',
            'portOdc' => 'nullable|integer|min:0',
            'fo_a' => 'nullable|integer|min:0',
            'fo_b' => 'nullable|integer|min:0',
            'kabel' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        // Validasi: pilih salah satu parent (ODC atau ODP), tidak boleh keduanya
        if (!empty($validated['idOdc']) && !empty($validated['idOdp'])) {
            return response()->json(['success' => false, 'message' => 'Pilih salah satu: Parent ODC atau Parent ODP (Estafet), tidak boleh keduanya'], 422);
        }

        try {
            DB::beginTransaction();

            // Resolve idOlt dari parent ODC atau parent ODP
            $idOlt = null;
            if (!empty($validated['idOdc'])) {
                $parentOdc = Lamtim_odc::find($validated['idOdc']);
                $idOlt = $parentOdc?->idOlt;
            } elseif (!empty($validated['idOdp'])) {
                $parentOdp = Lamtim_odp::find($validated['idOdp']);
                $idOlt = $parentOdp?->idOlt;
            }

            $port = $validated['port'] ?? 0;
            $odp = Lamtim_odp::create([
                'nama' => $validated['nama'],
                'kode' => $validated['kode'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'port' => $port,
                'portSisa' => $port,
                'tipe' => $validated['tipe'] ?? 'Splitter',
                'idOlt' => $idOlt,
                'idOdc' => $validated['idOdc'] ?? null,
                'idOdp' => $validated['idOdp'] ?? null,
                'portOdc' => $validated['portOdc'] ?? null,
                'fo_a' => $validated['fo_a'] ?? 0,
                'fo_b' => $validated['fo_b'] ?? 0,
                'kabel' => $validated['kabel'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Decrement parent ODC port (jika parent ODC)
            if (!empty($validated['idOdc'])) {
                Lamtim_odc::where('id', $validated['idOdc'])->where('portSisa', '>', 0)->decrement('portSisa');
            }

            // Decrement parent ODP port (jika parent ODP / estafet)
            if (!empty($validated['idOdp'])) {
                Lamtim_odp::where('id', $validated['idOdp'])->where('portSisa', '>', 0)->decrement('portSisa');
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'ODP berhasil ditambahkan', 'data' => $odp]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function updateODP(Request $request, $id)
    {
        $odp = Lamtim_odp::findOrFail($id);
        $validated = $request->validate([
            'nama' => 'nullable|string|max:255',
            'kode' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'port' => 'nullable|integer|min:0',
            'tipe' => 'nullable|string',
            'idOdc' => 'nullable|integer',
            'idOdp' => 'nullable|integer',
            'portOdc' => 'nullable|integer|min:0',
            'fo_a' => 'nullable|integer|min:0',
            'fo_b' => 'nullable|integer|min:0',
            'kabel' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        if (isset($validated['port'])) {
            $currentUsed = $odp->port - $odp->portSisa;
            $validated['portSisa'] = max(0, $validated['port'] - $currentUsed);
        }

        $odp->update($validated);
        return response()->json(['success' => true, 'message' => 'ODP berhasil diupdate', 'data' => $odp]);
    }

    public function deleteODP($id)
    {
        $odp = Lamtim_odp::findOrFail($id);

        DB::beginTransaction();
        // Restore parent ODC port
        if ($odp->idOdc) {
            Lamtim_odc::where('id', $odp->idOdc)->increment('portSisa');
        }
        // Restore parent ODP port (estafet)
        if ($odp->idOdp) {
            Lamtim_odp::where('id', $odp->idOdp)->increment('portSisa');
        }
        $odp->delete();
        DB::commit();

        return response()->json(['success' => true, 'message' => 'ODP berhasil dihapus']);
    }

    // ==================== CLIENT CRUD ====================

    public function getClients()
    {
        $clients = Lamtim_user_mikrotik_details::with('user:id,name')
            ->select('id', 'idUser', 'idOdp', 'localAdress', 'latitude', 'longitude', 'status')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'nama' => $c->user->name ?? 'Unknown',
                'latitude' => $c->latitude,
                'longitude' => $c->longitude,
            ]);

        return response()->json(['success' => true, 'data' => $clients]);
    }

    public function getClient($id)
    {
        $client = Lamtim_user_mikrotik_details::with(['user:id,name,wa', 'user.user_detail:id,idUser,tglDafatar,tglJatuhTempo', 'odp:id,nama'])->find($id);
        if (!$client) return response()->json(['success' => false, 'message' => 'Client tidak ditemukan'], 404);

        $userDetail = $client->user->user_detail ?? null;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $client->id,
                'nama' => $client->user->name ?? 'Unknown',
                'wa' => $client->user->wa ?? '-',
                'idOdp' => $client->idOdp,
                'odp_nama' => $client->odp->nama ?? null,
                'ip' => $client->localAdress,
                'lat' => $client->latitude,
                'lng' => $client->longitude,
                'status' => $client->status,
                'idKategori' => $client->idKategori,
                'idPaket' => $client->idPaket,
                'idMikrotik' => $client->idMikrotik,
                'portOdp' => $client->portOdp,
                'namaMikrotikUser' => $client->namaMikrotikUser,
                'idMikrotikUser' => $client->idMikrotikUser,
                'serviceMikrotikUser' => $client->serviceMikrotikUser,
                'profileMikrotikUser' => $client->profileMikrotikUser,
                'password' => $client->password,
                'keterangan' => $client->keterangan,
                'tglDaftar' => $userDetail->tglDafatar ?? null,
                'tglJatuhTempo' => $userDetail->tglJatuhTempo ?? null,
            ]
        ]);
    }

    public function createClient(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'wa' => 'nullable|string|max:20',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'idOdp' => 'nullable|integer|exists:lamtim_odps,id',
            'portOdp' => 'nullable|integer|min:0',
            'idKategori' => 'nullable|integer',
            'idPaket' => 'nullable|integer',
            'idMikrotik' => 'nullable|integer',
            'secretName' => 'nullable|string|max:255',
            'secretId' => 'nullable|string|max:255',
            'secretService' => 'nullable|string|max:255',
            'secretProfile' => 'nullable|string|max:255',
            'secretPassword' => 'nullable|string|max:255',
            'ip' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string',
            'status' => 'nullable|string|in:online,offline,isolir',
            'tglDaftar' => 'nullable|date',
            'tglJatuhTempo' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $validated['nama'],
                'email' => 'client_' . time() . '_' . rand(100, 999) . '@local.net',
                'password' => bcrypt('password'),
                'wa' => $validated['wa'] ?? '-',
                'idRole' => 5,
            ]);

            // Create user_details with dates from form
            Lamtim_user_details::create([
                'idUser' => $user->id,
                'tglDafatar' => $validated['tglDaftar'] ?? now()->toDateString(),
                'tglJatuhTempo' => $validated['tglJatuhTempo'] ?? now()->addMonth()->toDateString(),
                'statusPpn' => 1,
                'statusTagihan' => 1,
                'jenisBayar' => 1,
                'js' => 'L',
                'identitas' => 'KTP',
            ]);

            // Resolve OLT/ODC from ODP
            $idOlt = null;
            $idOdc = null;
            if (!empty($validated['idOdp'])) {
                $odp = Lamtim_odp::find($validated['idOdp']);
                if ($odp) {
                    $idOlt = $odp->idOlt;
                    $idOdc = $odp->idOdc;
                }
            }

            $client = Lamtim_user_mikrotik_details::create([
                'idUser' => $user->id,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'idOlt' => $idOlt,
                'idOdc' => $idOdc,
                'idOdp' => $validated['idOdp'] ?? null,
                'portOdp' => $validated['portOdp'] ?? null,
                'idKategori' => $validated['idKategori'] ?? null,
                'idPaket' => $validated['idPaket'] ?? null,
                'idMikrotik' => $validated['idMikrotik'] ?? null,
                'namaMikrotikUser' => $validated['secretName'] ?? null,
                'idMikrotikUser' => $validated['secretId'] ?? null,
                'serviceMikrotikUser' => $validated['secretService'] ?? null,
                'profileMikrotikUser' => $validated['secretProfile'] ?? null,
                'password' => $validated['secretPassword'] ?? null,
                'localAdress' => $validated['ip'] ?? null,
                'statusIsolir' => ($validated['status'] ?? 'online') === 'isolir' ? 1 : 0,
                'status' => $validated['status'] ?? 'online',
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Decrement parent ODP port
            if (!empty($validated['idOdp']) && isset($odp) && $odp->portSisa > 0) {
                $odp->decrement('portSisa');
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Client berhasil ditambahkan', 'data' => $client]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function updateClient(Request $request, $id)
    {
        $client = Lamtim_user_mikrotik_details::with(['user.user_detail'])->findOrFail($id);
        $validated = $request->validate([
            'nama' => 'nullable|string|max:255',
            'wa' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'idOdp' => 'nullable|integer',
            'portOdp' => 'nullable|integer|min:0',
            'idKategori' => 'nullable|integer',
            'idPaket' => 'nullable|integer',
            'idMikrotik' => 'nullable|integer',
            'secretName' => 'nullable|string|max:255',
            'secretId' => 'nullable|string|max:255',
            'secretService' => 'nullable|string|max:255',
            'secretProfile' => 'nullable|string|max:255',
            'secretPassword' => 'nullable|string|max:255',
            'ip' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string',
            'status' => 'nullable|string|in:online,offline,isolir',
            'tglDaftar' => 'nullable|date',
            'tglJatuhTempo' => 'nullable|date',
        ]);

        if (isset($validated['nama']) && $client->user) {
            $userUpdate = ['name' => $validated['nama']];
            if (isset($validated['wa'])) $userUpdate['wa'] = $validated['wa'];
            $client->user->update($userUpdate);
        }

        // Resolve OLT/ODC from ODP if changed
        $idOlt = $client->idOlt;
        $idOdc = $client->idOdc;
        if (isset($validated['idOdp']) && $validated['idOdp'] != $client->idOdp) {
            if ($validated['idOdp']) {
                $odp = Lamtim_odp::find($validated['idOdp']);
                if ($odp) { $idOlt = $odp->idOlt; $idOdc = $odp->idOdc; }
            } else {
                $idOlt = null; $idOdc = null;
            }
        }

        $client->update([
            'latitude' => $validated['latitude'] ?? $client->latitude,
            'longitude' => $validated['longitude'] ?? $client->longitude,
            'idOlt' => $idOlt,
            'idOdc' => $idOdc,
            'idOdp' => $validated['idOdp'] ?? $client->idOdp,
            'portOdp' => $validated['portOdp'] ?? $client->portOdp,
            'idKategori' => $validated['idKategori'] ?? $client->idKategori,
            'idPaket' => $validated['idPaket'] ?? $client->idPaket,
            'idMikrotik' => $validated['idMikrotik'] ?? $client->idMikrotik,
            'namaMikrotikUser' => $validated['secretName'] ?? $client->namaMikrotikUser,
            'idMikrotikUser' => $validated['secretId'] ?? $client->idMikrotikUser,
            'serviceMikrotikUser' => $validated['secretService'] ?? $client->serviceMikrotikUser,
            'profileMikrotikUser' => $validated['secretProfile'] ?? $client->profileMikrotikUser,
            'password' => $validated['secretPassword'] ?? $client->password,
            'localAdress' => $validated['ip'] ?? $client->localAdress,
            'keterangan' => $validated['keterangan'] ?? $client->keterangan,
            'status' => $validated['status'] ?? $client->status,
            'statusIsolir' => isset($validated['status']) ? ($validated['status'] === 'isolir' ? 1 : 0) : $client->statusIsolir,
        ]);

        // Update tglDaftar / tglJatuhTempo on user_details
        if (isset($validated['tglDaftar']) || isset($validated['tglJatuhTempo'])) {
            $userDetail = $client->user->user_detail ?? null;
            if ($userDetail) {
                $detailUpdate = [];
                if (isset($validated['tglDaftar'])) $detailUpdate['tglDafatar'] = $validated['tglDaftar'];
                if (isset($validated['tglJatuhTempo'])) $detailUpdate['tglJatuhTempo'] = $validated['tglJatuhTempo'];
                $userDetail->update($detailUpdate);
            }
        }

        return response()->json(['success' => true, 'message' => 'Client berhasil diupdate', 'data' => $client]);
    }

    public function deleteClient($id)
    {
        $client = Lamtim_user_mikrotik_details::findOrFail($id);

        DB::beginTransaction();
        // Restore parent ODP port
        if ($client->idOdp) {
            Lamtim_odp::where('id', $client->idOdp)->increment('portSisa');
        }
        $client->delete();
        DB::commit();

        return response()->json(['success' => true, 'message' => 'Client berhasil dihapus']);
    }

    // ==================== IMPORT (UNMAPPED ITEMS) ====================

    public function getUnmappedItems()
    {
        $data = $this->networkMapService->getUnmappedItems();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function setItemCoordinates(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:olt,odc,odp,client',
            'id' => 'required|integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $model = $this->resolveModel($validated['type']);
        $device = $model::findOrFail($validated['id']);
        $device->latitude = $validated['latitude'];
        $device->longitude = $validated['longitude'];
        $device->save();

        return response()->json(['success' => true, 'message' => 'Koordinat berhasil disimpan']);
    }

    // ==================== HELPERS ====================

    private function resolveModel(string $type): string
    {
        return match ($type) {
            'olt' => Lamtim_olt::class,
            'odc' => Lamtim_odc::class,
            'odp' => Lamtim_odp::class,
            'client' => Lamtim_user_mikrotik_details::class,
            default => throw new \InvalidArgumentException("Unknown type: {$type}"),
        };
    }
}
