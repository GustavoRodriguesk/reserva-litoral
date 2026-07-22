<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomType extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'booking.room_types';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'base_price' => 'decimal:2',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(RoomImage::class)->orderBy('position');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'booking.room_amenities', 'room_type_id', 'amenity_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function ratePlans(): HasMany
    {
        return $this->hasMany(RatePlan::class);
    }
}
