<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageUrlToVocabularyLibrary extends Migration
{
    public function up(): void
    {
        Schema::table('vocabulary_library', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('english_audio_url');
        });
    }

    public function down(): void
    {
        Schema::table('vocabulary_library', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });
    }
};
