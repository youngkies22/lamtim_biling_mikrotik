<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lamtim_mikrotik extends Model
{
  protected $fillable = [
    'nama',
    'kode',
    'username',
    'password',
    'ip',
    'port',
    'isActive',
  ];
  //protected $guarded = [];
}
