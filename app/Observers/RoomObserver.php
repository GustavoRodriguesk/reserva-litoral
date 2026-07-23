<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;

class RoomObserver
{
    /**
     * Handle the Room "created" event.
     */
    public function created(Room $room): void
    {
        $userId = Auth::id();
        $tenantId = Auth::user()?->tenant_id ?? 'e7b99c71-c068-45a2-83fe-4b149b0713b1';

        AuditLog::create([
            'tenant_id'   => $tenantId,
            'user_id'     => $userId,
            'action'      => 'create_room',
            'entity_type' => 'Room',
            'entity_id'   => $room->id,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'metadata'    => [
                'number' => $room->number,
                'floor'  => $room->floor,
                'status' => $room->status,
            ],
        ]);
    }

    /**
     * Handle the Room "updated" event.
     */
    public function updated(Room $room): void
    {
        $userId = Auth::id();
        $tenantId = Auth::user()?->tenant_id ?? 'e7b99c71-c068-45a2-83fe-4b149b0713b1';

        if ($room->isDirty('status')) {
            $oldStatus = $room->getOriginal('status');
            $newStatus = $room->status;

            AuditLog::create([
                'tenant_id'   => $tenantId,
                'user_id'     => $userId,
                'action'      => 'update_room_status',
                'entity_type' => 'Room',
                'entity_id'   => $room->id,
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
                'metadata'    => [
                    'number'     => $room->number,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ],
            ]);
        }
    }

    /**
     * Handle the Room "deleted" event.
     */
    public function deleted(Room $room): void
    {
        $userId = Auth::id();
        $tenantId = Auth::user()?->tenant_id ?? 'e7b99c71-c068-45a2-83fe-4b149b0713b1';

        AuditLog::create([
            'tenant_id'   => $tenantId,
            'user_id'     => $userId,
            'action'      => 'delete_room',
            'entity_type' => 'Room',
            'entity_id'   => $room->id,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'metadata'    => [
                'number' => $room->number,
            ],
        ]);
    }
}
