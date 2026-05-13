<?php

namespace Modules\FreeRadius\Models;

use Illuminate\Database\Eloquent\Model;

class RadiusConfig extends Model
{
    protected $table = 'lamtim_radius_configs';
    
    protected $fillable = [
        'host',
        'port',
        'database',
        'username',
        'password',
        'secret',
        'isActive',
    ];
}
