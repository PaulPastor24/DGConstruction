<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->increments('log_id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('worker_id');
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->date('log_date');
            $table->time('time_in')->nullable();
            $table->time('break_out')->nullable();
            $table->time('break_in')->nullable();
            $table->time('time_out')->nullable();
            $table->unsignedInteger('overtime_minutes')->default(0);
            $table->string('status')->default('present');
            $table->text('remarks')->nullable();
            $table->boolean('biometric_matched')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('worker_id')->references('worker_id')->on('workers')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('recorded_by')->references('user_id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
