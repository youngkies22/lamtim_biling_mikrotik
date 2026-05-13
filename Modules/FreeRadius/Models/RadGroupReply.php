<?php

namespace Modules\FreeRadius\Models;

use Illuminate\Database\Eloquent\Model;

class RadGroupReply extends Model
{
    protected $table = 'radgroupreply';
    public $timestamps = false;

    protected $fillable = [
        'groupname',
        'attribute',
        'op',
        'value',
    ];
}
