<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Allow slot_id to be null (required for ON DELETE SET NULL)
        \DB::statement('ALTER TABLE face_to_face_bookings ALTER COLUMN slot_id DROP NOT NULL');

        // Drop the existing CASCADE foreign key
        \DB::statement('ALTER TABLE face_to_face_bookings DROP CONSTRAINT face_to_face_bookings_slot_id_foreign');

        // Re-add with SET NULL so deleting a slot nullifies slot_id on the booking, preserving the booking record
        \DB::statement('ALTER TABLE face_to_face_bookings ADD CONSTRAINT face_to_face_bookings_slot_id_foreign
            FOREIGN KEY (slot_id) REFERENCES consultation_slots(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        // Remove SET NULL constraint
        \DB::statement('ALTER TABLE face_to_face_bookings DROP CONSTRAINT face_to_face_bookings_slot_id_foreign');

        // slot_id must not be null before restoring NOT NULL + CASCADE
        \DB::statement('UPDATE face_to_face_bookings SET slot_id = NULL WHERE slot_id IS NULL');
        \DB::statement('ALTER TABLE face_to_face_bookings ALTER COLUMN slot_id SET NOT NULL');

        // Restore original CASCADE
        \DB::statement('ALTER TABLE face_to_face_bookings ADD CONSTRAINT face_to_face_bookings_slot_id_foreign
            FOREIGN KEY (slot_id) REFERENCES consultation_slots(id) ON DELETE CASCADE');
    }
};
