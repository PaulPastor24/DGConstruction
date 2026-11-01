<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_delete_users')->default(false);
        });

        // Assign once to the designated existing account, not to future email matches.
        DB::table('users')->whereRaw('LOWER(email) = ?', ['dgcon.ad@gmail.com'])
            ->whereIn('role', ['engineer', 'admin', 'administrator'])
            ->update(['can_delete_users' => true]);
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('can_delete_users'));
    }
};
