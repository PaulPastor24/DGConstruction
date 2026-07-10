<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. USERS TABLE
        Schema::create('users', function (Blueprint $table) {
                $table->id('user_id'); // Sets 'user_id' as the primary key
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password_hash')->nullable(); // Legacy password field
                $table->string('role');
                $table->string('contact_number')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('email_verified_at')->nullable();
                $table->rememberToken();
                $table->timestamps();
        });
        // 2. CLIENTS TABLE
        Schema::create('clients', function (Blueprint $table) {
            $table->increments('client_id');
            $table->unsignedBigInteger('user_id');
            $table->string('company_name', 200)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        // 3. PROJECTS TABLE
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('project_id');
            $table->string('project_name', 200);
            $table->text('project_location');
            $table->unsignedInteger('client_id');
            $table->unsignedBigInteger('engineer_id');
            $table->date('start_date');
            $table->date('target_end_date');
            $table->date('actual_end_date')->nullable();
            $table->enum('status', ['planning', 'ongoing', 'completed', 'on_hold'])->default('planning');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->foreign('client_id')->references('client_id')->on('clients')->onUpdate('cascade');
            $table->foreign('engineer_id')->references('user_id')->on('users')->onUpdate('cascade');
        });

        // 4. CONSTRUCTION PHASES TABLE
        Schema::create('construction_phases', function (Blueprint $table) {
            $table->increments('phase_id');
            $table->unsignedInteger('project_id');
            $table->string('phase_name', 200);
            $table->unsignedInteger('phase_order');
            $table->date('planned_start_date');
            $table->date('planned_end_date');
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->decimal('completion_percentage', 5, 2)->default(0.00);
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'delayed'])->default('not_started');
            $table->timestamps();
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
        });

        // 5. ACCOMPLISHMENT REPORTS TABLE
        Schema::create('accomplishment_reports', function (Blueprint $table) {
            $table->increments('report_id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('phase_id');
            $table->unsignedBigInteger('submitted_by');
            $table->date('report_date');
            $table->longText('report_text');
            $table->json('site_images')->nullable();
            $table->enum('ai_status', ['pending', 'processed', 'failed'])->default('pending');
            $table->timestamps();
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('phase_id')->references('phase_id')->on('construction_phases')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('submitted_by')->references('user_id')->on('users')->onUpdate('cascade');
        });

        // 6. AI ANALYSIS RESULTS TABLE
        Schema::create('ai_analysis_results', function (Blueprint $table) {
            $table->increments('result_id');
            $table->unsignedInteger('report_id');
            $table->unsignedInteger('phase_id');
            $table->text('identified_activities')->nullable();
            $table->decimal('computed_progress', 5, 2)->default(0.00);
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->longText('raw_ai_output')->nullable();
            $table->timestamp('processed_at')->useCurrent();
            $table->foreign('report_id')->references('report_id')->on('accomplishment_reports')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('phase_id')->references('phase_id')->on('construction_phases')->onDelete('cascade')->onUpdate('cascade');
        });

        // 7. WORKERS TABLE
        Schema::create('workers', function (Blueprint $table) {
            $table->increments('worker_id');
            $table->string('full_name', 150);
            $table->string('trade', 100)->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();
        });

        // 8. ATTENDANCE LOGS TABLE
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->increments('log_id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('worker_id');
            $table->unsignedBigInteger('recorded_by');
            $table->date('log_date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->enum('status', ['present', 'absent', 'half_day', 'on_leave'])->default('present');
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('worker_id')->references('worker_id')->on('workers')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('recorded_by')->references('user_id')->on('users')->onUpdate('cascade');
        });

        // 9. PROJECT SUPERVISORS TABLE
        Schema::create('project_supervisors', function (Blueprint $table) {
            $table->increments('assignment_id');
            $table->unsignedInteger('project_id');
            $table->unsignedBigInteger('supervisor_id');
            $table->date('assigned_date');
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('supervisor_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        // 10. PROJECT WORKERS TABLE
        Schema::create('project_workers', function (Blueprint $table) {
            $table->increments('deployment_id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('worker_id');
            $table->date('deployed_date');
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('worker_id')->references('worker_id')->on('workers')->onDelete('cascade')->onUpdate('cascade');
        });

        // 11. TIMELINE MILESTONES TABLE
        Schema::create('timeline_milestones', function (Blueprint $table) {
            $table->increments('milestone_id');
            $table->unsignedInteger('phase_id');
            $table->string('milestone_name', 200);
            $table->date('planned_date');
            $table->date('actual_date')->nullable();
            $table->tinyInteger('is_completed')->default(0);
            $table->tinyInteger('is_delayed')->default(0);
            $table->timestamps();
            $table->foreign('phase_id')->references('phase_id')->on('construction_phases')->onDelete('cascade')->onUpdate('cascade');
        });

        // 12. SYSTEM LOGS TABLE
        Schema::create('system_logs', function (Blueprint $table) {
            $table->increments('log_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 200);
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('timeline_milestones');
        Schema::dropIfExists('project_workers');
        Schema::dropIfExists('project_supervisors');
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('workers');
        Schema::dropIfExists('ai_analysis_results');
        Schema::dropIfExists('accomplishment_reports');
        Schema::dropIfExists('construction_phases');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('users');
    }
};