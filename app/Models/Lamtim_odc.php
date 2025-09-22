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
  ];

  public function olt()
  {
    return $this->belongsTo(Lamtim_olt::class, 'idOlt', 'id');
  }
}
