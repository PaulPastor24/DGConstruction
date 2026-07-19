<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('worker_biometric_profiles')) {
            Schema::create('worker_biometric_profiles', function (Blueprint $table) {
                $table->increments('biometric_id');
                $table->integer('worker_id');
                $table->binary('fingerprint_template');
                $table->timestamp('enrolled_at')->useCurrent();
                $table->bigInteger('enrolled_by')->nullable();
                $table->foreign('worker_id')->references('worker_id')->on('workers')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('enrolled_by')->references('user_id')->on('users')->nullOnDelete()->onUpdate('cascade');
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'first_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('first_name', 100)->nullable()->after('user_id');
                $table->string('last_name', 100)->nullable()->after('first_name');
            });

            $users = DB::table('users')->whereNotNull('name')->get(['user_id', 'name']);
            foreach ($users as $user) {
                $parts = preg_split('/\s+/', trim((string) $user->name));
                $firstName = $parts[0] ?? null;
                $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;

                DB::table('users')->where('user_id', $user->user_id)->update([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]);
            }
        }

        if (Schema::hasTable('workers') && !Schema::hasColumn('workers', 'first_name')) {
            Schema::table('workers', function (Blueprint $table) {
                $table->string('first_name', 100)->nullable()->after('worker_id');
                $table->string('last_name', 100)->nullable()->after('first_name');
            });

            $workers = DB::table('workers')->whereNotNull('full_name')->get(['worker_id', 'full_name']);
            foreach ($workers as $worker) {
                $parts = preg_split('/\s+/', trim((string) $worker->full_name));
                $firstName = $parts[0] ?? null;
                $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;

                DB::table('workers')->where('worker_id', $worker->worker_id)->update([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]);
            }
        }

        if (Schema::hasTable('attendance_logs') && !Schema::hasColumn('attendance_logs', 'deployment_id')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                $table->integer('deployment_id')->nullable()->after('log_id');
            });

            $logs = DB::table('attendance_logs')->whereNull('deployment_id')->get(['log_id', 'project_id', 'worker_id']);
            foreach ($logs as $log) {
                $deploymentId = DB::table('project_workers')
                    ->where('project_id', $log->project_id)
                    ->where('worker_id', $log->worker_id)
                    ->value('deployment_id');

                if ($deploymentId) {
                    DB::table('attendance_logs')->where('log_id', $log->log_id)->update(['deployment_id' => $deploymentId]);
                }
            }
        }

        if (Schema::hasTable('projects') && !Schema::hasColumn('projects', 'location')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->text('location')->nullable()->after('project_name');
            });

            DB::table('projects')->whereNull('location')->update([
                'location' => DB::raw('project_location'),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('worker_biometric_profiles')) {
            Schema::dropIfExists('worker_biometric_profiles');
        }

        if (Schema::hasTable('attendance_logs') && Schema::hasColumn('attendance_logs', 'deployment_id')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                $table->dropColumn('deployment_id');
            });
        }

        if (Schema::hasTable('projects') && Schema::hasColumn('projects', 'location')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('location');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'first_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['first_name', 'last_name']);
            });
        }

        if (Schema::hasTable('workers') && Schema::hasColumn('workers', 'first_name')) {
            Schema::table('workers', function (Blueprint $table) {
                $table->dropColumn(['first_name', 'last_name']);
            });
        }
    }
};
