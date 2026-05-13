<?php

namespace Modules\FreeRadius\Models;

use Illuminate\Database\Eloquent\Model;

class Nas extends Model
{
    protected $table = 'nas';
    public $timestamps = false;
    protected $guarded = [];
}
