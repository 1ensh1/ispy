<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vocabulary_library', function (Blueprint $table) {
            $table->id();
            $table->string('noun_anchor');
            $table->string('category');
            $table->string('filipino_label');
            $table->string('english_label')->unique();
            $table->string('filipino_audio_url')->nullable();
            $table->string('english_audio_url')->nullable();
            $table->string('audio_status')->default('Missing');
            $table->integer('complexity_level');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vocabulary_library');
    }
};
