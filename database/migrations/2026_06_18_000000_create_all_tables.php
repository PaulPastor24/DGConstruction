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
            $table->bigInteger('user_id');
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
            $table->integer('client_id');
            $table->bigInteger('engineer_id');
            $table->date('start_date');
            $table->date('target_end_date');
            $table->date('actual_end_date')->nullable();
            $table->string('status')->default('planning');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->foreign('client_id')->references('client_id')->on('clients')->onUpdate('cascade');
            $table->foreign('engineer_id')->references('user_id')->on('users')->onUpdate('cascade');
        });

        // 4. CONSTRUCTION PHASES TABLE
        Schema::create('construction_phases', function (Blueprint $table) {
            $table->increments('phase_id');
            $table->integer('project_id');
            $table->string('phase_name', 200);
            $table->integer('phase_order');
            $table->date('planned_start_date');
            $table->date('planned_end_date');
            $table->decimal('completion_percentage', 5, 2)->default(0.00);
            $table->string('status')->default('not_started');
            $table->timestamps();
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
        });

        // 5. ACCOMPLISHMENT REPORTS TABLE
        Schema::create('accomplishment_reports', function (Blueprint $table) {
            $table->increments('report_id');
            $table->integer('project_id');
            $table->integer('phase_id');
            $table->bigInteger('submitted_by');
            $table->date('report_date');
            $table->longText('report_text');
            $table->json('site_images')->nullable();
            $table->string('ai_status')->default('pending');
            $table->timestamps();
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('phase_id')->references('phase_id')->on('construction_phases')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('submitted_by')->references('user_id')->on('users')->onUpdate('cascade');
        });

        // 6. AI ANALYSIS RESULTS TABLE
        Schema::create('ai_analysis_results', function (Blueprint $table) {
            $table->increments('result_id');
            $table->integer('report_id');
            $table->integer('phase_id');
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
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 8. ATTENDANCE LOGS TABLE
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->increments('log_id');
            $table->integer('project_id');
            $table->integer('worker_id');
            $table->bigInteger('recorded_by');
            $table->date('log_date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->string('status')->default('present');
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('worker_id')->references('worker_id')->on('workers')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('recorded_by')->references('user_id')->on('users')->onUpdate('cascade');
        });

        // 9. PROJECT SUPERVISORS TABLE
        Schema::create('project_supervisors', function (Blueprint $table) {
            $table->increments('assignment_id');
            $table->integer('project_id');
            $table->bigInteger('supervisor_id');
            $table->date('assigned_date');
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('supervisor_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        // 10. PROJECT WORKERS TABLE
        Schema::create('project_workers', function (Blueprint $table) {
            $table->increments('deployment_id');
            $table->integer('project_id');
            $table->integer('worker_id');
            $table->date('deployed_date');
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('worker_id')->references('worker_id')->on('workers')->onDelete('cascade')->onUpdate('cascade');
        });

        // 11. TIMELINE MILESTONES TABLE
        Schema::create('timeline_milestones', function (Blueprint $table) {
            $table->increments('milestone_id');
            $table->integer('phase_id');
            $table->string('milestone_name', 200);
            $table->date('planned_date');
            $table->date('actual_date')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_delayed')->default(false);
            $table->timestamps();
            $table->foreign('phase_id')->references('phase_id')->on('construction_phases')->onDelete('cascade')->onUpdate('cascade');
        });

        // 12. SYSTEM LOGS TABLE
        Schema::create('system_logs', function (Blueprint $table) {
            $table->increments('log_id');
            $table->bigInteger('user_id')->nullable();
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