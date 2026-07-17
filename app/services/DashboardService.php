<?php

namespace App\Services;

class DashboardService
{
    public function data(): array
    {
        return [

            'revenueToday' => 0,

            'occupancy' => 0,

            'checkins' => 0,

            'checkouts' => 0,

            'roomStatus' => [],

            'recentReservations' => [],

        ];
    }
}