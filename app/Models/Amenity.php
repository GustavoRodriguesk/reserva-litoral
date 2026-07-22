<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Amenity extends Model
{
    use HasUuids;

    protected $table = 'core.amenities';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public $timestamps = false;

    public function roomTypes(): BelongsToMany
    {
        return $this->belongsToMany(RoomType::class, 'booking.room_amenities', 'amenity_id', 'room_type_id');
    }
}
