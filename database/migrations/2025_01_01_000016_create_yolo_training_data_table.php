<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yolo_training_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('administrators')->cascadeOnDelete();
            $table->string('image_url');
            $table->string('label');
            $table->json('annotation_data')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yolo_training_data');
    }
};
