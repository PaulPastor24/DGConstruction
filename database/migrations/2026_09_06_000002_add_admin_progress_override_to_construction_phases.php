<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('construction_phases', function (Blueprint $table) {
            $table->decimal('admin_progress_override', 5, 2)->nullable()->after('completion_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('construction_phases', function (Blueprint $table) {
            $table->dropColumn('admin_progress_override');
        });
    }
};
