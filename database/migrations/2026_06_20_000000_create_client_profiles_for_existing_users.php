<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all client users that don't have a client profile
        $clientUsers = DB::table('users')
            ->where('role', 'client')
            ->whereNotIn('user_id', function ($query) {
                $query->select('user_id')->from('clients');
            })
            ->get();

        // Create client profiles for them
        foreach ($clientUsers as $user) {
            DB::table('clients')->insert([
                'user_id' => $user->user_id,
                'company_name' => null,
                'address' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed - this just creates missing client profiles
    }
};
