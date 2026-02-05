<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lamtim_google_map_marker extends Model
{
    use HasFactory;

    protected $table = 'lamtim_google_map_markers';

    protected $fillable = [
        'tipe',
        'ref_id',
        'nama',
        'latitude',
        'longitude',
        'icon',
        'warna',
        'extra_data',
        'is_visible',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'extra_data' => 'array',
        'is_visible' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    // === RELATIONSHIPS ===

    public function odc()
    {
        return $this->belongsTo(Lamtim_odc::class, 'ref_id', 'id');
    }

    public function odp()
    {
        return $this->belongsTo(Lamtim_odp::class, 'ref_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'ref_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // === SCOPES ===

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeByTipe($query, $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    public function scopeOdc($query)
    {
        return $query->where('tipe', 'odc');
    }

    public function scopeOdp($query)
    {
        return $query->where('tipe', 'odp');
    }

    public function scopeUser($query)
    {
        return $query->where('tipe', 'user');
    }

    // === HELPER METHODS ===

    /**
     * Get icon URL based on type
     */
    public function getIconUrl(): string
    {
        if ($this->icon) {
            return $this->icon;
        }

        $icons = [
            'odc' => 'http://maps.google.com/mapfiles/ms/icons/orange-dot.png',
            'odp' => 'http://maps.google.com/mapfiles/ms/icons/green-dot.png',
            'user' => 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
            'custom' => 'http://maps.google.com/mapfiles/ms/icons/purple-dot.png',
        ];

        return $icons[$this->tipe] ?? $icons['custom'];
    }

    /**
     * Get marker data for map
     */
    public function toMapData(): array
    {
        return [
            'id' => $this->id,
            'tipe' => $this->tipe,
            'ref_id' => $this->ref_id,
            'nama' => $this->nama,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'icon' => $this->getIconUrl(),
            'warna' => $this->warna,
            'extra_data' => $this->extra_data,
        ];
    }
}
