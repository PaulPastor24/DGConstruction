<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Project;
use App\Models\ProjectWorker;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AttendanceReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function ($table) {
            $table->id('user_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('engineer');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('workers', function ($table) {
            $table->id('worker_id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('trade')->nullable();
            $table->string('role', 30)->default('worker');
            $table->time('schedule_start')->default('07:00:00');
            $table->time('schedule_end')->default('17:00:00');
            $table->unsignedSmallInteger('break_minutes')->default(60);
            $table->string('contact_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('projects', function ($table) {
            $table->id('project_id');
            $table->string('project_name');
            $table->text('project_location')->nullable();
            $table->string('status')->default('ongoing');
            $table->timestamps();
        });

        Schema::create('project_workers', function ($table) {
            $table->id('deployment_id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('worker_id');
            $table->date('deployed_date');
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('attendance_logs', function ($table) {
            $table->id('log_id');
            $table->unsignedInteger('project_id')->nullable();
            $table->unsignedInteger('worker_id')->nullable();
            $table->unsignedInteger('deployment_id')->nullable();
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
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('project_workers');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('workers');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_preview_page_loads_for_selected_date(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'engineer',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.preview-report', ['date' => '2026-09-12']));

        $response->assertStatus(200);
        $response->assertSee('Attendance Report Preview');
    }

    public function test_preview_filters_records_by_date(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'engineer',
            'is_active' => true,
        ]);

        $worker = Worker::create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'full_name' => 'Jane Doe',
            'schedule_start' => '07:00:00',
            'schedule_end' => '17:00:00',
        ]);

        $project = Project::create([
            'project_name' => 'Site A',
            'project_location' => 'Location A',
            'status' => 'ongoing',
        ]);

        $deploymentId = DB::table('project_workers')->insertGetId([
            'project_id' => $project->project_id,
            'worker_id' => $worker->worker_id,
            'deployed_date' => '2026-09-12',
            'is_active' => 1,
            'created_at' => now(),
        ]);

        $deployment = ProjectWorker::find($deploymentId);

        Attendance::create([
            'project_id' => $project->project_id,
            'worker_id' => $worker->worker_id,
            'deployment_id' => $deployment->deployment_id,
            'recorded_by' => $admin->user_id,
            'log_date' => '2026-09-12',
            'time_in' => '07:05:00',
            'time_out' => '17:30:00',
            'status' => 'present',
            'overtime_minutes' => 30,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.preview-report', ['date' => '2026-09-12']));

        $response->assertStatus(200);
        $response->assertSee('Jane Doe');
        $response->assertSee('Site A');
    }

    public function test_send_report_uses_authenticated_user_email(): void
    {
        Mail::fake();

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'engineer',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.attendance.send-report'), [
            'date' => '2026-09-12',
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('admin.attendance.preview-report', ['date' => '2026-09-12']));

        Mail::assertSent(\App\Mail\DailyAttendanceReport::class, function ($mail) {
            return $mail->date->format('Y-m-d') === '2026-09-12';
        });
    }

    public function test_send_report_handles_missing_email(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => '',
            'password' => bcrypt('password'),
            'role' => 'engineer',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.attendance.send-report'), [
            'date' => '2026-09-12',
        ]);

        $response->assertSessionHas('error');
        $response->assertRedirect(route('admin.attendance.preview-report', ['date' => '2026-09-12']));
    }

    public function test_send_report_handles_mail_failure(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'engineer',
            'is_active' => true,
        ]);

        Mail::fake();

        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new \RuntimeException('Mail configuration error'));

        $response = $this->actingAs($admin)->post(route('admin.attendance.send-report'), [
            'date' => '2026-09-12',
        ]);

        $response->assertSessionHas('error');
        $response->assertRedirect(route('admin.attendance.preview-report', ['date' => '2026-09-12']));
    }
}
