<?php

namespace App\Http\Controllers;

use App\Models\RatePlan;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RatePlanController extends Controller
{
    public function index()
    {
        $hotelId = auth()->user()->hotel_id;

        $roomTypes = RoomType::where('hotel_id', $hotelId)
            ->with(['ratePlans' => fn($q) => $q->orderByDesc('is_default')->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('rate-plans.index', compact('roomTypes'));
    }

    public function create()
    {
        $hotelId   = auth()->user()->hotel_id;
        $roomTypes = RoomType::where('hotel_id', $hotelId)->orderBy('name')->get();
        return view('rate-plans.create', compact('roomTypes'));
    }

    public function store(Request $request)
    {
        $hotelId = auth()->user()->hotel_id;

        $validated = $request->validate([
            'room_type_id'       => ['required', 'uuid'],
            'name'               => ['required', 'string', 'max:150'],
            'code'               => ['nullable', 'string', 'max:40'],
            'description'        => ['nullable', 'string'],
            'meal_plan'          => ['required', 'in:room_only,breakfast,half_board,full_board,all_inclusive'],
            'is_refundable'      => ['boolean'],
            'cancellation_policy'=> ['nullable', 'string'],
            'min_advance_days'   => ['required', 'integer', 'min:0'],
            'is_default'         => ['boolean'],
            'is_active'          => ['boolean'],
        ]);

        // Validate room_type belongs to this hotel
        $roomType = RoomType::where('id', $validated['room_type_id'])
            ->where('hotel_id', $hotelId)
            ->firstOrFail();

        // If marking as default, unmark the others
        if ($request->boolean('is_default')) {
            RatePlan::where('room_type_id', $roomType->id)->update(['is_default' => false]);
        }

        $validated['is_refundable']  = $request->boolean('is_refundable');
        $validated['is_default']     = $request->boolean('is_default');
        $validated['is_active']      = $request->boolean('is_active', true);

        RatePlan::create($validated);

        return redirect()->route('rate-plans.index')->with('success', 'Tarifário criado com sucesso!');
    }

    public function edit(RatePlan $ratePlan)
    {
        $hotelId   = auth()->user()->hotel_id;
        $roomTypes = RoomType::where('hotel_id', $hotelId)->orderBy('name')->get();
        return view('rate-plans.edit', compact('ratePlan', 'roomTypes'));
    }

    public function update(Request $request, RatePlan $ratePlan)
    {
        $hotelId = auth()->user()->hotel_id;

        $validated = $request->validate([
            'room_type_id'       => ['required', 'uuid'],
            'name'               => ['required', 'string', 'max:150'],
            'code'               => ['nullable', 'string', 'max:40'],
            'description'        => ['nullable', 'string'],
            'meal_plan'          => ['required', 'in:room_only,breakfast,half_board,full_board,all_inclusive'],
            'is_refundable'      => ['boolean'],
            'cancellation_policy'=> ['nullable', 'string'],
            'min_advance_days'   => ['required', 'integer', 'min:0'],
            'is_default'         => ['boolean'],
            'is_active'          => ['boolean'],
        ]);

        // If marking as default, unmark the others first
        if ($request->boolean('is_default')) {
            RatePlan::where('room_type_id', $validated['room_type_id'])
                ->where('id', '!=', $ratePlan->id)
                ->update(['is_default' => false]);
        }

        $validated['is_refundable'] = $request->boolean('is_refundable');
        $validated['is_default']    = $request->boolean('is_default');
        $validated['is_active']     = $request->boolean('is_active', true);

        $ratePlan->update($validated);

        return redirect()->route('rate-plans.index')->with('success', 'Tarifário atualizado com sucesso!');
    }

    public function destroy(RatePlan $ratePlan)
    {
        $ratePlan->delete();
        return redirect()->route('rate-plans.index')->with('success', 'Tarifário removido com sucesso!');
    }
}
