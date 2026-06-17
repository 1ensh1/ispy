<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            DO \$\$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM pg_constraint
                    WHERE conname = 'chk_bookings_status'
                ) THEN
                    ALTER TABLE face_to_face_bookings
                        DROP CONSTRAINT chk_bookings_status;
                END IF;

                ALTER TABLE face_to_face_bookings
                    ADD CONSTRAINT chk_bookings_status
                    CHECK (status IN ('Pending', 'Confirmed', 'Completed', 'Cancelled', 'Rejected', 'No-show'));
            END
            \$\$;
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE face_to_face_bookings
                DROP CONSTRAINT IF EXISTS chk_bookings_status;
        ");
    }
};
