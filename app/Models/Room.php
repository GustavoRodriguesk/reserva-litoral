<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'booking.rooms';

    public $incrementing = false;

    protected $keyType = 'string';

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
