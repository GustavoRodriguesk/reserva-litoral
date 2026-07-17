<?php

namespace App\Services;

use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AvailabilityService
{
  
    public function availableRooms(
        string $hotelId,
        Carbon|string $checkIn,
        Carbon|string $checkOut,
        int $adults,
        int $children = 0
    ): Collection {

        $checkIn = Carbon::parse($checkIn)->startOfDay();
        $checkOut = Carbon::parse($checkOut)->startOfDay();

        if ($checkOut->lte($checkIn)) {
            throw new \InvalidArgumentException(
                'A data de check-out deve ser maior que a de check-in.'
            );
        }

        $guests = $adults + $children;

        return Room::query()

            ->select('booking.rooms.*')

            ->join(
                'booking.room_types',
                'booking.room_types.id',
                '=',
                'booking.rooms.room_type_id'
            )

            ->where('booking.rooms.hotel_id', $hotelId)

            ->where('booking.rooms.status', 'available')

            ->where('booking.room_types.max_capacity', '>=', $guests)

            ->whereNotExists(function ($query) use ($checkIn, $checkOut) {

                $query->select(DB::raw(1))
                    ->from('booking.reservation_rooms as rr')
                    ->join(
                        'booking.reservations as r',
                        'r.id',
                        '=',
                        'rr.reservation_id'
                    )

                    ->whereColumn('rr.room_id', 'booking.rooms.id')

                    ->where('rr.is_active', true)

                    ->whereRaw(
                        "daterange(rr.check_in_date, rr.check_out_date, '[)')
                         && daterange(?::date, ?::date, '[)')",
                        [
                            $checkIn->toDateString(),
                            $checkOut->toDateString(),
                        ]
                    );
            })

            ->orderBy('booking.rooms.number')

            ->get();
    }
}