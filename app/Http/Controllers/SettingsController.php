<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $hotel = Hotel::find(auth()->user()->hotel_id);

        $settings = DB::table('core.hotel_settings')
            ->where('hotel_id', $hotel->id)
            ->first();

        return view('settings.index', compact('hotel', 'settings'));
    }

    public function updateHotel(Request $request)
    {
        $hotel = Hotel::find(auth()->user()->hotel_id);

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'legal_name'     => ['nullable', 'string', 'max:255'],
            'document_number'=> ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'address_line'   => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:120'],
            'state'          => ['nullable', 'string', 'max:120'],
            'postal_code'    => ['nullable', 'string', 'max:20'],
        ]);

        $hotel->update($validated);

        return redirect()->route('settings.index')->with('success', 'Dados da pousada atualizados com sucesso!');
    }

    public function updateSettings(Request $request)
    {
        $hotel = Hotel::find(auth()->user()->hotel_id);

        $validated = $request->validate([
            'checkin_time'              => ['required', 'date_format:H:i'],
            'checkout_time'             => ['required', 'date_format:H:i'],
            'cancellation_policy'       => ['nullable', 'string'],
            'overbooking_allowed'       => ['boolean'],
            'auto_confirm_reservations' => ['boolean'],
        ]);

        $existing = DB::table('core.hotel_settings')->where('hotel_id', $hotel->id)->exists();

        if ($existing) {
            DB::table('core.hotel_settings')
                ->where('hotel_id', $hotel->id)
                ->update([
                    'checkin_time'              => $validated['checkin_time'],
                    'checkout_time'             => $validated['checkout_time'],
                    'cancellation_policy'       => $validated['cancellation_policy'] ?? null,
                    'overbooking_allowed'       => $request->boolean('overbooking_allowed'),
                    'auto_confirm_reservations' => $request->boolean('auto_confirm_reservations'),
                    'updated_at'                => now(),
                ]);
        } else {
            DB::table('core.hotel_settings')->insert([
                'hotel_id'                  => $hotel->id,
                'checkin_time'              => $validated['checkin_time'],
                'checkout_time'             => $validated['checkout_time'],
                'cancellation_policy'       => $validated['cancellation_policy'] ?? null,
                'overbooking_allowed'       => $request->boolean('overbooking_allowed'),
                'auto_confirm_reservations' => $request->boolean('auto_confirm_reservations'),
                'settings_json'             => '{}',
                'updated_at'                => now(),
            ]);
        }

        return redirect()->route('settings.index')->with('success', 'Configurações de operação atualizadas!');
    }
}
