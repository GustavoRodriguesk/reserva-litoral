<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationCharge extends Model
{
    use HasUuids;

    protected $table = 'booking.reservation_charges';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_discount' => 'boolean',
    ];

    public const UPDATED_AT = null;

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
