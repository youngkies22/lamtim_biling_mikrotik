<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lamtim_ip_pool extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ranges',
        'next_pool',
    ];
}
