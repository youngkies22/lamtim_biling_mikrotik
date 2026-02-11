<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lamtim_odc extends Model
{
  protected $fillable = [
    'idOlt',
    'idOdc',
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

  public function parentOdc()
  {
    return $this->belongsTo(self::class, 'idOdc', 'id');
  }

  public function childOdcs()
  {
    return $this->hasMany(self::class, 'idOdc', 'id');
  }

  public function odps()
  {
    return $this->hasMany(Lamtim_odp::class, 'idOdc', 'id');
  }
}
