<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Lamtim_tagihans extends Model
{
  protected $fillable = [
    'id',
    'idUser',
    'idPaket',
    'noTagihan',
    'bulan',
    'tahun',
    'statusBayar',
    'harga',
    'ppn',
    'diskon',
    'total',
    'tglJatuhTempo',
    'tglBayar',
    'prosesNama',
    'prosesBy',
    'metode',
    'aksi',
    'created_at',
    'updated_at',
    'create_by',
  ];

  protected $casts = [
    'tglBayar' => 'datetime:Y-m-d H:i:s', // Format custom
    'tglJatuhTempo' => 'date:Y-m-d', // Jika ada
    'harga' => 'decimal:2',
    'ppn' => 'decimal:2',
    'diskon' => 'decimal:2',
    'total' => 'decimal:2',
  ];

  /**
   * Enkripsi ID hanya saat ditampilkan
   */
  protected function getIdAttribute($value)
  {
    if (!empty($value)) {
      return encrypt($value);
    }
    return $value;
  }
  public function user()
  {
    return $this->hasOne(User::class, 'id', 'idUser');
  }
  public function user_mikrotik()
  {
    return $this->hasOne(Lamtim_user_mikrotik_details::class, 'idUser', 'id');
  }
  public function paket()
  {
    return $this->belongsTo(Lamtim_paket::class, 'idPaket', 'id');
  }
}
