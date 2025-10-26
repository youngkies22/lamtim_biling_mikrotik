<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Lamtim_area extends Model
{
  protected $table = 'lamtim_areas';

  protected $fillable = [
    'name',
    'address',
    'code_area'
  ];

  protected function id(): Attribute
  {
    return Attribute::make(
      get: fn($value) => encrypt($value)
    );
  }
}
