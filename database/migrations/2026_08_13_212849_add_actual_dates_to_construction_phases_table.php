<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('construction_phases', function (Blueprint $table) {
            if (!Schema::hasColumn('construction_phases', 'actual_start_date')) {
                $table->date('actual_start_date')->nullable()->after('planned_end_date');
            }
            if (!Schema::hasColumn('construction_phases', 'actual_end_date')) {
                $table->date('actual_end_date')->nullable()->after('actual_start_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('construction_phases', function (Blueprint $table) {
            $table->dropColumn(['actual_start_date', 'actual_end_date']);
        });
    }
};
