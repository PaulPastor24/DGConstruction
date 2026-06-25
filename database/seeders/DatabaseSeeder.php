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
        
        DB::table('materials')->insert([
            ['name' => 'Portland Cement', 'unit' => 'bags', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '10mm Deformed Steel Bar', 'unit' => 'pcs', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gravel (3/4")', 'unit' => 'm³', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sand (Washed)', 'unit' => 'm³', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ceramic Tiles (60x60)', 'unit' => 'boxes', 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // 3. Insert non-client users
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
        ]);

        // 4. Insert John Doe into the users table and grab his user_id right away
        $clientId = DB::table('users')->insertGetId([
            'name' => 'John Doe',
            'full_name' => 'John Doe (Client Representative)',
            'email' => 'client@dg-corp.ph',
            'password_hash' => $password,
            'role' => 'client',
            'contact_number' => '+639159998888',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. FIX: Insert the linked client profile row using his generated user_id
        DB::table('clients')->insert([
            'user_id' => $clientId,
            'company_name' => 'D&G Construction Corp', // You can customize this
            'address' => 'Lipa City, Batangas',       // You can customize this
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}