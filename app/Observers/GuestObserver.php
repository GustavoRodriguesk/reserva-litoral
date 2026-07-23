<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Guest;
use Illuminate\Support\Facades\Auth;

class GuestObserver
{
    /**
     * Handle the Guest "created" event.
     */
    public function created(Guest $guest): void
    {
        $userId = Auth::id();
        $tenantId = Auth::user()?->tenant_id ?? $guest->tenant_id ?? 'e7b99c71-c068-45a2-83fe-4b149b0713b1';

        AuditLog::create([
            'tenant_id'   => $tenantId,
            'user_id'     => $userId,
            'action'      => 'create_guest',
            'entity_type' => 'Guest',
            'entity_id'   => $guest->id,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'metadata'    => [
                'full_name' => $guest->full_name,
                'email'     => $guest->email,
                'document'  => $guest->document_number,
            ],
        ]);
    }

    /**
     * Handle the Guest "updated" event.
     */
    public function updated(Guest $guest): void
    {
        $userId = Auth::id();
        $tenantId = Auth::user()?->tenant_id ?? $guest->tenant_id ?? 'e7b99c71-c068-45a2-83fe-4b149b0713b1';

        AuditLog::create([
            'tenant_id'   => $tenantId,
            'user_id'     => $userId,
            'action'      => 'update_guest',
            'entity_type' => 'Guest',
            'entity_id'   => $guest->id,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'metadata'    => [
                'full_name'       => $guest->full_name,
                'changed_fields' => array_keys($guest->getChanges()),
            ],
        ]);
    }

    /**
     * Handle the Guest "deleted" event.
     */
    public function deleted(Guest $guest): void
    {
        $userId = Auth::id();
        $tenantId = Auth::user()?->tenant_id ?? $guest->tenant_id ?? 'e7b99c71-c068-45a2-83fe-4b149b0713b1';

        AuditLog::create([
            'tenant_id'   => $tenantId,
            'user_id'     => $userId,
            'action'      => 'delete_guest',
            'entity_type' => 'Guest',
            'entity_id'   => $guest->id,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'metadata'    => [
                'full_name' => $guest->full_name,
            ],
        ]);
    }
}
