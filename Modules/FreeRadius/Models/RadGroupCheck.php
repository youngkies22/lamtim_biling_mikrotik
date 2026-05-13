<?php

namespace Modules\FreeRadius\Models;

use Illuminate\Database\Eloquent\Model;

class RadGroupCheck extends Model
{
    protected $table = 'radgroupcheck';
    public $timestamps = false;

    protected $fillable = [
        'groupname',
        'attribute',
        'op',
        'value',
    ];
}
