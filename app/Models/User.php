<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'julukan',
    'isActive',
    'idRole',
    'wa',
    'email',
    'password',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
  ];

  public function user_detail()
  {
    return $this->hasOne(Lamtim_user_details::class, 'idUser', 'id');
  }
  public function user_mikrotik()
  {
    return $this->hasOne(Lamtim_user_mikrotik_details::class, 'idUser', 'id');
  }
  public function fotos()
  {
    return $this->hasMany(Lamtim_foto::class, 'idPelanggan', 'id');
  }
}
