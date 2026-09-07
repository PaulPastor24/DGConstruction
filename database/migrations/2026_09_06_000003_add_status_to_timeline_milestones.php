<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timeline_milestones', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('is_delayed');
        });
    }

    public function down(): void
    {
        Schema::table('timeline_milestones', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
