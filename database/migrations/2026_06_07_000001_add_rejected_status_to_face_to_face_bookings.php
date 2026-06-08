<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Add 'Rejected' as a valid status value for face_to_face_bookings.status.
     *
     * Valid status values: Pending, Confirmed, Completed, Cancelled, Rejected
     *
     * SQLite (used in development) stores status as a plain text column and
     * already accepts any string — no schema change is required.
     * For MySQL deployments, run the statement in the comment below.
     */
    public function up(): void
    {
        // MySQL only — uncomment if deploying to MySQL:
        // DB::statement("ALTER TABLE face_to_face_bookings MODIFY COLUMN status
        //     ENUM('Pending','Confirmed','Completed','Cancelled','Rejected') NOT NULL DEFAULT 'Pending'");
    }

    public function down(): void
    {
        // MySQL only — uncomment if rolling back on MySQL:
        // DB::statement("ALTER TABLE face_to_face_bookings MODIFY COLUMN status
        //     ENUM('Pending','Confirmed','Completed','Cancelled') NOT NULL DEFAULT 'Pending'");
    }
};
