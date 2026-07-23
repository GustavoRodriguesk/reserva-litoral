<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Reservation;
use App\Models\ReservationEvent;
use Illuminate\Support\Facades\Auth;

class ReservationObserver
{
    /**
     * Handle the Reservation "created" event.
     */
    public function created(Reservation $reservation): void
    {
        $userId = Auth::id();
        $tenantId = Auth::user()?->tenant_id ?? $reservation->tenant_id ?? 'e7b99c71-c068-45a2-83fe-4b149b0713b1';

        // 1. Registrar evento na timeline da reserva
        ReservationEvent::create([
            'reservation_id' => $reservation->id,
            'event_type'     => 'created',
            'description'    => "Reserva #{$reservation->locator_code} criada no sistema.",
            'metadata'       => [
                'locator_code' => $reservation->locator_code,
                'total_amount' => $reservation->total_amount,
            ],
            'performed_by'   => $userId,
        ]);

        // 2. Registrar no log de auditoria do sistema
        AuditLog::create([
            'tenant_id'   => $tenantId,
            'user_id'     => $userId,
            'action'      => 'create_reservation',
            'entity_type' => 'Reservation',
            'entity_id'   => $reservation->id,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'metadata'    => [
                'locator_code' => $reservation->locator_code,
            ],
        ]);
    }

    /**
     * Handle the Reservation "updated" event.
     */
    public function updated(Reservation $reservation): void
    {
        $userId = Auth::id();
        $tenantId = Auth::user()?->tenant_id ?? $reservation->tenant_id ?? 'e7b99c71-c068-45a2-83fe-4b149b0713b1';

        // A. Alteração no stay_status (Check-in / Check-out)
        if ($reservation->isDirty('stay_status')) {
            $oldStay = $reservation->getOriginal('stay_status');
            $newStay = $reservation->stay_status;

            if ($newStay === 'checked_in') {
                // Alterar quartos associados para "Ocupado"
                $reservation->rooms()->update(['status' => 'occupied']);

                // Registrar Timeline
                ReservationEvent::create([
                    'reservation_id' => $reservation->id,
                    'event_type'     => 'check_in',
                    'description'    => "Check-in realizado para a reserva #{$reservation->locator_code}. Quarto(s) alterado(s) para 'Ocupado'.",
                    'performed_by'   => $userId,
                ]);

                // Registrar Auditoria
                AuditLog::create([
                    'tenant_id'   => $tenantId,
                    'user_id'     => $userId,
                    'action'      => 'check_in_reservation',
                    'entity_type' => 'Reservation',
                    'entity_id'   => $reservation->id,
                    'ip_address'  => request()->ip(),
                    'user_agent'  => request()->userAgent(),
                    'metadata'    => ['locator_code' => $reservation->locator_code],
                ]);
            } elseif ($newStay === 'checked_out') {
                // Alterar quartos associados para "Limpeza"
                $reservation->rooms()->update(['status' => 'cleaning']);

                // Registrar Timeline
                ReservationEvent::create([
                    'reservation_id' => $reservation->id,
                    'event_type'     => 'check_out',
                    'description'    => "Check-out realizado para a reserva #{$reservation->locator_code}. Quarto(s) alterado(s) para 'Limpeza'.",
                    'performed_by'   => $userId,
                ]);

                // Registrar Auditoria
                AuditLog::create([
                    'tenant_id'   => $tenantId,
                    'user_id'     => $userId,
                    'action'      => 'check_out_reservation',
                    'entity_type' => 'Reservation',
                    'entity_id'   => $reservation->id,
                    'ip_address'  => request()->ip(),
                    'user_agent'  => request()->userAgent(),
                    'metadata'    => ['locator_code' => $reservation->locator_code],
                ]);
            }
        }

        // B. Alteração no reservation_status (Confirmação / Cancelamento)
        if ($reservation->isDirty('reservation_status')) {
            $oldStatus = $reservation->getOriginal('reservation_status');
            $newStatus = $reservation->reservation_status;

            if ($newStatus === 'canceled') {
                // Se o quarto estava associado a esta reserva e não há outra ocupação ativa, liberar para "Disponível"
                $reservation->rooms()->where('status', 'occupied')->update(['status' => 'available']);

                // Registrar Timeline
                ReservationEvent::create([
                    'reservation_id' => $reservation->id,
                    'event_type'     => 'cancel',
                    'description'    => "Reserva #{$reservation->locator_code} cancelada no sistema.",
                    'performed_by'   => $userId,
                ]);

                // Registrar Auditoria
                AuditLog::create([
                    'tenant_id'   => $tenantId,
                    'user_id'     => $userId,
                    'action'      => 'cancel_reservation',
                    'entity_type' => 'Reservation',
                    'entity_id'   => $reservation->id,
                    'ip_address'  => request()->ip(),
                    'user_agent'  => request()->userAgent(),
                    'metadata'    => ['locator_code' => $reservation->locator_code],
                ]);
            } else {
                ReservationEvent::create([
                    'reservation_id' => $reservation->id,
                    'event_type'     => 'status_change',
                    'description'    => "Status da reserva #{$reservation->locator_code} alterado de '{$oldStatus}' para '{$newStatus}'.",
                    'performed_by'   => $userId,
                ]);
            }
        }
    }
}
