<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendance_logs')) {
            return;
        }

        Schema::table('attendance_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('attendance_logs', 'worker_id')) {
                $table->unsignedInteger('worker_id')->nullable()->after('log_id');
            }

            if (! Schema::hasColumn('attendance_logs', 'project_id')) {
                $table->unsignedInteger('project_id')->nullable()->after('worker_id');
            }
        });

        if (Schema::hasColumn('attendance_logs', 'deployment_id')
            && Schema::hasColumn('attendance_logs', 'worker_id')
            && Schema::hasTable('project_workers')) {
            DB::table('attendance_logs')->whereNull('worker_id')->update([
                'worker_id' => DB::table('project_workers')->select('worker_id')
                    ->whereColumn('project_workers.deployment_id', 'attendance_logs.deployment_id')->limit(1),
            ]);
        }

        if (Schema::hasColumn('attendance_logs', 'deployment_id')
            && Schema::hasColumn('attendance_logs', 'project_id')
            && Schema::hasTable('project_workers')) {
            DB::table('attendance_logs')->whereNull('project_id')->update([
                'project_id' => DB::table('project_workers')->select('project_id')
                    ->whereColumn('project_workers.deployment_id', 'attendance_logs.deployment_id')->limit(1),
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('attendance_logs')) {
            return;
        }

        Schema::table('attendance_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('attendance_logs', 'project_id')) {
                $table->dropColumn('project_id');
            }

            if (Schema::hasColumn('attendance_logs', 'worker_id')) {
                $table->dropColumn('worker_id');
            }
        });
    }
};
