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
    'speed_limit',
    'ip_pool',
    'address_list',
    'is_burst',
    'burst_rate',
    'burst_threshold',
    'burst_time',
    'priority',
  ];

  protected static function boot()
  {
    parent::boot();

    static::saved(function ($model) {
      // Jika kode paket berubah, hapus data lama di Radius
      if ($model->isDirty('kode')) {
          $oldKode = $model->getOriginal('kode');
          try {
              \Illuminate\Support\Facades\DB::connection('radius')->table('radgroupreply')
                  ->where('groupname', $oldKode)
                  ->delete();
          } catch (\Exception $e) {
              \Illuminate\Support\Facades\Log::error("Gagal hapus data lama di Radius: " . $e->getMessage());
          }
      }

      // Hitung value untuk Mikrotik-Rate-Limit
      $rateLimitValue = $model->speed_limit;
      if ($model->is_burst) {
          $rateLimitValue .= " {$model->burst_rate} {$model->burst_threshold} {$model->burst_time} {$model->priority}";
      }

      $attributes = [
        'Mikrotik-Rate-Limit' => $rateLimitValue,
        'Framed-Pool' => $model->ip_pool,
        'Mikrotik-Address-List' => $model->address_list,
      ];

      foreach ($attributes as $attr => $value) {
        try {
          if ($value) {
            \Illuminate\Support\Facades\DB::connection('radius')->table('radgroupreply')->updateOrInsert(
              ['groupname' => $model->kode, 'attribute' => $attr],
              ['op' => ':=', 'value' => $value]
            );
          } else {
            \Illuminate\Support\Facades\DB::connection('radius')->table('radgroupreply')
              ->where('groupname', $model->kode)
              ->where('attribute', $attr)
              ->delete();
          }
        } catch (\Exception $e) {
          \Illuminate\Support\Facades\Log::error("Gagal sinkron atribut {$attr} ke Radius: " . $e->getMessage());
        }
      }
    });

    static::deleted(function ($model) {
      try {
        \Illuminate\Support\Facades\DB::connection('radius')->table('radgroupreply')
          ->where('groupname', $model->kode)
          ->delete();
      } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("Gagal hapus paket di Radius: " . $e->getMessage());
      }
    });
  }
  public function kategori()
  {
    return $this->belongsTo(Lamtim_kategori::class, 'idKategori', 'id');
  }
}
