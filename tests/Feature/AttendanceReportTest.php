<?php

namespace Tests\Feature;

use App\Mail\DailyAttendanceReport;
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
        config([
            'attendance_reports.mailer' => 'mailjet',
            'attendance_reports.from.address' => 'reports@dgconphil.test',
            'attendance_reports.from.name' => 'D&G Reports',
            'mail.mailers.mailjet.username' => 'test-mailjet-api-key',
            'mail.mailers.mailjet.password' => 'test-mailjet-secret',
        ]);

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

        Mail::assertSent(DailyAttendanceReport::class, function ($mail) {
            return $mail->date->format('Y-m-d') === '2026-09-12'
                && $mail->hasTo('admin@example.com')
                && $mail->mailer === 'mailjet'
                && $mail->envelope()->from->address === 'reports@dgconphil.test';
        });
        Mail::assertSentCount(1);
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

        Mail::shouldReceive('mailer')
            ->once()
            ->with('mailjet')
            ->andThrow(new \RuntimeException('Mail configuration error'));

        $response = $this->actingAs($admin)->post(route('admin.attendance.send-report'), [
            'date' => '2026-09-12',
        ]);

        $response->assertSessionHas('error');
        $response->assertRedirect(route('admin.attendance.preview-report', ['date' => '2026-09-12']));
    }

    public function test_send_report_uses_latest_account_email_and_ignores_submitted_recipient(): void
    {
        Mail::fake();
        $admin = User::create([
            'name' => 'Admin', 'email' => 'old@example.com', 'password' => bcrypt('password'),
            'role' => 'engineer', 'is_active' => true,
        ]);
        $this->actingAs($admin);
        DB::table('users')->where('user_id', $admin->user_id)->update(['email' => 'updated@example.com']);
        $this->post(route('admin.attendance.send-report'), [
            'date' => '2026-09-12', 'email' => 'another@example.com', 'recipient' => 'another@example.com',
        ])->assertSessionHas('success');
        Mail::assertSent(DailyAttendanceReport::class, fn ($mail) => $mail->hasTo('updated@example.com') && ! $mail->hasTo('another@example.com') && ! $mail->hasTo('old@example.com')
        );
        Mail::assertSentCount(1);
    }

    public function test_missing_mailjet_credentials_reports_configuration_error_instead_of_success(): void
    {
        Mail::fake();
        config(['mail.mailers.mailjet.password' => null]);
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password'),
            'role' => 'engineer', 'is_active' => true,
        ]);
        $this->actingAs($admin)->post(route('admin.attendance.send-report'), ['date' => '2026-09-12'])
            ->assertSessionHas('error', 'Mailjet is not configured. Set MAILJET_API_KEY and MAILJET_SECRET_KEY on the server before sending reports.');
        Mail::assertNothingSent();
    }

    public function test_mailjet_transport_uses_api_credentials_and_requires_tls(): void
    {
        $transport = Mail::mailer('mailjet')->getSymfonyTransport();
        $this->assertSame('in-v3.mailjet.com', $transport->getStream()->getHost());
        $this->assertSame(587, $transport->getStream()->getPort());
        $this->assertSame('test-mailjet-api-key', $transport->getUsername());
        $this->assertTrue($transport->isTlsRequired());
    }

    public function test_scheduled_attendance_reports_use_mailjet_and_each_active_admin_email(): void
    {
        Mail::fake();
        foreach ([['first@example.com', 'engineer', true], ['second@example.com', 'admin', true], ['disabled@example.com', 'engineer', false], ['supervisor@example.com', 'supervisor', true]] as [$email, $role, $active]) {
            User::create(['name' => 'User', 'email' => $email, 'password' => bcrypt('password'), 'role' => $role, 'is_active' => $active]);
        }
        $this->artisan('attendance:daily-report', ['date' => '2026-09-12'])->assertSuccessful();
        Mail::assertSentCount(2);
        foreach (['first@example.com', 'second@example.com'] as $email) {
            Mail::assertSent(DailyAttendanceReport::class, fn ($mail) => $mail->hasTo($email) && $mail->mailer === 'mailjet');
        }
    }
}
