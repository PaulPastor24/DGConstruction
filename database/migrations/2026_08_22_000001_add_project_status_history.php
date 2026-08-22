<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('projects', 'hold_reason')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->text('hold_reason')->nullable()->after('status');
            });
        }

        if (!Schema::hasTable('project_status_histories')) {
            Schema::create('project_status_histories', function (Blueprint $table) {
                $table->id('project_status_history_id');
                $table->unsignedInteger('project_id');
                $table->string('from_status')->nullable();
                $table->string('to_status');
                $table->unsignedBigInteger('changed_by')->nullable();
                $table->text('reason')->nullable();
                $table->timestamps();

                $table->foreign('project_id')->references('project_id')->on('projects')->cascadeOnDelete();
                $table->foreign('changed_by')->references('user_id')->on('users')->nullOnDelete();
                $table->index(['project_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_status_histories');

        if (Schema::hasColumn('projects', 'hold_reason')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('hold_reason');
            });
        }
    }
};
