<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendance_logs') && !Schema::hasColumn('attendance_logs', 'biometric_matched')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                $table->boolean('biometric_matched')->default(true)->after('remarks');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendance_logs') && Schema::hasColumn('attendance_logs', 'biometric_matched')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                $table->dropColumn('biometric_matched');
            });
        }
    }
};
