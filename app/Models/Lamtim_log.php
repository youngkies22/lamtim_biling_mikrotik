<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lamtim_log extends Model
{
  protected $fillable = ['idUser', 'jenis', 'nama', 'date', 'keterangan'];

  protected $casts = [
    'date' => 'datetime',
  ];

  public function user()
  {
    return $this->belongsTo(User::class, 'idUser');
  }
}
