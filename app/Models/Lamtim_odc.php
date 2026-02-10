<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lamtim_odc extends Model
{
  protected $fillable = [
    'idOlt',
    'nama',
    'kode',
    'port',
    'portSisa',
    'portOlt',
    'latitude',
    'longitude',
    'route_waypoints',
    'status',
    'keterangan',
  ];

  protected $casts = [
    'route_waypoints' => 'array',
  ];

  public function olt()
  {
    return $this->belongsTo(Lamtim_olt::class, 'idOlt', 'id');
  }

  public function odps()
  {
    return $this->hasMany(Lamtim_odp::class, 'idOdc', 'id');
  }
}
