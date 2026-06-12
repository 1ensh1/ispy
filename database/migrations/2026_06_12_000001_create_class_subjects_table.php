<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1 — Create class_subjects table.
        Schema::create('class_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_list_id')->constrained('class_lists')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('subject');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('archived_at')->nullable();

            $table->unique(['class_list_id', 'subject']);
        });

        // Restrict subject values to 'English' or 'Filipino' at the DB level.
        DB::statement("ALTER TABLE class_subjects ADD CONSTRAINT class_subjects_subject_check CHECK (subject IN ('English', 'Filipino'))");

        // Step 2 — Migrate existing subjects from class_lists into class_subjects.
        // Only active rows (archived_at IS NULL) with a non-null subject are migrated.
        // ON CONFLICT DO NOTHING skips any duplicate (class_list_id, subject) pair.
        DB::statement("
            INSERT INTO class_subjects (class_list_id, teacher_id, subject, created_at)
            SELECT id, teacher_id, subject, NOW()
            FROM class_lists
            WHERE subject IS NOT NULL
              AND archived_at IS NULL
            ON CONFLICT (class_list_id, subject) DO NOTHING
        ");

        // Step 3 — Case-insensitive unique index on class_lists.class_name.
        DB::statement('CREATE UNIQUE INDEX class_lists_class_name_unique_lower ON class_lists (LOWER(class_name))');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS class_lists_class_name_unique_lower');

        Schema::dropIfExists('class_subjects');
    }
};
