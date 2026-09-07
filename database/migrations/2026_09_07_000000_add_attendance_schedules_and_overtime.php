<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workers')) {
            Schema::table('workers', function (Blueprint $table) {
                if (! Schema::hasColumn('workers', 'role')) {
                    $table->string('role', 30)->default('worker')->after('trade');
                }
                if (! Schema::hasColumn('workers', 'schedule_start')) {
                    $table->time('schedule_start')->default('07:00:00')->after('role');
                }
                if (! Schema::hasColumn('workers', 'schedule_end')) {
                    $table->time('schedule_end')->default('17:00:00')->after('schedule_start');
                }
                if (! Schema::hasColumn('workers', 'break_minutes')) {
                    $table->unsignedSmallInteger('break_minutes')->default(60)->after('schedule_end');
                }
            });
        }

        if (Schema::hasTable('attendance_logs')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                if (! Schema::hasColumn('attendance_logs', 'break_out')) {
                    $table->time('break_out')->nullable()->after('time_in');
                }
                if (! Schema::hasColumn('attendance_logs', 'break_in')) {
                    $table->time('break_in')->nullable()->after('break_out');
                }
                if (! Schema::hasColumn('attendance_logs', 'overtime_minutes')) {
                    $table->unsignedInteger('overtime_minutes')->default(0)->after('time_out');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendance_logs')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                foreach (['break_out', 'break_in', 'overtime_minutes'] as $column) {
                    if (Schema::hasColumn('attendance_logs', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('workers')) {
            Schema::table('workers', function (Blueprint $table) {
                foreach (['role', 'schedule_start', 'schedule_end', 'break_minutes'] as $column) {
                    if (Schema::hasColumn('workers', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};