<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomType extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'booking.room_types';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'base_price' => 'decimal:2',
    ];
}
