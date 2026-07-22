<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PlanningBoardService
{
    public function build(Carbon $startDate, int $days, ?string $roomTypeId = null): array
    {
        $endDate = $startDate->copy()->addDays($days - 1);

        $roomsQuery = Room::query()
            ->with('roomType')
            ->orderBy('number');

        if (!empty($roomTypeId)) {
            $roomsQuery->where('room_type_id', $roomTypeId);
        }

        $rooms = $roomsQuery->get();

        $reservations = Reservation::query()
            ->with([
                'mainGuest',
                'rooms.room.roomType',
            ])
            ->whereIn('reservation_status', [
                'confirmed',
                'checked_in',
            ])
            ->whereDate('check_in_date', '<=', $endDate)
            ->whereDate('check_out_date', '>=', $startDate)
            ->get();

        return [
            'dates' => $this->buildDates($startDate, $days),
            'rooms' => $this->mapRooms($rooms, $reservations),
        ];
    }

    protected function buildDates(Carbon $startDate, int $days): Collection
    {
        return collect(range(0, $days - 1))
            ->map(fn ($day) => $startDate->copy()->addDays($day));
    }

    protected function mapRooms(Collection $rooms, Collection $reservations): Collection
    {
        return $rooms->map(function ($room) use ($reservations) {
            $matchedReservations = $reservations->filter(function ($reservation) use ($room) {
                return $reservation->rooms->contains('room_id', $room->id);
            })->map(function ($reservation) use ($room) {
                $resRoom = $reservation->rooms->firstWhere('room_id', $room->id);
                
                return [
                    'id' => $reservation->id,
                    'locator_code' => $reservation->locator_code,
                    'guest_name' => $reservation->mainGuest->full_name ?? 'Hóspede sem nome',
                    'reservation_status' => $reservation->reservation_status,
                    'stay_status' => $reservation->stay_status,
                    'check_in_date' => $reservation->check_in_date->toDateString(),
                    'check_out_date' => $reservation->check_out_date->toDateString(),
                    'reservation_room_id' => $resRoom?->id,
                    'total_amount' => (float)$reservation->total_amount,
                ];
            })->values();

            return [
                'room' => $room,
                'reservations' => $matchedReservations,
            ];
        });
    }
}