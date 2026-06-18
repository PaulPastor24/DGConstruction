<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Clean the database safely
        Schema::disableForeignKeyConstraints();
        
        DB::table('reports')->truncate();
        DB::table('attendances')->truncate();
        DB::table('projects')->truncate();
        DB::table('clients')->truncate();
        DB::table('users')->truncate();

        Schema::enableForeignKeyConstraints();

        // 2. Define standard password
        $password = Hash::make('password123');

        // 3. Insert users with BOTH 'name' and 'full_name' to satisfy database constraints
        DB::table('users')->insert([
            [
                'name' => 'Lead Project Engineer',
                'full_name' => 'Lead Project Engineer',
                'email' => 'admin@dg-corp.ph',
                'password_hash' => $password,
                'role' => 'engineer',
                'contact_number' => '+639171234567',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Site Supervisor Alpha',
                'full_name' => 'Site Supervisor Alpha',
                'email' => 'supervisor@dg-corp.ph',
                'password_hash' => $password,
                'role' => 'site_supervisor',
                'contact_number' => '+639177654321',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'John Doe (Client)',
                'full_name' => 'John Doe (Client Representative)',
                'email' => 'client@dg-corp.ph',
                'password_hash' => $password,
                'role' => 'client',
                'contact_number' => '+639159998888',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 4. Verification Note: 
        // Run 'php artisan migrate:fresh --seed' after saving this file.
    }
}