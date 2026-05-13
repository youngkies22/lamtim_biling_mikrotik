<?php

namespace Modules\FreeRadius\Models;

use Illuminate\Database\Eloquent\Model;

class RadUserGroup extends Model
{
    protected $table = 'radusergroup';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'groupname',
        'priority',
    ];
}
