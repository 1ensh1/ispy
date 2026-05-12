<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('vocabulary_id')->constrained('vocabulary_library')->cascadeOnDelete();
            $table->string('mode');
            $table->integer('attempts')->default(0);
            $table->float('score')->default(0);
            $table->json('errors')->nullable();
            $table->float('mastery_weight')->default(0);
            $table->timestamp('attempted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_progress');
    }
};
