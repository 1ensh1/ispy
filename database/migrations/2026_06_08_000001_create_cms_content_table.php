<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_content', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('section_key')->unique();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('image_url')->nullable();
            $table->string('file_url')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamp('updated_at')->nullable();
        });

        $sections = ['hero', 'about_school', 'about_app', 'how_to_download', 'apk_download'];

        foreach ($sections as $key) {
            DB::table('cms_content')->insert([
                'section_key'  => $key,
                'title'        => 'Placeholder title for ' . $key,
                'body'         => 'Placeholder body content for the ' . str_replace('_', ' ', $key) . ' section.',
                'image_url'    => null,
                'file_url'     => null,
                'is_published' => true,
                'updated_at'   => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_content');
    }
};
