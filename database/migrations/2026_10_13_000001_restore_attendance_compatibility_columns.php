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
            DB::statement(
                'UPDATE attendance_logs AS logs
                 INNER JOIN project_workers AS deployments
                    ON deployments.deployment_id = logs.deployment_id
                 SET logs.worker_id = deployments.worker_id
                 WHERE logs.worker_id IS NULL'
            );
        }

        if (Schema::hasColumn('attendance_logs', 'deployment_id')
            && Schema::hasColumn('attendance_logs', 'project_id')
            && Schema::hasTable('project_workers')) {
            DB::statement(
                'UPDATE attendance_logs AS logs
                 INNER JOIN project_workers AS deployments
                    ON deployments.deployment_id = logs.deployment_id
                 SET logs.project_id = deployments.project_id
                 WHERE logs.project_id IS NULL'
            );
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
