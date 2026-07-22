<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $query = Room::with('roomType')->orderBy('number');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }
        if ($request->filled('search')) {
            $query->where('number', 'ilike', '%' . $request->search . '%');
        }

        $rooms     = $query->paginate(24)->withQueryString();
        $roomTypes = RoomType::orderBy('name')->get();

        return view('rooms.index', compact('rooms', 'roomTypes'));
    }

    public function create(): View
    {
        $roomTypes = RoomType::orderBy('name')->get();
        $hotelId   = auth()->user()->hotel_id;

        return view('rooms.create', compact('roomTypes', 'hotelId'));
    }

    public function store(StoreRoomRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['hotel_id'] = auth()->user()->hotel_id;

        $room = Room::create($data);

        return redirect()
            ->route('rooms.show', $room)
            ->with('success', 'Quarto cadastrado com sucesso.');
    }

    public function show(Room $room): View
    {
        $room->load([
            'hotel',
            'roomType',
            'reservations.guest',
            'reservations.payments',
        ]);

        $currentReservation = $room->reservations
            ->firstWhere('stay_status', 'checked_in');

        $nextReservation = $room->reservations
            ->whereIn('reservation_status', ['confirmed', 'pending'])
            ->where('stay_status', 'awaiting_checkin')
            ->sortBy('check_in_date')
            ->first();

        return view('rooms.show', compact(
            'room',
            'currentReservation',
            'nextReservation'
        ));
    }

    public function edit(Room $room): View
    {
        $room->load('roomType');
        $roomTypes = RoomType::orderBy('name')->get();

        return view('rooms.edit', compact('room', 'roomTypes'));
    }

    public function update(UpdateRoomRequest $request, Room $room): RedirectResponse
    {
        $room->update($request->validated());

        return redirect()
            ->route('rooms.show', $room)
            ->with('success', 'Quarto atualizado com sucesso.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $room->delete();

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Quarto removido com sucesso.');
    }

    public function block(Room $room): RedirectResponse
    {
        if ($room->status === 'occupied') {
            return back()->with('error', 'Não é possível bloquear um quarto ocupado.');
        }

        $room->update(['status' => 'blocked']);

        return back()->with('success', 'Quarto bloqueado.');
    }

    public function unblock(Room $room): RedirectResponse
    {
        if ($room->status !== 'blocked') {
            return back()->with('error', 'O quarto não está bloqueado.');
        }

        $room->update(['status' => 'available']);

        return back()->with('success', 'Quarto desbloqueado e disponível.');
    }
}