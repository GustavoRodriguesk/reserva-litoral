<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HousekeepingController extends Controller
{
    /**
     * Display the housekeeping dashboard.
     */
    public function index(Request $request)
    {
        $query = Room::where('hotel_id', auth()->user()->hotel_id)
            ->with(['roomType']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        $rooms = $query->orderBy('number')->get();

        // Metrics for Housekeeping Overview
        $counts = [
            'total'       => $rooms->count(),
            'available'   => Room::where('hotel_id', auth()->user()->hotel_id)->where('status', 'available')->count(),
            'reserved'    => Room::where('hotel_id', auth()->user()->hotel_id)->where('status', 'reserved')->count(),
            'occupied'    => Room::where('hotel_id', auth()->user()->hotel_id)->where('status', 'occupied')->count(),
            'cleaning'    => Room::where('hotel_id', auth()->user()->hotel_id)->where('status', 'cleaning')->count(),
            'inspected'   => Room::where('hotel_id', auth()->user()->hotel_id)->where('status', 'inspected')->count(),
            'maintenance' => Room::where('hotel_id', auth()->user()->hotel_id)->where('status', 'maintenance')->count(),
            'blocked'     => Room::where('hotel_id', auth()->user()->hotel_id)->where('status', 'blocked')->count(),
        ];

        $roomTypes = RoomType::where('hotel_id', auth()->user()->hotel_id)->orderBy('name')->get();

        return view('housekeeping.index', compact('rooms', 'counts', 'roomTypes'));
    }

    /**
     * Update room status from housekeeping board.
     */
    public function updateStatus(Request $request, Room $room)
    {
        $request->validate([
            'status' => [
                'required',
                Rule::in(['available', 'reserved', 'occupied', 'cleaning', 'inspected', 'maintenance', 'blocked']),
            ],
        ]);

        $statusLabels = [
            'available'   => 'Livre',
            'reserved'    => 'Reservado',
            'occupied'    => 'Ocupado',
            'cleaning'    => 'Limpeza',
            'inspected'   => 'Inspecionado',
            'maintenance' => 'Manutenção',
            'blocked'     => 'Bloqueado',
        ];

        $oldStatus = $room->status;
        $room->update(['status' => $request->status]);

        $label = $statusLabels[$request->status] ?? $request->status;

        return redirect()->back()->with('success', "Status do Quarto #{$room->number} alterado para '{$label}' com sucesso!");
    }
}
