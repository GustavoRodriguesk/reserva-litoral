<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckInService
{
    /**
     * Executa o check-in completo com todas as regras de negócio.
     *
     * Regras:
     *  1. Reserva deve estar com stay_status = 'awaiting_checkin'
     *  2. Reserva não pode estar cancelada ou no-show
     *  3. Altera stay_status → 'checked_in'
     *  4. Altera status de todos os quartos da reserva → 'occupied'
     *  5. Insere registro em booking.checkins (horário, operador, docs verificados)
     *  6. Registra evento na timeline da reserva
     *  7. Emite log de auditoria em iam.audit_logs
     */
    public function execute(Reservation $reservation, array $options = []): void
    {
        // --- Validações de negócio ---
        if ($reservation->stay_status !== 'awaiting_checkin') {
            throw ValidationException::withMessages([
                'stay_status' => 'Check-in não permitido: a reserva já foi realizada ou está em outro estado.',
            ]);
        }

        if (in_array($reservation->reservation_status, ['canceled', 'no_show', 'refunded'])) {
            throw ValidationException::withMessages([
                'reservation_status' => 'Check-in não permitido: reserva ' . $reservation->reservation_status . '.',
            ]);
        }

        $user = auth()->user();
        $now  = now();

        DB::transaction(function () use ($reservation, $user, $now, $options) {

            // 1. Registra (ou atualiza) o check-in formal — upsert por reservation_id (UNIQUE)
            DB::table('booking.checkins')->updateOrInsert(
                ['reservation_id' => $reservation->id],
                [
                    'id'                => Str::uuid(),
                    'checked_in_by'     => $user->id,
                    'checked_in_at'     => $now,
                    'document_verified' => $options['document_verified'] ?? true,
                    'notes'             => $options['notes'] ?? null,
                ]
            );

            // 2. Atualiza o status de estadia da reserva
            $reservation->update([
                'stay_status' => 'checked_in',
            ]);

            // 3. Marca todos os quartos vinculados como ocupados
            $reservation->load('rooms');
            foreach ($reservation->rooms as $room) {
                $room->update(['status' => 'occupied']);
            }

            // 4. Evento na timeline da reserva
            $reservation->events()->create([
                'event_type'   => 'checkin_performed',
                'description'  => vsprintf(
                    'Check-in realizado por %s às %s. Documento verificado: %s.',
                    [
                        $user->name,
                        $now->format('H:i'),
                        $options['document_verified'] ?? true ? 'Sim' : 'Não',
                    ]
                ),
                'performed_by' => $user->id,
                'performed_at' => $now,
            ]);

            // 5. Auditoria
            DB::table('iam.audit_logs')->insert([
                'id'          => Str::uuid(),
                'tenant_id'   => $user->tenant_id,
                'user_id'     => $user->id,
                'action'      => 'checkin_performed',
                'entity_type' => 'reservation',
                'entity_id'   => $reservation->id,
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
                'metadata'    => json_encode([
                    'reservation_id'    => $reservation->id,
                    'locator_code'      => $reservation->locator_code,
                    'document_verified' => $options['document_verified'] ?? true,
                    'notes'             => $options['notes'] ?? null,
                ]),
                'created_at'  => $now,
            ]);
        });
    }
}
