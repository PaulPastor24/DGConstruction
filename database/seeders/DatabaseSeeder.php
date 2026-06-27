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
        
        DB::table('accomplishment_reports')->truncate();
        DB::table('attendance_logs')->truncate();
        DB::table('construction_phases')->truncate();
        DB::table('project_supervisors')->truncate();
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
                'email' => 'supervisor@dg-corp.ph',
                'password_hash' => $password,
                'role' => 'site_supervisor',
                'contact_number' => '+639177654321',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

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

        DB::table('clients')->insert([
            'user_id' => $clientId,
            'company_name' => 'D&G Construction Corp',
            'address' => 'Lipa City, Batangas',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('projects')->insert([
            [
                'project_id' => 1,
                'project_name' => 'Kulas and Rene',
                'project_location' => 'Quezon City',
                'client_id' => 1,
                'engineer_id' => 1,
                'start_date' => now()->addDays(5),
                'target_end_date' => now()->addMonths(8),
                'actual_end_date' => null,
                'status' => 'ongoing',
                'description' => 'Residential complex construction project',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => 2,
                'project_name' => 'Ghost Project',
                'project_location' => 'Pasig',
                'client_id' => 1,
                'engineer_id' => 1,
                'start_date' => now(),
                'target_end_date' => now()->addMonths(6),
                'actual_end_date' => null,
                'status' => 'planning',
                'description' => 'Commercial office building development',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('project_supervisors')->insert([
            [
                'project_id' => 1,
                'supervisor_id' => 2,
                'assigned_date' => now()->toDateString(),
                'is_active' => 1,
                'created_at' => now(),
            ],
            [
                'project_id' => 2,
                'supervisor_id' => 2,
                'assigned_date' => now()->toDateString(),
                'is_active' => 1,
                'created_at' => now(),
            ],
        ]);

        $this->call(ConstructionPhaseSeeder::class);

    }
}