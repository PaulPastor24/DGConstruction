<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SandboxCredentialsSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');
        $now = now();

        $clientUserId = null;

        foreach ([
            ['email' => 'admin@dg-corp.ph', 'first_name' => 'Lead', 'last_name' => 'Engineer', 'role' => 'engineer'],
            ['email' => 'supervisor@dg-corp.ph', 'first_name' => 'Site', 'last_name' => 'Supervisor', 'role' => 'supervisor'],
            ['email' => 'client@dg-corp.ph', 'first_name' => 'John', 'last_name' => 'Doe', 'role' => 'client'],
        ] as $account) {
            DB::table('users')->updateOrInsert(
                ['email' => $account['email']],
                [
                    'first_name' => $account['first_name'],
                    'last_name' => $account['last_name'],
                    'password' => $password,
                    'role' => $account['role'],
                    'is_active' => true,
                    'updated_at' => $now,
                ],
            );

            if ($account['role'] === 'client') {
                $clientUserId = DB::table('users')->where('email', $account['email'])->value('user_id');
            }
        }

        if ($clientUserId !== null && DB::getSchemaBuilder()->hasTable('clients')) {
            DB::table('clients')->updateOrInsert(
                ['user_id' => $clientUserId],
                [
                    'company_name' => 'D&G Construction Corp',
                    'address' => 'Lipa City, Batangas',
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }
}
