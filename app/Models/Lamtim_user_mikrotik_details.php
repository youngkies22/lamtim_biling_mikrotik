<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lamtim_user_mikrotik_details extends Model
{
  protected $hidden = ['created_at'];
  protected $guarded = [];

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

  public function odp()
  {
    return $this->belongsTo(Lamtim_odp::class, 'idOdp', 'id');
  }
  public function user()
  {
    return $this->belongsTo(User::class, 'idUser', 'id');
  }
  public function paket()
  {
    return $this->belongsTo(Lamtim_paket::class, 'idPaket', 'id');
  }
  public function kategori()
  {
    return $this->belongsTo(Lamtim_kategori::class, 'idKategori', 'id');
  }
  public function mikrotik()
  {
    return $this->belongsTo(Lamtim_mikrotik::class, 'idMikrotik', 'id');
  }
}
