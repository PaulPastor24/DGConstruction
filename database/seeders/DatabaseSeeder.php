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
        DB::table('project_workers')->truncate();
        DB::table('workers')->truncate();
        DB::table('timeline_milestones')->truncate();
        DB::table('worker_biometric_profiles')->truncate();
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
        
        // 3. Insert users (engineer, supervisor, client) using revised schema columns
        $engineerId = DB::table('users')->insertGetId([
            'first_name' => 'Lead',
            'last_name' => 'Engineer',
            'name' => 'Lead Engineer',
            'email' => 'admin@dg-corp.ph',
            'password' => $password,
            'role' => 'engineer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $supervisorId = DB::table('users')->insertGetId([
            'first_name' => 'Site',
            'last_name' => 'Supervisor',
            'name' => 'Site Supervisor',
            'email' => 'supervisor@dg-corp.ph',
            'password' => $password,
            'role' => 'supervisor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $clientUserId = DB::table('users')->insertGetId([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'email' => 'client@dg-corp.ph',
            'password' => $password,
            'role' => 'client',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $clientId = DB::table('clients')->insertGetId([
            'user_id' => $clientUserId,
            'company_name' => 'D&G Construction Corp',
            'address' => 'Lipa City, Batangas',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('projects')->insert([
            [
                'project_name' => 'Kulas and Rene',
            'location' => 'Quezon City',
            'project_location' => 'Quezon City',
                'client_id' => $clientId,
                'engineer_id' => $engineerId,
                'start_date' => now()->addDays(5),
                'target_end_date' => now()->addMonths(8),
                'actual_end_date' => null,
                'status' => 'ongoing',
                'description' => 'Residential complex construction project',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_name' => 'Ghost Project',
                'location' => 'Pasig',
                'project_location' => 'Pasig',
                'client_id' => $clientId,
                'engineer_id' => $engineerId,
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
                'supervisor_id' => $supervisorId,
                'assigned_date' => now()->toDateString(),
                'is_active' => 1,
                'created_at' => now(),
            ],
            [
                'project_id' => 2,
                'supervisor_id' => $supervisorId,
                'assigned_date' => now()->toDateString(),
                'is_active' => 1,
                'created_at' => now(),
            ],
        ]);

        $this->call(ConstructionPhaseSeeder::class);
        $this->call(DevelopmentDataSeeder::class);

    }
}