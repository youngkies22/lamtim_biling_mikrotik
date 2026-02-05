<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lamtim_google_map_polyline extends Model
{
    use HasFactory;

    protected $table = 'lamtim_google_map_polylines';

    protected $fillable = [
        'nama',
        'deskripsi',
        'tipe',
        'id_odc_from',
        'id_odc_to',
        'id_odp_from',
        'id_odp_to',
        'id_user',
        'warna',
        'ketebalan',
        'animasi',
        'koordinat',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'koordinat' => 'array',
        'animasi' => 'boolean',
        'is_active' => 'boolean',
    ];

    // === RELATIONSHIPS ===

    public function odcFrom()
    {
        return $this->belongsTo(Lamtim_odc::class, 'id_odc_from', 'id');
    }

    public function odcTo()
    {
        return $this->belongsTo(Lamtim_odc::class, 'id_odc_to', 'id');
    }

    public function odpFrom()
    {
        return $this->belongsTo(Lamtim_odp::class, 'id_odp_from', 'id');
    }

    public function odpTo()
    {
        return $this->belongsTo(Lamtim_odp::class, 'id_odp_to', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // === SCOPES ===

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTipe($query, $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    // === HELPER METHODS ===

    /**
     * Get coordinates for the polyline based on type and relations
     */
    public function getResolvedCoordinates(): array
    {
        // Jika koordinat custom sudah ada, gunakan itu
        if (!empty($this->koordinat)) {
            return $this->koordinat;
        }

        $coords = [];

        // ODC to ODP
        if ($this->tipe === 'odc_to_odp' && $this->odcFrom && $this->odpTo) {
            if ($this->odcFrom->latitude && $this->odcFrom->longitude) {
                $coords[] = ['lat' => (float) $this->odcFrom->latitude, 'lng' => (float) $this->odcFrom->longitude];
            }
            if ($this->odpTo->latitude && $this->odpTo->longitude) {
                $coords[] = ['lat' => (float) $this->odpTo->latitude, 'lng' => (float) $this->odpTo->longitude];
            }
        }

        // ODP to ODP
        if ($this->tipe === 'odp_to_odp' && $this->odpFrom && $this->odpTo) {
            if ($this->odpFrom->latitude && $this->odpFrom->longitude) {
                $coords[] = ['lat' => (float) $this->odpFrom->latitude, 'lng' => (float) $this->odpFrom->longitude];
            }
            if ($this->odpTo->latitude && $this->odpTo->longitude) {
                $coords[] = ['lat' => (float) $this->odpTo->latitude, 'lng' => (float) $this->odpTo->longitude];
            }
        }

        // ODP to User
        if ($this->tipe === 'odp_to_user' && $this->odpFrom && $this->user) {
            if ($this->odpFrom->latitude && $this->odpFrom->longitude) {
                $coords[] = ['lat' => (float) $this->odpFrom->latitude, 'lng' => (float) $this->odpFrom->longitude];
            }
            $userMikrotik = $this->user->user_mikrotik;
            if ($userMikrotik && $userMikrotik->latitude && $userMikrotik->longitude) {
                $coords[] = ['lat' => (float) $userMikrotik->latitude, 'lng' => (float) $userMikrotik->longitude];
            }
        }

        return $coords;
    }
}
