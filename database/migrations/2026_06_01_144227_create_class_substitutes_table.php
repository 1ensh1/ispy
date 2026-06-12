<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('class_substitutes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_list_id')->constrained('class_lists')->cascadeOnDelete();
            $table->foreignId('substitute_teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->integer('assigned_by');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_substitutes');
    }
};
