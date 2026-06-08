<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by_user_id');
            $table->string('created_by_role');
            $table->unsignedBigInteger('assigned_to_user_id');
            $table->string('title', 255);
            $table->text('description');
            $table->string('status')->default('Open');
            $table->string('priority')->default('Medium');
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_to_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
