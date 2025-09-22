<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lamtim_olt extends Model
{
  protected $fillable = [
    'nama',
    'kode',
    'ip',
    'port',
    'sfp',
    'isActive',
  ];
}
