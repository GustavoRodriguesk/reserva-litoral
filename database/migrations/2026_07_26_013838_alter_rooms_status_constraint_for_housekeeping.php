<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing constraint
        DB::statement('ALTER TABLE booking.rooms DROP CONSTRAINT IF EXISTS rooms_status_check');
        
        // Add the new constraint with 'reserved' and 'inspected' included
        DB::statement("
            ALTER TABLE booking.rooms 
            ADD CONSTRAINT rooms_status_check 
            CHECK (status::text = ANY (ARRAY[
                'available'::character varying, 
                'reserved'::character varying,
                'occupied'::character varying, 
                'cleaning'::character varying, 
                'inspected'::character varying,
                'maintenance'::character varying, 
                'blocked'::character varying
            ]::text[]))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new constraint
        DB::statement('ALTER TABLE booking.rooms DROP CONSTRAINT IF EXISTS rooms_status_check');
        
        // At this point, we need to revert statuses that might violate the old constraint back to 'available'
        DB::statement("UPDATE booking.rooms SET status = 'available' WHERE status IN ('reserved', 'inspected')");

        // Restore the old constraint
        DB::statement("
            ALTER TABLE booking.rooms 
            ADD CONSTRAINT rooms_status_check 
            CHECK (status::text = ANY (ARRAY[
                'available'::character varying, 
                'occupied'::character varying, 
                'cleaning'::character varying, 
                'maintenance'::character varying, 
                'blocked'::character varying
            ]::text[]))
        ");
    }
};
