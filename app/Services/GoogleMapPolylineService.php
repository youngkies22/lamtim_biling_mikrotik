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

        // Get marker information if provided
        $markerFrom = null;
        $markerTo = null;
        
        if (isset($data['marker_from_id'])) {
            $markerFrom = \App\Models\Lamtim_google_map_marker::find($data['marker_from_id']);
            $polylineData['marker_from_id'] = $data['marker_from_id'];
        }
        
        if (isset($data['marker_to_id'])) {
            $markerTo = \App\Models\Lamtim_google_map_marker::find($data['marker_to_id']);
            $polylineData['marker_to_id'] = $data['marker_to_id'];
        }

        // Auto-detect and set relations based on markers
        if ($markerFrom && $markerTo) {
            if ($tipe === 'odc_to_odp') {
                // Handle both directions: ODC to ODP or ODP to ODC
                if ($markerFrom->tipe === 'odc' && $markerTo->tipe === 'odp') {
                    $polylineData['id_odc_from'] = $markerFrom->ref_id;
                    $polylineData['id_odp_to'] = $markerTo->ref_id;
                } elseif ($markerFrom->tipe === 'odp' && $markerTo->tipe === 'odc') {
                    // Reverse direction: swap them
                    $polylineData['id_odc_from'] = $markerTo->ref_id;
                    $polylineData['id_odp_to'] = $markerFrom->ref_id;
                }
            } elseif ($tipe === 'odp_to_odp') {
                if ($markerFrom->tipe === 'odp' && $markerTo->tipe === 'odp') {
                    $polylineData['id_odp_from'] = $markerFrom->ref_id;
                    $polylineData['id_odp_to'] = $markerTo->ref_id;
                }
            } elseif ($tipe === 'odp_to_user') {
                if ($markerFrom->tipe === 'odp' && $markerTo->tipe === 'user') {
                    $polylineData['id_odp_from'] = $markerFrom->ref_id;
                    $polylineData['id_user'] = $markerTo->ref_id;
                } elseif ($markerFrom->tipe === 'user' && $markerTo->tipe === 'odp') {
                    // Reverse direction: swap them
                    $polylineData['id_odp_from'] = $markerTo->ref_id;
                    $polylineData['id_user'] = $markerFrom->ref_id;
                }
            }
        } else {
            // Fallback to manual IDs if provided
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
        }

        DB::beginTransaction();
        try {
            // Create the polyline
            $polyline = Lamtim_google_map_polyline::create($polylineData);

            // Decrease ports based on connection type
            if ($tipe === 'odc_to_odp' && isset($polylineData['id_odc_from'])) {
                // Decrease portSisa in ODC
                $odc = \App\Models\Lamtim_odc::find($polylineData['id_odc_from']);
                if ($odc && $odc->portSisa > 0) {
                    $odc->portSisa = max(0, $odc->portSisa - 1);
                    $odc->save();
                    
                    // Update marker's extra_data to reflect new portSisa
                    $marker = \App\Models\Lamtim_google_map_marker::where('tipe', 'odc')
                        ->where('ref_id', $odc->id)
                        ->first();
                    if ($marker && $marker->extra_data) {
                        $extraData = $marker->extra_data;
                        $extraData['portSisa'] = $odc->portSisa;
                        $extraData['port'] = $odc->port;
                        $extraData['portOlt'] = $odc->portOlt;
                        $marker->update(['extra_data' => $extraData]);
                    }
                }
            } elseif ($tipe === 'odp_to_odp' && isset($polylineData['id_odp_from']) && isset($polylineData['id_odp_to'])) {
                // Decrease portSisa in both ODPs
                $odpFrom = \App\Models\Lamtim_odp::find($polylineData['id_odp_from']);
                if ($odpFrom && $odpFrom->portSisa > 0) {
                    $odpFrom->portSisa = max(0, $odpFrom->portSisa - 1);
                    $odpFrom->save();
                    
                    // Update marker's extra_data
                    $markerFrom = \App\Models\Lamtim_google_map_marker::where('tipe', 'odp')
                        ->where('ref_id', $odpFrom->id)
                        ->first();
                    if ($markerFrom && $markerFrom->extra_data) {
                        $extraDataFrom = $markerFrom->extra_data;
                        $extraDataFrom['portSisa'] = $odpFrom->portSisa;
                        $extraDataFrom['port'] = $odpFrom->port;
                        $extraDataFrom['portOdc'] = $odpFrom->portOdc;
                        $markerFrom->update(['extra_data' => $extraDataFrom]);
                    }
                }
                
                $odpTo = \App\Models\Lamtim_odp::find($polylineData['id_odp_to']);
                if ($odpTo && $odpTo->portSisa > 0) {
                    $odpTo->portSisa = max(0, $odpTo->portSisa - 1);
                    $odpTo->save();
                    
                    // Update marker's extra_data
                    $markerTo = \App\Models\Lamtim_google_map_marker::where('tipe', 'odp')
                        ->where('ref_id', $odpTo->id)
                        ->first();
                    if ($markerTo && $markerTo->extra_data) {
                        $extraDataTo = $markerTo->extra_data;
                        $extraDataTo['portSisa'] = $odpTo->portSisa;
                        $extraDataTo['port'] = $odpTo->port;
                        $extraDataTo['portOdc'] = $odpTo->portOdc;
                        $markerTo->update(['extra_data' => $extraDataTo]);
                    }
                }
            } elseif ($tipe === 'odp_to_user' && isset($polylineData['id_odp_from'])) {
                // Decrease portSisa in ODP
                $odp = \App\Models\Lamtim_odp::find($polylineData['id_odp_from']);
                if ($odp && $odp->portSisa > 0) {
                    $odp->portSisa = max(0, $odp->portSisa - 1);
                    $odp->save();
                    
                    // Update marker's extra_data
                    $marker = \App\Models\Lamtim_google_map_marker::where('tipe', 'odp')
                        ->where('ref_id', $odp->id)
                        ->first();
                    if ($marker && $marker->extra_data) {
                        $extraData = $marker->extra_data;
                        $extraData['portSisa'] = $odp->portSisa;
                        $extraData['port'] = $odp->port;
                        $extraData['portOdc'] = $odp->portOdc;
                        $marker->update(['extra_data' => $extraData]);
                    }
                }
            }

            DB::commit();
            return $polyline;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save polyline: ' . $e->getMessage());
            throw $e;
        }
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

        DB::beginTransaction();
        try {
            $tipe = $polyline->tipe;
            
            // Restore ports when deleting polyline
            if ($tipe === 'odc_to_odp' && $polyline->id_odc_from) {
                // Restore portSisa in ODC
                $odc = \App\Models\Lamtim_odc::find($polyline->id_odc_from);
                if ($odc) {
                    $odc->portSisa = min($odc->port, $odc->portSisa + 1);
                    $odc->save();
                    
                    // Update marker's extra_data
                    $marker = \App\Models\Lamtim_google_map_marker::where('tipe', 'odc')
                        ->where('ref_id', $odc->id)
                        ->first();
                    if ($marker && $marker->extra_data) {
                        $extraData = $marker->extra_data;
                        $extraData['portSisa'] = $odc->portSisa;
                        $extraData['port'] = $odc->port;
                        $extraData['portOlt'] = $odc->portOlt;
                        $marker->update(['extra_data' => $extraData]);
                    }
                }
            } elseif ($tipe === 'odp_to_odp') {
                // Restore portSisa in both ODPs
                if ($polyline->id_odp_from) {
                    $odpFrom = \App\Models\Lamtim_odp::find($polyline->id_odp_from);
                    if ($odpFrom) {
                        $odpFrom->portSisa = min($odpFrom->port, $odpFrom->portSisa + 1);
                        $odpFrom->save();
                        
                        // Update marker's extra_data
                        $markerFrom = \App\Models\Lamtim_google_map_marker::where('tipe', 'odp')
                            ->where('ref_id', $odpFrom->id)
                            ->first();
                        if ($markerFrom && $markerFrom->extra_data) {
                            $extraDataFrom = $markerFrom->extra_data;
                            $extraDataFrom['portSisa'] = $odpFrom->portSisa;
                            $extraDataFrom['port'] = $odpFrom->port;
                            $extraDataFrom['portOdc'] = $odpFrom->portOdc;
                            $markerFrom->update(['extra_data' => $extraDataFrom]);
                        }
                    }
                }
                
                if ($polyline->id_odp_to) {
                    $odpTo = \App\Models\Lamtim_odp::find($polyline->id_odp_to);
                    if ($odpTo) {
                        $odpTo->portSisa = min($odpTo->port, $odpTo->portSisa + 1);
                        $odpTo->save();
                        
                        // Update marker's extra_data
                        $markerTo = \App\Models\Lamtim_google_map_marker::where('tipe', 'odp')
                            ->where('ref_id', $odpTo->id)
                            ->first();
                        if ($markerTo && $markerTo->extra_data) {
                            $extraDataTo = $markerTo->extra_data;
                            $extraDataTo['portSisa'] = $odpTo->portSisa;
                            $extraDataTo['port'] = $odpTo->port;
                            $extraDataTo['portOdc'] = $odpTo->portOdc;
                            $markerTo->update(['extra_data' => $extraDataTo]);
                        }
                    }
                }
            } elseif ($tipe === 'odp_to_user' && $polyline->id_odp_from) {
                // Restore portSisa in ODP
                $odp = \App\Models\Lamtim_odp::find($polyline->id_odp_from);
                if ($odp) {
                    $odp->portSisa = min($odp->port, $odp->portSisa + 1);
                    $odp->save();
                    
                    // Update marker's extra_data
                    $marker = \App\Models\Lamtim_google_map_marker::where('tipe', 'odp')
                        ->where('ref_id', $odp->id)
                        ->first();
                    if ($marker && $marker->extra_data) {
                        $extraData = $marker->extra_data;
                        $extraData['portSisa'] = $odp->portSisa;
                        $extraData['port'] = $odp->port;
                        $extraData['portOdc'] = $odp->portOdc;
                        $marker->update(['extra_data' => $extraData]);
                    }
                }
            }

            $deleted = $polyline->delete();
            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete polyline: ' . $e->getMessage());
            throw $e;
        }
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
        DB::beginTransaction();
        try {
            // Get all polylines before deleting
            $polylines = Lamtim_google_map_polyline::all();
            
            // Restore ports for all polylines
            foreach ($polylines as $polyline) {
                $tipe = $polyline->tipe;
                
                if ($tipe === 'odc_to_odp' && $polyline->id_odc_from) {
                    $odc = \App\Models\Lamtim_odc::find($polyline->id_odc_from);
                    if ($odc) {
                        $odc->portSisa = min($odc->port, $odc->portSisa + 1);
                        $odc->save();
                        
                        // Update marker's extra_data
                        $marker = \App\Models\Lamtim_google_map_marker::where('tipe', 'odc')
                            ->where('ref_id', $odc->id)
                            ->first();
                        if ($marker && $marker->extra_data) {
                            $extraData = $marker->extra_data;
                            $extraData['portSisa'] = $odc->portSisa;
                            $extraData['port'] = $odc->port;
                            $extraData['portOlt'] = $odc->portOlt;
                            $marker->update(['extra_data' => $extraData]);
                        }
                    }
                } elseif ($tipe === 'odp_to_odp') {
                    if ($polyline->id_odp_from) {
                        $odpFrom = \App\Models\Lamtim_odp::find($polyline->id_odp_from);
                        if ($odpFrom) {
                            $odpFrom->portSisa = min($odpFrom->port, $odpFrom->portSisa + 1);
                            $odpFrom->save();
                            
                            // Update marker's extra_data
                            $markerFrom = \App\Models\Lamtim_google_map_marker::where('tipe', 'odp')
                                ->where('ref_id', $odpFrom->id)
                                ->first();
                            if ($markerFrom && $markerFrom->extra_data) {
                                $extraDataFrom = $markerFrom->extra_data;
                                $extraDataFrom['portSisa'] = $odpFrom->portSisa;
                                $extraDataFrom['port'] = $odpFrom->port;
                                $extraDataFrom['portOdc'] = $odpFrom->portOdc;
                                $markerFrom->update(['extra_data' => $extraDataFrom]);
                            }
                        }
                    }
                    if ($polyline->id_odp_to) {
                        $odpTo = \App\Models\Lamtim_odp::find($polyline->id_odp_to);
                        if ($odpTo) {
                            $odpTo->portSisa = min($odpTo->port, $odpTo->portSisa + 1);
                            $odpTo->save();
                            
                            // Update marker's extra_data
                            $markerTo = \App\Models\Lamtim_google_map_marker::where('tipe', 'odp')
                                ->where('ref_id', $odpTo->id)
                                ->first();
                            if ($markerTo && $markerTo->extra_data) {
                                $extraDataTo = $markerTo->extra_data;
                                $extraDataTo['portSisa'] = $odpTo->portSisa;
                                $extraDataTo['port'] = $odpTo->port;
                                $extraDataTo['portOdc'] = $odpTo->portOdc;
                                $markerTo->update(['extra_data' => $extraDataTo]);
                            }
                        }
                    }
                } elseif ($tipe === 'odp_to_user' && $polyline->id_odp_from) {
                    $odp = \App\Models\Lamtim_odp::find($polyline->id_odp_from);
                    if ($odp) {
                        $odp->portSisa = min($odp->port, $odp->portSisa + 1);
                        $odp->save();
                        
                        // Update marker's extra_data
                        $marker = \App\Models\Lamtim_google_map_marker::where('tipe', 'odp')
                            ->where('ref_id', $odp->id)
                            ->first();
                        if ($marker && $marker->extra_data) {
                            $extraData = $marker->extra_data;
                            $extraData['portSisa'] = $odp->portSisa;
                            $extraData['port'] = $odp->port;
                            $extraData['portOdc'] = $odp->portOdc;
                            $marker->update(['extra_data' => $extraData]);
                        }
                    }
                }
            }
            
            // Delete all polylines
            $count = Lamtim_google_map_polyline::count();
            Lamtim_google_map_polyline::truncate();
            
            DB::commit();
            return $count;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to clear all polylines: ' . $e->getMessage());
            throw $e;
        }
    }
}
