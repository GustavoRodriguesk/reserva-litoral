<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Landing page — shows the index with filters and triggers either
     * the occupancy or the financial report based on query params.
     */
    public function index(Request $request)
    {
        $type      = $request->input('type', 'occupancy');
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfMonth()->toDateString());

        $data = $type === 'financial'
            ? $this->buildFinancialReport($startDate, $endDate)
            : $this->buildOccupancyReport($startDate, $endDate);

        return view('reports.index', array_merge([
            'type'      => $type,
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ], $data));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // OCCUPANCY REPORT
    // ─────────────────────────────────────────────────────────────────────────
    private function buildOccupancyReport(string $startDate, string $endDate): array
    {
        $hotelId = auth()->user()->hotel_id;

        $totalRooms = DB::table('booking.rooms')
            ->join('booking.room_types', 'booking.rooms.room_type_id', '=', 'booking.room_types.id')
            ->where('booking.room_types.hotel_id', $hotelId)
            ->whereNull('booking.rooms.deleted_at')
            ->count();

        $start = Carbon::parse($startDate);
        $end   = Carbon::parse($endDate);
        $totalNights = $start->diffInDays($end) ?: 1;
        $totalRoomNights = $totalRooms * $totalNights;

        // Nights actually occupied
        $occupiedNights = DB::table('booking.reservation_rooms as rr')
            ->join('booking.reservations as r', 'r.id', '=', 'rr.reservation_id')
            ->join('booking.rooms', 'booking.rooms.id', '=', 'rr.room_id')
            ->join('booking.room_types', 'booking.room_types.id', '=', 'booking.rooms.room_type_id')
            ->where('booking.room_types.hotel_id', $hotelId)
            ->whereNotIn('r.reservation_status', ['cancelled'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('r.check_in_date', '<', $endDate)
                  ->where('r.check_out_date', '>', $startDate);
            })
            ->selectRaw("
                SUM(
                    GREATEST(0, EXTRACT(DAY FROM (
                        LEAST(r.check_out_date, ?::date) - GREATEST(r.check_in_date, ?::date)
                    )))
                ) as nights
            ", [$endDate, $startDate])
            ->value('nights') ?? 0;

        $occupancyRate = $totalRoomNights > 0
            ? round(($occupiedNights / $totalRoomNights) * 100, 1)
            : 0;

        // Total revenue in period
        $revenue = DB::table('booking.reservations as r')
            ->join('booking.room_types', function ($join) use ($hotelId) {
                $join->on('1', '=', '1'); // will filter via subquery
            })
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('r.check_in_date', '<', $endDate)
                  ->where('r.check_out_date', '>', $startDate);
            })
            ->whereNotIn('r.reservation_status', ['cancelled'])
            ->whereExists(function ($q) use ($hotelId) {
                $q->select(DB::raw(1))
                  ->from('booking.reservation_rooms as rr2')
                  ->join('booking.rooms as rm2', 'rm2.id', '=', 'rr2.room_id')
                  ->join('booking.room_types as rt2', 'rt2.id', '=', 'rm2.room_type_id')
                  ->whereColumn('rr2.reservation_id', 'r.id')
                  ->where('rt2.hotel_id', $hotelId);
            })
            ->sum('r.total_amount');

        // ADR = Average Daily Rate
        $adr = $occupiedNights > 0 ? round($revenue / $occupiedNights, 2) : 0;

        // RevPAR = Revenue Per Available Room
        $revpar = $totalRoomNights > 0 ? round($revenue / $totalRoomNights, 2) : 0;

        // Reservations per day breakdown
        $dailyData = DB::table('booking.reservations as r')
            ->whereNotIn('r.reservation_status', ['cancelled'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('r.check_in_date', '<', $endDate)
                  ->where('r.check_out_date', '>', $startDate);
            })
            ->whereExists(function ($q) use ($hotelId) {
                $q->select(DB::raw(1))
                  ->from('booking.reservation_rooms as rr2')
                  ->join('booking.rooms as rm2', 'rm2.id', '=', 'rr2.room_id')
                  ->join('booking.room_types as rt2', 'rt2.id', '=', 'rm2.room_type_id')
                  ->whereColumn('rr2.reservation_id', 'r.id')
                  ->where('rt2.hotel_id', $hotelId);
            })
            ->selectRaw("DATE(r.check_in_date) as date, COUNT(*) as checkins")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Per room-type occupancy
        $byRoomType = DB::table('booking.room_types as rt')
            ->where('rt.hotel_id', $hotelId)
            ->leftJoin('booking.rooms as rm', function ($join) {
                $join->on('rm.room_type_id', '=', 'rt.id')
                     ->whereNull('rm.deleted_at');
            })
            ->leftJoin('booking.reservation_rooms as rr', function ($join) {
                $join->on('rr.room_id', '=', 'rm.id');
            })
            ->leftJoin('booking.reservations as r', function ($join) use ($startDate, $endDate) {
                $join->on('r.id', '=', 'rr.reservation_id')
                     ->where('r.check_in_date', '<', $endDate)
                     ->where('r.check_out_date', '>', $startDate)
                     ->whereNotIn('r.reservation_status', ['cancelled']);
            })
            ->selectRaw("
                rt.name,
                COUNT(DISTINCT rm.id) as total_rooms,
                COUNT(DISTINCT r.id) as reservations,
                COALESCE(SUM(r.total_amount), 0) as revenue
            ")
            ->groupBy('rt.id', 'rt.name')
            ->get();

        return compact(
            'totalRooms', 'totalNights', 'totalRoomNights',
            'occupiedNights', 'occupancyRate',
            'revenue', 'adr', 'revpar',
            'dailyData', 'byRoomType'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FINANCIAL REPORT
    // ─────────────────────────────────────────────────────────────────────────
    private function buildFinancialReport(string $startDate, string $endDate): array
    {
        $hotelId = auth()->user()->hotel_id;

        // Revenue from reservations
        $reservationRevenue = DB::table('booking.reservations as r')
            ->whereNotIn('r.reservation_status', ['cancelled'])
            ->whereBetween('r.check_in_date', [$startDate, $endDate])
            ->whereExists(function ($q) use ($hotelId) {
                $q->select(DB::raw(1))
                  ->from('booking.reservation_rooms as rr2')
                  ->join('booking.rooms as rm2', 'rm2.id', '=', 'rr2.room_id')
                  ->join('booking.room_types as rt2', 'rt2.id', '=', 'rm2.room_type_id')
                  ->whereColumn('rr2.reservation_id', 'r.id')
                  ->where('rt2.hotel_id', $hotelId);
            })
            ->sum('r.total_amount');

        // Charges (extras)
        $extraCharges = DB::table('booking.reservation_charges as rc')
            ->join('booking.reservations as r', 'r.id', '=', 'rc.reservation_id')
            ->whereBetween('rc.charged_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('rc.is_voided', false)
            ->whereExists(function ($q) use ($hotelId) {
                $q->select(DB::raw(1))
                  ->from('booking.reservation_rooms as rr2')
                  ->join('booking.rooms as rm2', 'rm2.id', '=', 'rr2.room_id')
                  ->join('booking.room_types as rt2', 'rt2.id', '=', 'rm2.room_type_id')
                  ->whereColumn('rr2.reservation_id', 'r.id')
                  ->where('rt2.hotel_id', $hotelId);
            })
            ->sum('rc.amount');

        // Payments received
        $paymentsReceived = DB::table('booking.payments as p')
            ->join('booking.reservations as r', 'r.id', '=', 'p.reservation_id')
            ->where('p.status', 'confirmed')
            ->whereBetween('p.paid_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereExists(function ($q) use ($hotelId) {
                $q->select(DB::raw(1))
                  ->from('booking.reservation_rooms as rr2')
                  ->join('booking.rooms as rm2', 'rm2.id', '=', 'rr2.room_id')
                  ->join('booking.room_types as rt2', 'rt2.id', '=', 'rm2.room_type_id')
                  ->whereColumn('rr2.reservation_id', 'r.id')
                  ->where('rt2.hotel_id', $hotelId);
            })
            ->sum('p.amount');

        $totalRevenue    = $reservationRevenue + $extraCharges;
        $pendingBalance  = $totalRevenue - $paymentsReceived;

        // Revenue by payment method
        $byPaymentMethod = DB::table('booking.payments as p')
            ->join('booking.reservations as r', 'r.id', '=', 'p.reservation_id')
            ->where('p.status', 'confirmed')
            ->whereBetween('p.paid_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereExists(function ($q) use ($hotelId) {
                $q->select(DB::raw(1))
                  ->from('booking.reservation_rooms as rr2')
                  ->join('booking.rooms as rm2', 'rm2.id', '=', 'rr2.room_id')
                  ->join('booking.room_types as rt2', 'rt2.id', '=', 'rm2.room_type_id')
                  ->whereColumn('rr2.reservation_id', 'r.id')
                  ->where('rt2.hotel_id', $hotelId);
            })
            ->selectRaw('p.method, SUM(p.amount) as total')
            ->groupBy('p.method')
            ->get();

        // Reservations count breakdown
        $reservationStats = DB::table('booking.reservations as r')
            ->whereBetween('r.check_in_date', [$startDate, $endDate])
            ->whereExists(function ($q) use ($hotelId) {
                $q->select(DB::raw(1))
                  ->from('booking.reservation_rooms as rr2')
                  ->join('booking.rooms as rm2', 'rm2.id', '=', 'rr2.room_id')
                  ->join('booking.room_types as rt2', 'rt2.id', '=', 'rm2.room_type_id')
                  ->whereColumn('rr2.reservation_id', 'r.id')
                  ->where('rt2.hotel_id', $hotelId);
            })
            ->selectRaw('reservation_status, COUNT(*) as count')
            ->groupBy('reservation_status')
            ->get()
            ->keyBy('reservation_status');

        // Monthly revenue trend (last 6 months)
        $monthlyTrend = DB::table('booking.reservations as r')
            ->whereNotIn('r.reservation_status', ['cancelled'])
            ->whereExists(function ($q) use ($hotelId) {
                $q->select(DB::raw(1))
                  ->from('booking.reservation_rooms as rr2')
                  ->join('booking.rooms as rm2', 'rm2.id', '=', 'rr2.room_id')
                  ->join('booking.room_types as rt2', 'rt2.id', '=', 'rm2.room_type_id')
                  ->whereColumn('rr2.reservation_id', 'r.id')
                  ->where('rt2.hotel_id', $hotelId);
            })
            ->where('r.check_in_date', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("TO_CHAR(r.check_in_date, 'YYYY-MM') as month, SUM(r.total_amount) as revenue, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return compact(
            'reservationRevenue', 'extraCharges', 'paymentsReceived',
            'totalRevenue', 'pendingBalance',
            'byPaymentMethod', 'reservationStats', 'monthlyTrend'
        );
    }
}
