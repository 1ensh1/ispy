<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('captured_objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('vocabulary_id')->constrained('vocabulary_library')->cascadeOnDelete();
            $table->string('captured_image_url')->nullable();
            $table->boolean('is_successful_match')->default(false);
            $table->timestamp('captured_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('captured_objects');
    }
};
