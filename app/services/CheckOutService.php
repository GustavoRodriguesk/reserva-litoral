<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckOutService
{
    /**
     * Executa o check-out completo com todas as regras de negócio.
     *
     * Regras:
     *  1. Reserva deve estar com stay_status = 'checked_in'
     *  2. Verificar saldo devedor — se houver saldo e block_on_balance = true, lança exceção
     *  3. Altera stay_status → 'checked_out'
     *  4. Altera status de todos os quartos da reserva → 'cleaning'
     *  5. Insere registro em booking.checkouts (horário, operador, observações)
     *  6. Registra evento na timeline da reserva
     *  7. Emite log de auditoria em iam.audit_logs
     */
    public function execute(Reservation $reservation, array $options = []): array
    {
        // --- Validações de negócio ---
        if ($reservation->stay_status !== 'checked_in') {
            throw ValidationException::withMessages([
                'stay_status' => 'Check-out não permitido: o hóspede não está hospedado.',
            ]);
        }

        // Calcula o saldo devedor
        $totalAmount = (float) $reservation->total_amount;
        $totalPaid   = (float) $reservation->payments()
            ->where('status', 'paid')
            ->sum('amount');
        $balance = max(0, $totalAmount - $totalPaid);

        // Impede check-out se houver saldo e a opção block_on_balance estiver ativa
        $blockOnBalance = $options['block_on_balance'] ?? true;
        if ($balance > 0 && $blockOnBalance) {
            throw ValidationException::withMessages([
                'balance' => vsprintf(
                    'Check-out bloqueado: há um saldo devedor de R$ %s. Quitte o valor antes de finalizar.',
                    [number_format($balance, 2, ',', '.')]
                ),
            ]);
        }

        $user = auth()->user();
        $now  = now();

        DB::transaction(function () use ($reservation, $user, $now, $options, $balance) {

            // 1. Registra (ou atualiza) o check-out formal — upsert por reservation_id (UNIQUE)
            DB::table('booking.checkouts')->updateOrInsert(
                ['reservation_id' => $reservation->id],
                [
                    'id'              => Str::uuid(),
                    'checked_out_by'  => $user->id,
                    'checked_out_at'  => $now,
                    'damage_report'   => $options['damage_report'] ?? null,
                    'extra_amount'    => $options['extra_amount'] ?? 0,
                    'notes'           => $options['notes'] ?? null,
                ]
            );

            // 2. Atualiza o status de estadia da reserva
            $reservation->update([
                'stay_status' => 'checked_out',
            ]);

            // 3. Marca os quartos para limpeza
            $reservation->load('rooms');
            foreach ($reservation->rooms as $room) {
                $room->update(['status' => 'cleaning']);
            }

            // 4. Evento na timeline da reserva
            $descricao = vsprintf(
                'Check-out realizado por %s às %s.',
                [$user->name, $now->format('H:i')]
            );

            if ($balance > 0) {
                $descricao .= ' Saldo devedor de R$ ' . number_format($balance, 2, ',', '.') . ' registrado (check-out forçado).';
            }

            if (!empty($options['damage_report'])) {
                $descricao .= ' Danos: ' . $options['damage_report'];
            }

            $reservation->events()->create([
                'event_type'   => 'checkout_performed',
                'description'  => $descricao,
                'performed_by' => $user->id,
                'performed_at' => $now,
            ]);

            // 5. Auditoria
            DB::table('iam.audit_logs')->insert([
                'id'          => Str::uuid(),
                'tenant_id'   => $user->tenant_id,
                'user_id'     => $user->id,
                'action'      => 'checkout_performed',
                'entity_type' => 'reservation',
                'entity_id'   => $reservation->id,
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
                'metadata'    => json_encode([
                    'reservation_id' => $reservation->id,
                    'locator_code'   => $reservation->locator_code,
                    'balance'        => $balance,
                    'damage_report'  => $options['damage_report'] ?? null,
                    'extra_amount'   => $options['extra_amount'] ?? 0,
                    'forced'         => $balance > 0,
                ]),
                'created_at'  => $now,
            ]);
        });

        return [
            'balance' => $balance,
            'forced'  => $balance > 0,
        ];
    }
}
