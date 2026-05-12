<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin account
        DB::table('users')->insertOrIgnore([
            'name'              => 'Admin',
            'email'             => 'admin@ispyworld.com',
            'password'          => Hash::make('admin123'),
            'role'              => 'Admin',   
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Teacher account
        DB::table('users')->insertOrIgnore([
            'name'              => 'Alfonso',
            'email'             => 'alfonso@gmail.com',
            'password'          => Hash::make('teacher123'),
            'role'              => 'teacher', 
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }
}