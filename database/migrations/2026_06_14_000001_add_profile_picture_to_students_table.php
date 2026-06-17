<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('students', 'profile_picture')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('profile_picture', 1024)->nullable()->default(null)->after('profile_icon');
            });
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('profile_picture');
        });
    }
};
