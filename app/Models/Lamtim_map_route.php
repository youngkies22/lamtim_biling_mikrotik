<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lamtim_map_route extends Model
{
  use HasFactory;

  protected $table = 'lamtim_map_routes';

  protected $fillable = [
    'name',
    'description',
    'color',
    'weight',
    'coordinates',
    'type',
    'created_by'
  ];

  protected $casts = [
    'coordinates' => 'array',
  ];

  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by', 'id');
  }
}
