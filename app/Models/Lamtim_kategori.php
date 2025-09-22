<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lamtim_kategori extends Model
{
  protected $fillable = [
    'nama',
    'description',
    'isActive',
  ];
}
