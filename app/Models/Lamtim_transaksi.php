<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lamtim_transaksi extends Model
{
  protected $fillable = [
    'idUser',
    'idTagihan',
    'metodeBayar',
    'namaPaket',
    'tglBayar',
    'prosesBy',
    'prosesNama',
    'harga',
    'ppn',
    'diskon',
    'total',
    'keterangan'
  ];

  protected $casts = [
    'tglBayar' => 'datetime',
    'harga' => 'decimal:2',
    'ppn' => 'decimal:2',
    'diskon' => 'decimal:2',
    'total' => 'decimal:2',
  ];

  // Relasi ke User
  public function user()
  {
    return $this->belongsTo(User::class, 'idUser');
  }

  // Relasi ke Tagihan
  public function tagihan()
  {
    return $this->belongsTo(Lamtim_tagihans::class, 'idTagihan');
  }

  // Relasi ke User yang memproses
  public function processor()
  {
    return $this->belongsTo(User::class, 'prosesBy');
  }
}
