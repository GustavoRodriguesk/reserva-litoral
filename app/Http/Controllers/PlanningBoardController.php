<?php

namespace App\Http\Controllers;

use App\Services\PlanningBoardService;
use App\Models\RoomType;
use App\Models\ReservationRoom;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlanningBoardController extends Controller
{
    public function __invoke(
        Request $request,
        PlanningBoardService $planningBoardService
    ) {
        $startDate = $request->filled('start')
            ? Carbon::parse($request->start)->startOfDay()
            : now()->startOfWeek();

        // Número de dias a serem exibidos. Padrão 7 dias (uma semana)
        $days = (int) $request->integer('days', 7);
        $roomTypeId = $request->input('room_type_id');

        $planning = $planningBoardService->build(
            $startDate,
            $days,
            $roomTypeId
        );

        $roomTypes = RoomType::query()
            ->orderBy('name')
            ->get();

        return view('planning.index', [
            'planning' => $planning,
            'startDate' => $startDate,
            'endDate' => $startDate->copy()->addDays($days - 1),
            'days' => $days,
            'roomTypes' => $roomTypes,
            'selectedRoomTypeId' => $roomTypeId,
        ]);
    }

    /**
     * Processa a reatribuição de quarto e/ou datas via AJAX (Drag & Drop).
     */
    public function reallocate(Request $request)
    {
        $validated = $request->validate([
            'reservation_room_id' => ['required', 'uuid'],
            'new_room_id' => ['required', 'uuid'],
            'new_check_in' => ['required', 'date'],
            'new_check_out' => ['required', 'date', 'after:new_check_in'],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Recupera a alocação específica
                $allocation = ReservationRoom::query()
                    ->whereKey($validated['reservation_room_id'])
                    ->firstOrFail();

                $reservation = $allocation->reservation;

                // Garante que a reserva pertença ao hotel do usuário ativo (segurança RLS)
                if ($reservation->hotel_id !== auth()->user()->hotel_id) {
                    throw new \Exception('Acesso não autorizado a esta reserva.');
                }

                $newCheckIn = Carbon::parse($validated['new_check_in'])->startOfDay();
                $newCheckOut = Carbon::parse($validated['new_check_out'])->startOfDay();
                $nights = $newCheckIn->diffInDays($newCheckOut);

                // 1. Atualiza a alocação (reservation_rooms)
                $allocation->update([
                    'room_id' => $validated['new_room_id'],
                    'check_in_date' => $newCheckIn->toDateString(),
                    'check_out_date' => $newCheckOut->toDateString(),
                ]);

                // 2. Atualiza as datas na reserva pai (reservations)
                $reservation->update([
                    'check_in_date' => $newCheckIn->toDateString(),
                    'check_out_date' => $newCheckOut->toDateString(),
                ]);

                // 3. Atualiza a quantidade e o total da cobrança da diária principal
                $dailyCharge = $reservation->charges()
                    ->where('charge_type', 'diaria')
                    ->first();

                if ($dailyCharge) {
                    $newTotal = round($dailyCharge->unit_amount * $nights, 2);
                    $dailyCharge->update([
                        'quantity' => $nights,
                        'total_amount' => $newTotal,
                    ]);
                }

                // 4. Recalcula o total geral da reserva
                $this->recalculateTotal($reservation);

                // 5. Registra o evento de reatribuição na timeline da reserva
                $reservation->events()->create([
                    'event_type' => 'room_change',
                    'description' => "Reserva remanejada no planejamento para o Quarto {$allocation->room->number} de {$newCheckIn->format('d/m')} a {$newCheckOut->format('d/m')}.",
                    'performed_by' => auth()->id(),
                    'performed_at' => now(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Reserva remanejada com sucesso.',
            ]);

        } catch (\Exception $e) {
            // Se for erro de constraint de exclusão (overbooking), o handler irá converter,
            // mas caso chegue aqui, retornamos um JSON de erro adequado.
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível reatribuir: o quarto já está ocupado ou indisponível no período solicitado.',
            ], 422);
        }
    }

    private function recalculateTotal(Reservation $reservation)
    {
        $charges = $reservation->charges()->get();
        $subtotal = 0;
        $discounts = 0;

        foreach ($charges as $charge) {
            if ($charge->is_discount) {
                $discounts += (float) $charge->total_amount;
            } else {
                $subtotal += (float) $charge->total_amount;
            }
        }

        $total = max(0, $subtotal - $discounts);

        $reservation->update([
            'total_amount' => $total,
        ]);
    }
}