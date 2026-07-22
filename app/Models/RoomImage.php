<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomImage extends Model
{
    use HasUuids;

    protected $table = 'booking.room_images';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public const UPDATED_AT = null;

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }
}
