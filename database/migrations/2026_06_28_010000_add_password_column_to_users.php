<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('password')->nullable()->after('email');
            });

            // Backfill from legacy column if present
            if (Schema::hasColumn('users', 'password_hash')) {
                DB::table('users')->whereNotNull('password_hash')->update([
                    'password' => DB::raw('password_hash')
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('password');
            });
        }
    }
};
