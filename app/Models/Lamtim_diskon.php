<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lamtim_diskon extends Model
{
  protected $fillable = [
    'idUser',
    'bulan',
    'nominal',
    'createBy',
  ];
}
