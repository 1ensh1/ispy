<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engagement_id')->constrained('engagement_records')->cascadeOnDelete();
            $table->string('sender_role');
            $table->integer('sender_id');
            $table->text('message_body');
            $table->timestamp('sent_at');
            $table->boolean('is_read')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
