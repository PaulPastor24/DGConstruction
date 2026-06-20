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

        // 3. Insert users with BOTH 'name' and 'full_name' to satisfy database constraints
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
            [
                'name' => 'John Doe (Client Representative)',
                'email' => 'client@dg-corp.ph',
                'password_hash' => $password,
                'role' => 'client',
                'contact_number' => '+639159998888',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 4. Insert client profiles
        DB::table('clients')->insert([
            [
                'client_id' => 1,
                'user_id' => 3,
                'company_name' => 'Doe Properties Inc.',
                'address' => 'Makati, Manila',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 5. Insert projects with client assignment
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

        // 6. Assign supervisors to projects
        DB::table('project_supervisors')->insert([
            [
                'project_id' => 1,
                'supervisor_id' => 2,  // Site Supervisor Alpha
                'assigned_date' => now()->toDateString(),
                'is_active' => 1,
                'created_at' => now(),
            ],
            [
                'project_id' => 2,
                'supervisor_id' => 2,  // Site Supervisor Alpha
                'assigned_date' => now()->toDateString(),
                'is_active' => 1,
                'created_at' => now(),
            ],
        ]);

        // 7. Run the ConstructionPhaseSeeder
        $this->call(ConstructionPhaseSeeder::class);

        // 8. Verification Note: 
        // Run 'php artisan migrate:fresh --seed' after saving this file.
    }
}