<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasUuids;

    protected $table = 'booking.reservations';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function mainGuest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'main_guest_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'main_guest_id');
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'booking.reservation_rooms', 'reservation_id', 'room_id')
            ->withPivot(['rate_per_night', 'check_in_date', 'check_out_date']);
    }

    public function reservationRooms()
    {
        return $this->hasMany(ReservationRoom::class);
    }

    public function charges()
    {
        return $this->hasMany(ReservationCharge::class);
    }

    public function events()
    {
        return $this->hasMany(ReservationEvent::class)->orderBy('performed_at', 'asc');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getCheckInAttribute()
    {
        return $this->check_in_date;
    }

    public function getCheckOutAttribute()
    {
        return $this->check_out_date;
    }
}
