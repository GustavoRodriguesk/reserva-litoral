<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function data(): array
    {
        $hotelId = auth()->user()->hotel_id;

        // 1. Receita hoje (pagamentos realizados hoje)
        $revenueToday = (float) DB::table('finance.payments')
            ->join('booking.reservations', 'booking.reservations.id', '=', 'finance.payments.reservation_id')
            ->where('booking.reservations.hotel_id', $hotelId)
            ->where('finance.payments.status', 'paid')
            ->whereDate('finance.payments.paid_at', today())
            ->sum('finance.payments.amount');

        // 2. Ocupação do hotel (percentual de quartos ocupados hoje)
        $totalRooms = DB::table('booking.rooms')
            ->where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->count();

        $occupiedRooms = DB::table('booking.rooms')
            ->where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->where('status', 'occupied')
            ->count();

        $occupancy = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;

        // 3. Check-ins agendados para hoje
        $checkins = DB::table('booking.reservations')
            ->where('hotel_id', $hotelId)
            ->where('check_in_date', today())
            ->where('reservation_status', '!=', 'canceled')
            ->count();

        // 4. Check-outs agendados para hoje
        $checkouts = DB::table('booking.reservations')
            ->where('hotel_id', $hotelId)
            ->where('check_out_date', today())
            ->where('reservation_status', '!=', 'canceled')
            ->count();

        // 5. Contagem de status dos quartos
        $roomStatusCounts = DB::table('booking.rooms')
            ->where('hotel_id', $hotelId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $roomStatus = [
            'available'   => $roomStatusCounts['available'] ?? 0,
            'occupied'    => $roomStatusCounts['occupied'] ?? 0,
            'cleaning'    => $roomStatusCounts['cleaning'] ?? 0,
            'maintenance' => $roomStatusCounts['maintenance'] ?? 0,
            'blocked'     => $roomStatusCounts['blocked'] ?? 0,
        ];

        // 6. Últimas 5 reservas criadas
        $recentReservations = DB::table('booking.reservations as r')
            ->join('crm.guests as g', 'g.id', '=', 'r.main_guest_id')
            ->where('r.hotel_id', $hotelId)
            ->orderByDesc('r.created_at')
            ->limit(5)
            ->select([
                'r.id',
                'r.locator_code',
                'r.reservation_status',
                'r.stay_status',
                'r.check_in_date',
                'r.check_out_date',
                'r.total_amount',
                'g.full_name as guest_name'
            ])
            ->get()
            ->toArray();

        // 7. Lista completa de quartos ativos para o mapa/rack do dashboard
        $roomsList = DB::table('booking.rooms as r')
            ->join('booking.room_types as rt', 'rt.id', '=', 'r.room_type_id')
            ->where('r.hotel_id', $hotelId)
            ->where('r.is_active', true)
            ->orderBy('r.number')
            ->select(['r.id', 'r.number', 'r.status', 'rt.name as type_name'])
            ->get()
            ->toArray();

        return [
            'revenueToday'       => $revenueToday,
            'occupancy'          => $occupancy,
            'checkins'           => $checkins,
            'checkouts'          => $checkouts,
            'roomStatus'         => $roomStatus,
            'recentReservations' => $recentReservations,
            'roomsList'          => $roomsList,
        ];
    }
}