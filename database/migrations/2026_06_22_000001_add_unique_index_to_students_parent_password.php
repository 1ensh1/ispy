<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Enforce that no two *active* (non-archived) students share the same
     * parent_password. A Postgres partial unique index is used so that:
     *   - archived students (archived_at IS NOT NULL) are excluded, letting a
     *     freed-up password be reused by a new student;
     *   - NULL parent_password values never conflict (Postgres treats NULLs in
     *     a unique index as distinct), so students without a password set yet
     *     are unaffected.
     */
    public function up(): void
    {
        DB::statement('
            CREATE UNIQUE INDEX IF NOT EXISTS students_parent_password_active_unique
            ON students (parent_password)
            WHERE archived_at IS NULL
        ');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS students_parent_password_active_unique');
    }
};
