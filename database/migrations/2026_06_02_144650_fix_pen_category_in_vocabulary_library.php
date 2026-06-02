<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('vocabulary_library')
            ->where('english_label', 'Pen')
            ->update(['category' => 'CVC']);
    }

    public function down(): void
    {
        DB::table('vocabulary_library')
            ->where('english_label', 'Pen')
            ->update(['category' => 'Classroom']);
    }
};
