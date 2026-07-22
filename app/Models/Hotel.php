<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'core.hotels';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }
}
