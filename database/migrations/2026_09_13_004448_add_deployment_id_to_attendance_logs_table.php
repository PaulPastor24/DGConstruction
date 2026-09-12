<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance_logs', 'deployment_id')) {
                $table->unsignedInteger('deployment_id')->nullable()->after('worker_id');
                $table->foreign('deployment_id')->references('deployment_id')->on('project_workers')->onDelete('set null')->onUpdate('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_logs', 'deployment_id')) {
                $table->dropForeign(['deployment_id']);
                $table->dropColumn('deployment_id');
            }
        });
    }
};
