<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendance_logs') || ! Schema::hasColumn('attendance_logs', 'project_id')) {
            return;
        }

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->unsignedInteger('project_id')->nullable()->change();
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->unsignedInteger('project_id')->nullable(false)->change();
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
        });
    }
};
