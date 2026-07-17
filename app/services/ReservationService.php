<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    public function create(array $data, User $user): Reservation
    {
        return DB::transaction(function () use ($data, $user) {
            $checkIn = Carbon::parse($data['check_in'])->startOfDay();
            $checkOut = Carbon::parse($data['check_out'])->startOfDay();

            $room = Room::query()
                ->with('roomType')
                ->lockForUpdate()
                ->where('id', $data['room_id'])
                ->where('hotel_id', $user->hotel_id)
                ->where('status', 'available')
                ->firstOrFail();

            $guest = Guest::query()
                ->where('id', $data['guest_id'])
                ->where('tenant_id', $user->tenant_id)
                ->firstOrFail();

            if ($room->roomType->max_capacity < ($data['adults'] + ($data['children'] ?? 0))) {
                throw ValidationException::withMessages([
                    'room_id' => 'O quarto selecionado não comporta a quantidade de hóspedes informada.',
                ]);
            }

            $hasConflict = DB::table('booking.reservation_rooms as reservation_rooms')
                ->join('booking.reservations as reservations', 'reservations.id', '=', 'reservation_rooms.reservation_id')
                ->where('reservation_rooms.room_id', $room->id)
                ->where('reservation_rooms.is_active', true)
                ->whereRaw("daterange(reservation_rooms.check_in_date, reservation_rooms.check_out_date, '[)') && daterange(?::date, ?::date, '[)')", [
                    $checkIn->toDateString(),
                    $checkOut->toDateString(),
                ])
                ->exists();

            if ($hasConflict) {
                throw ValidationException::withMessages([
                    'room_id' => 'Este quarto não está mais disponível para o período selecionado.',
                ]);
            }

            $nights = $checkIn->diffInDays($checkOut);
            $ratePerNight = (float) $room->roomType->base_price;
            $subtotalAmount = round($ratePerNight * $nights, 2);

            // Calcula valores dos extras
            $extrasAmount = 0;
            $extras = $data['extras'] ?? [];
            $chargesToInsert = [];

            if (in_array('cafe', $extras)) {
                $qty = $nights * $data['adults'];
                $val = 40.00;
                $tot = $qty * $val;
                $extrasAmount += $tot;
                $chargesToInsert[] = [
                    'charge_type' => 'servico_extra',
                    'description' => 'Café da manhã',
                    'quantity' => $qty,
                    'unit_amount' => $val,
                    'total_amount' => $tot,
                ];
            }
            if (in_array('estacionamento', $extras)) {
                $qty = $nights;
                $val = 30.00;
                $tot = $qty * $val;
                $extrasAmount += $tot;
                $chargesToInsert[] = [
                    'charge_type' => 'servico_extra',
                    'description' => 'Estacionamento',
                    'quantity' => $qty,
                    'unit_amount' => $val,
                    'total_amount' => $tot,
                ];
            }
            if (in_array('berco', $extras)) {
                $qty = 1;
                $val = 50.00;
                $tot = $qty * $val;
                $extrasAmount += $tot;
                $chargesToInsert[] = [
                    'charge_type' => 'servico_extra',
                    'description' => 'Aluguel de Berço',
                    'quantity' => $qty,
                    'unit_amount' => $val,
                    'total_amount' => $tot,
                ];
            }
            if (in_array('pet', $extras)) {
                $qty = 1;
                $val = 80.00;
                $tot = $qty * $val;
                $extrasAmount += $tot;
                $chargesToInsert[] = [
                    'charge_type' => 'servico_extra',
                    'description' => 'Taxa de Pet',
                    'quantity' => $qty,
                    'unit_amount' => $val,
                    'total_amount' => $tot,
                ];
            }
            if (in_array('cama_extra', $extras)) {
                $qty = 1;
                $val = 120.00;
                $tot = $qty * $val;
                $extrasAmount += $tot;
                $chargesToInsert[] = [
                    'charge_type' => 'servico_extra',
                    'description' => 'Cama extra',
                    'quantity' => $qty,
                    'unit_amount' => $val,
                    'total_amount' => $tot,
                ];
            }

            $grandTotal = $subtotalAmount + $extrasAmount;

            $reservation = Reservation::create([
                'hotel_id' => $user->hotel_id,
                'locator_code' => $this->newLocatorCode($user->hotel_id),
                'main_guest_id' => $guest->id,
                'reservation_status' => 'confirmed',
                'stay_status' => 'awaiting_checkin',
                'channel' => 'direct',
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'adults' => $data['adults'],
                'children' => $data['children'] ?? 0,
                'currency' => 'BRL',
                'total_amount' => $grandTotal,
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            // Insere quarto na reserva
            DB::table('booking.reservation_rooms')->insert([
                'id' => \Illuminate\Support\Str::uuid(),
                'reservation_id' => $reservation->id,
                'room_id' => $room->id,
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'rate_per_night' => $ratePerNight,
            ]);

            // Insere hóspede na reserva
            DB::table('booking.reservation_guests')->insert([
                'reservation_id' => $reservation->id,
                'guest_id' => $guest->id,
                'is_main' => true,
            ]);

            // Insere cobrança de diária
            DB::table('booking.reservation_charges')->insert([
                'id' => \Illuminate\Support\Str::uuid(),
                'reservation_id' => $reservation->id,
                'charge_type' => 'diaria',
                'description' => "Diárias ({$nights} noites no Quarto {$room->number})",
                'quantity' => $nights,
                'unit_amount' => $ratePerNight,
                'total_amount' => $subtotalAmount,
                'is_discount' => false,
                'created_at' => now(),
            ]);

            // Insere cobranças dos extras
            foreach ($chargesToInsert as $charge) {
                DB::table('booking.reservation_charges')->insert(array_merge($charge, [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'reservation_id' => $reservation->id,
                    'is_discount' => false,
                    'created_at' => now(),
                ]));
            }

            // Registra Pagamento se não for pendente
            $paymentMethod = $data['payment_method'] ?? 'pending';
            if ($paymentMethod !== 'pending') {
                DB::table('finance.payments')->insert([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'reservation_id' => $reservation->id,
                    'amount' => $grandTotal,
                    'currency' => 'BRL',
                    'status' => 'paid',
                    'paid_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $methodLabels = [
                    'pix' => 'PIX',
                    'credit_card' => 'Cartão de Crédito',
                    'cash' => 'Dinheiro',
                ];
                $methodLabel = $methodLabels[$paymentMethod] ?? $paymentMethod;

                DB::table('booking.reservation_events')->insert([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'reservation_id' => $reservation->id,
                    'event_type' => 'payment_received',
                    'description' => "Pagamento integral recebido via {$methodLabel} (R$ " . number_format($grandTotal, 2, ',', '.') . ")",
                    'performed_by' => $user->id,
                    'performed_at' => now(),
                ]);
            }

            // Registra evento de criação
            DB::table('booking.reservation_events')->insert([
                'id' => \Illuminate\Support\Str::uuid(),
                'reservation_id' => $reservation->id,
                'event_type' => 'reservation_created',
                'description' => 'Reserva criada.',
                'metadata' => json_encode([
                    'room_id' => $room->id,
                    'nights' => $nights,
                    'total_amount' => $grandTotal,
                ]),
                'performed_by' => $user->id,
                'performed_at' => now(),
            ]);

            return $reservation;
        });
    }

    private function newLocatorCode(string $hotelId): string
    {
        do {
            $locator = 'RL'.strtoupper(str()->random(8));
        } while (Reservation::query()
            ->where('hotel_id', $hotelId)
            ->where('locator_code', $locator)
            ->exists());

        return $locator;
    }
}
