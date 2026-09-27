<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workers') && ! Schema::hasColumn('workers', 'profile_image')) {
            Schema::table('workers', function (Blueprint $table) {
                $table->string('profile_image')->nullable()->after('trade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workers') && Schema::hasColumn('workers', 'profile_image')) {
            Schema::table('workers', function (Blueprint $table) {
                $table->dropColumn('profile_image');
            });
        }
    }
};