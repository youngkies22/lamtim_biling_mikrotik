<?php

namespace App\Services;

use App\Models\Lamtim_google_map_polyline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GoogleMapPolylineService
{
    /**
     * Get all active polylines with eager-loaded relations
     */
    public function getAll(): array
    {
        $polylines = Lamtim_google_map_polyline::active()
            ->with([
                'odcFrom:id,nama,latitude,longitude',
                'odcTo:id,nama,latitude,longitude',
                'odpFrom:id,nama,latitude,longitude',
                'odpTo:id,nama,latitude,longitude',
                'user:id,name',
                'user.user_mikrotik:id,idUser,latitude,longitude',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return $polylines->map(function ($polyline) {
            return [
                'id' => $polyline->id,
                'nama' => $polyline->nama,
                'deskripsi' => $polyline->deskripsi,
                'tipe' => $polyline->tipe,
                'warna' => $polyline->warna,
                'ketebalan' => $polyline->ketebalan,
                'animasi' => $polyline->animasi,
                'koordinat' => $polyline->getResolvedCoordinates(),
                'id_odc_from' => $polyline->id_odc_from,
                'id_odc_to' => $polyline->id_odc_to,
                'id_odp_from' => $polyline->id_odp_from,
                'id_odp_to' => $polyline->id_odp_to,
                'id_user' => $polyline->id_user,
            ];
        })->toArray();
    }

    /**
     * Get polylines by type
     */
    public function getByTipe(string $tipe): array
    {
        $polylines = Lamtim_google_map_polyline::active()
            ->byTipe($tipe)
            ->with([
                'odcFrom:id,nama,latitude,longitude',
                'odcTo:id,nama,latitude,longitude',
                'odpFrom:id,nama,latitude,longitude',
                'odpTo:id,nama,latitude,longitude',
                'user:id,name',
                'user.user_mikrotik:id,idUser,latitude,longitude',
            ])
            ->get();

        return $polylines->map(function ($polyline) {
            return [
                'id' => $polyline->id,
                'nama' => $polyline->nama,
                'tipe' => $polyline->tipe,
                'warna' => $polyline->warna,
                'ketebalan' => $polyline->ketebalan,
                'animasi' => $polyline->animasi,
                'koordinat' => $polyline->getResolvedCoordinates(),
            ];
        })->toArray();
    }

    /**
     * Create a polyline connecting ODC to ODP
     */
    public function createOdcToOdp(int $idOdc, int $idOdp, array $options = []): Lamtim_google_map_polyline
    {
        return Lamtim_google_map_polyline::create([
            'tipe' => 'odc_to_odp',
            'id_odc_from' => $idOdc,
            'id_odp_to' => $idOdp,
            'nama' => $options['nama'] ?? null,
            'deskripsi' => $options['deskripsi'] ?? null,
            'warna' => $options['warna'] ?? '#ff9800', // Orange
            'ketebalan' => $options['ketebalan'] ?? 3,
            'animasi' => $options['animasi'] ?? true,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Create a polyline connecting ODP to ODP
     */
    public function createOdpToOdp(int $idOdpFrom, int $idOdpTo, array $options = []): Lamtim_google_map_polyline
    {
        return Lamtim_google_map_polyline::create([
            'tipe' => 'odp_to_odp',
            'id_odp_from' => $idOdpFrom,
            'id_odp_to' => $idOdpTo,
            'nama' => $options['nama'] ?? null,
            'deskripsi' => $options['deskripsi'] ?? null,
            'warna' => $options['warna'] ?? '#9c27b0', // Purple
            'ketebalan' => $options['ketebalan'] ?? 3,
            'animasi' => $options['animasi'] ?? true,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Create a polyline connecting ODP to User
     */
    public function createOdpToUser(int $idOdp, int $idUser, array $options = []): Lamtim_google_map_polyline
    {
        return Lamtim_google_map_polyline::create([
            'tipe' => 'odp_to_user',
            'id_odp_from' => $idOdp,
            'id_user' => $idUser,
            'nama' => $options['nama'] ?? null,
            'deskripsi' => $options['deskripsi'] ?? null,
            'warna' => $options['warna'] ?? '#2196f3', // Blue
            'ketebalan' => $options['ketebalan'] ?? 2,
            'animasi' => $options['animasi'] ?? true,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Create a custom polyline with arbitrary coordinates
     */
    public function createCustom(array $koordinat, array $options = []): Lamtim_google_map_polyline
    {
        return Lamtim_google_map_polyline::create([
            'tipe' => 'custom',
            'koordinat' => $koordinat,
            'nama' => $options['nama'] ?? null,
            'deskripsi' => $options['deskripsi'] ?? null,
            'warna' => $options['warna'] ?? '#3388ff',
            'ketebalan' => $options['ketebalan'] ?? 3,
            'animasi' => $options['animasi'] ?? true,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Save polyline from form data (auto-detect type)
     * Koordinat selalu disimpan untuk semua tipe (manual drawing)
     */
    public function save(array $data): Lamtim_google_map_polyline
    {
        $tipe = $data['tipe'] ?? 'custom';

        $polylineData = [
            'tipe' => $tipe,
            'nama' => $data['nama'] ?? null,
            'deskripsi' => $data['deskripsi'] ?? null,
            'warna' => $data['warna'] ?? '#3388ff',
            'ketebalan' => $data['ketebalan'] ?? 3,
            'animasi' => $data['animasi'] ?? true,
            'koordinat' => $data['koordinat'] ?? [], // Selalu simpan koordinat manual
            'created_by' => Auth::id(),
        ];

        // Set relasi berdasarkan tipe (optional - koordinat sudah cukup)
        if ($tipe === 'odc_to_odp') {
            $polylineData['id_odc_from'] = $data['id_odc_from'] ?? null;
            $polylineData['id_odp_to'] = $data['id_odp_to'] ?? null;
        } elseif ($tipe === 'odp_to_odp') {
            $polylineData['id_odp_from'] = $data['id_odp_from'] ?? null;
            $polylineData['id_odp_to'] = $data['id_odp_to'] ?? null;
        } elseif ($tipe === 'odp_to_user') {
            $polylineData['id_odp_from'] = $data['id_odp_from'] ?? null;
            $polylineData['id_user'] = $data['id_user'] ?? null;
        }

        // Store marker IDs if provided
        if (isset($data['marker_from_id'])) {
            $polylineData['marker_from_id'] = $data['marker_from_id'];
        }
        if (isset($data['marker_to_id'])) {
            $polylineData['marker_to_id'] = $data['marker_to_id'];
        }

        return Lamtim_google_map_polyline::create($polylineData);
    }

    /**
     * Update polyline
     */
    public function update(int $id, array $data): ?Lamtim_google_map_polyline
    {
        $polyline = Lamtim_google_map_polyline::find($id);
        if (!$polyline) {
            return null;
        }

        $polyline->update($data);
        return $polyline->fresh();
    }

    /**
     * Delete polyline
     */
    public function delete(int $id): bool
    {
        $polyline = Lamtim_google_map_polyline::find($id);
        if (!$polyline) {
            return false;
        }

        return $polyline->delete();
    }

    /**
     * Auto-generate polylines from existing ODC-ODP-User relations
     */
    public function generateFromRelations(): array
    {
        $created = ['odc_to_odp' => 0, 'odp_to_odp' => 0, 'odp_to_user' => 0];

        DB::beginTransaction();
        try {
            // 1. ODC to ODP (ODP yang punya idOdc)
            $odps = \App\Models\Lamtim_odp::whereNotNull('idOdc')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereNull('idOdp') // Tidak punya parent ODP
                ->get();

            foreach ($odps as $odp) {
                // Cek apakah sudah ada polyline
                $exists = Lamtim_google_map_polyline::where('tipe', 'odc_to_odp')
                    ->where('id_odc_from', $odp->idOdc)
                    ->where('id_odp_to', $odp->id)
                    ->exists();

                if (!$exists) {
                    $this->createOdcToOdp($odp->idOdc, $odp->id);
                    $created['odc_to_odp']++;
                }
            }

            // 2. ODP to ODP (ODP yang punya idOdp parent)
            $odpWithParent = \App\Models\Lamtim_odp::whereNotNull('idOdp')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get();

            foreach ($odpWithParent as $odp) {
                $exists = Lamtim_google_map_polyline::where('tipe', 'odp_to_odp')
                    ->where('id_odp_from', $odp->idOdp)
                    ->where('id_odp_to', $odp->id)
                    ->exists();

                if (!$exists) {
                    $this->createOdpToOdp($odp->idOdp, $odp->id);
                    $created['odp_to_odp']++;
                }
            }

            // 3. ODP to User
            $userDetails = \App\Models\Lamtim_user_mikrotik_details::whereNotNull('idOdp')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get();

            foreach ($userDetails as $detail) {
                $exists = Lamtim_google_map_polyline::where('tipe', 'odp_to_user')
                    ->where('id_odp_from', $detail->idOdp)
                    ->where('id_user', $detail->idUser)
                    ->exists();

                if (!$exists) {
                    $this->createOdpToUser($detail->idOdp, $detail->idUser);
                    $created['odp_to_user']++;
                }
            }

            DB::commit();
            return ['success' => true, 'created' => $created];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate polylines from relations: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Clear all polylines
     */
    public function clearAll(): int
    {
        return Lamtim_google_map_polyline::truncate() ? Lamtim_google_map_polyline::count() : 0;
    }
}
