<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lamtim_odp extends Model
{
  protected $fillable = [
    'idOlt',
    'idOdc',
    'idOdp',
    'nama',
    'kode',
    'port',
    'portSisa',
    'portOdc',
    'latitude',
    'longitude',
    'route_waypoints',
    'tipe',
    'fo_a',
    'fo_b',
    'kabel',
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

  public function odc()
  {
    return $this->belongsTo(Lamtim_odc::class, 'idOdc', 'id');
  }

  public function parentOdp()
  {
    return $this->belongsTo(Lamtim_odp::class, 'idOdp', 'id');
  }

  public function clients()
  {
    return $this->hasMany(Lamtim_user_mikrotik_details::class, 'idOdp', 'id');
  }
}
