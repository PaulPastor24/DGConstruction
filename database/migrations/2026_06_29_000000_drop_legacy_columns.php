<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop legacy columns that are removed in the revised SQL
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'name')) {
                    $table->dropColumn('name');
                }
                if (Schema::hasColumn('users', 'password_hash')) {
                    $table->dropColumn('password_hash');
                }
            });
        }

        if (Schema::hasTable('workers')) {
            Schema::table('workers', function (Blueprint $table) {
                if (Schema::hasColumn('workers', 'full_name')) {
                    $table->dropColumn('full_name');
                }
            });
        }

        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (Schema::hasColumn('projects', 'project_location')) {
                    $table->dropColumn('project_location');
                }
            });
        }

        if (Schema::hasTable('attendance_logs')) {
            // Remove foreign keys before dropping columns
            $dbName = env('DB_DATABASE');
            $constraints = \Illuminate\Support\Facades\DB::select(
                "SELECT CONSTRAINT_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'attendance_logs' AND REFERENCED_TABLE_NAME IS NOT NULL",
                [$dbName]
            );

            foreach ($constraints as $c) {
                try {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE `attendance_logs` DROP FOREIGN KEY `{$c->CONSTRAINT_NAME}`");
                } catch (\Exception $e) {
                    // ignore
                }
            }

            Schema::table('attendance_logs', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_logs', 'project_id')) {
                    $table->dropColumn('project_id');
                }
                if (Schema::hasColumn('attendance_logs', 'worker_id')) {
                    $table->dropColumn('worker_id');
                }
            });
        }
    }

    public function down(): void
    {
        // Recreate legacy columns (nullable) to allow rollback
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'name')) {
                    $table->string('name')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('users', 'password_hash')) {
                    $table->string('password_hash')->nullable()->after('password');
                }
            });
        }

        if (Schema::hasTable('workers')) {
            Schema::table('workers', function (Blueprint $table) {
                if (!Schema::hasColumn('workers', 'full_name')) {
                    $table->string('full_name', 150)->nullable()->after('worker_id');
                }
            });
        }

        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (!Schema::hasColumn('projects', 'project_location')) {
                    $table->text('project_location')->nullable()->after('project_name');
                }
            });
        }

        if (Schema::hasTable('attendance_logs')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_logs', 'project_id')) {
                    $table->unsignedInteger('project_id')->nullable()->after('log_id');
                }
                if (!Schema::hasColumn('attendance_logs', 'worker_id')) {
                    $table->unsignedInteger('worker_id')->nullable()->after('project_id');
                }
            });
        }
    }
};
