<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lamtim_paket extends Model
{
  protected $fillable = [
    'idKategori',
    'kode',
    'nama',
    'price',
    'picture',
    'description',
    'isActive',
  ];
  public function kategori()
  {
    return $this->belongsTo(Lamtim_kategori::class, 'idKategori', 'id');
  }
}
