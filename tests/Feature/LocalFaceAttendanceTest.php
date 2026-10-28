<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LocalFaceAttendanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['face_attendance.enabled' => true]);
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
            $table->string('profile_image')->nullable();
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

        Schema::create('project_supervisors', function ($t) {
            $t->unsignedBigInteger('project_id');
            $t->unsignedBigInteger('supervisor_id');
            $t->boolean('is_active')->default(true);
            $t->date('assigned_date')->nullable();
        });
        foreach (['2026_10_14_000001_create_worker_face_enrollments', '2026_10_14_000002_create_worker_face_attempts', '2026_10_16_000001_add_local_face_templates', '2026_10_16_000002_allow_contract_based_face_enrollment', '2026_10_16_000003_expand_face_template_storage'] as $migration) {
            (require database_path('migrations/'.$migration.'.php'))->up();
        }
        $supervisor = User::create(['name' => 'Supervisor', 'email' => 'camera@example.com', 'password' => bcrypt('password'), 'role' => 'supervisor', 'is_active' => true]);
        $this->actingAs($supervisor);
        DB::table('projects')->insert(['project_id' => 1, 'project_name' => 'Site']);
        DB::table('project_supervisors')->insert(['project_id' => 1, 'supervisor_id' => $supervisor->user_id]);
        foreach ([1, 2] as $id) {
            DB::table('workers')->insert(['worker_id' => $id, 'first_name' => 'Worker', 'last_name' => (string) $id]);
            DB::table('project_workers')->insert(['project_id' => 1, 'worker_id' => $id, 'deployed_date' => '2026-10-16']);
        }
    }

    private function samples(float $value = 1): array
    {
        return array_fill(0, 3, array_fill(0, 1024, $value));
    }

    private function startScan(string $purpose = 'attendance', int $worker = 1): array
    {
        return $this->postJson(route('supervisor.face-attendance.start'), [
            'purpose' => $purpose, 'worker_id' => $worker,
        ])->assertOk()->json();
    }

    private function finishScan(array $scan, array $samples)
    {
        return $this->postJson(route('supervisor.face-attendance.finish'), [
            'session_id' => $scan['session_id'], 'model_version' => $scan['model_version'], 'samples' => $samples,
        ]);
    }

    private function enroll(): void
    {
        $this->finishScan($this->startScan('enroll'), $this->samples())->assertOk();
    }

    public function test_break_in_and_time_out_each_require_a_fresh_face_scan(): void
    {
        $this->travelTo(now('Asia/Manila')->setTime(13, 0));
        $this->enroll();
        DB::table('attendance_logs')->insert([
            'worker_id' => 1, 'project_id' => 1, 'deployment_id' => 1,
            'log_date' => now('Asia/Manila')->toDateString(),
            'time_in' => '07:00:00', 'break_out' => '12:00:00',
        ]);
        foreach (['break_in', 'time_out'] as $action) {
            $payload = ['worker_id' => 1, 'log_date' => now('Asia/Manila')->toDateString(),
                'action' => $action, 'verification_method' => 'face', 'supervisor_confirmed_identity' => true];
            $this->postJson(route('supervisor.attendance.logWorker'), array_merge($payload, ['verification_method' => 'passkey']))->assertForbidden();
            $this->postJson(route('supervisor.attendance.logWorker'), $payload)->assertForbidden();
            $this->finishScan($this->startScan(), $this->samples())->assertOk();
            $this->postJson(route('supervisor.attendance.logWorker'), $payload)->assertOk();
            $this->postJson(route('supervisor.attendance.logWorker'), $payload)->assertForbidden();
            $this->travel(4)->hours();
        }
    }

    public function test_face_enrollment_and_scanning_do_not_require_checkbox_confirmations(): void
    {
        $this->enroll();
        $this->assertNull(DB::table('worker_face_enrollments')->value('notice_acknowledged_at'));
        $this->postJson(route('supervisor.face-attendance.start'), ['purpose' => 'attendance'])
            ->assertOk()->assertJsonStructure(['session_id', 'model_version']);
    }

    public function test_additional_active_supervisor_can_scan_existing_project_workers(): void
    {
        $this->enroll();
        $originalSupervisor = auth()->user();
        $additional = User::create(['name' => 'Additional Supervisor', 'email' => 'additional@example.com',
            'password' => bcrypt('password'), 'role' => 'supervisor', 'is_active' => true]);
        DB::table('project_supervisors')->insert([
            'project_id' => 1, 'supervisor_id' => $additional->user_id, 'is_active' => true,
        ]);
        $this->actingAs($additional);
        $this->finishScan($this->startScan(), $this->samples())->assertOk()->assertJsonPath('worker.worker_id', 1);
        $this->actingAs($originalSupervisor);
        $this->finishScan($this->startScan(), $this->samples())->assertOk()->assertJsonPath('worker.worker_id', 1);
    }

    public function test_enrollment_is_encrypted_and_camera_match_can_save_attendance_once(): void
    {
        $this->enroll();
        $row = DB::table('worker_face_enrollments')->first();
        $this->assertNotEquals(json_encode($this->samples()), $row->face_descriptors);
        $this->assertEquals($this->samples(), json_decode(Crypt::decryptString($row->face_descriptors), true));
        $scan = $this->startScan();
        $this->finishScan($scan, $this->samples())->assertOk()->assertJsonPath('worker.worker_id', 1);
        $payload = ['worker_id' => 1, 'log_date' => now('Asia/Manila')->toDateString(), 'action' => 'time_in',
            'verification_method' => 'face', 'supervisor_confirmed_identity' => true];
        $this->postJson(route('supervisor.attendance.logWorker'), $payload)->assertOk();
        $this->assertDatabaseHas('attendance_logs', ['worker_id' => 1, 'biometric_matched' => 0]);
        $this->assertStringContainsString('Local camera', DB::table('attendance_logs')->value('remarks'));
        $this->postJson(route('supervisor.attendance.logWorker'), $payload)->assertForbidden();
        $this->finishScan($scan, $this->samples())->assertStatus(409);
    }

    public function test_unknown_face_and_duplicate_enrollment_are_rejected(): void
    {
        $this->enroll();
        $this->finishScan($this->startScan(), $this->samples(-1))->assertStatus(422);
        $this->finishScan($this->startScan('enroll', 2), $this->samples())->assertStatus(422);
        $this->assertDatabaseCount('worker_face_enrollments', 1);
    }

    public function test_expired_scan_and_cancelled_scan_cannot_match(): void
    {
        $this->enroll();
        $scan = $this->startScan();
        DB::table('worker_face_attempts')->where('aws_session_id', $scan['session_id'])->update(['expires_at' => now()->subSecond()]);
        $this->finishScan($scan, $this->samples())->assertStatus(409);
        $scan = $this->startScan();
        $this->postJson(route('supervisor.face-attendance.cancel'), ['session_id' => $scan['session_id']])->assertOk();
        $this->finishScan($scan, $this->samples())->assertStatus(409);
    }

    public function test_unassigned_workers_and_invalid_templates_cannot_enroll(): void
    {
        DB::table('project_workers')->where('worker_id', 2)->update(['is_active' => false]);
        $this->postJson(route('supervisor.face-attendance.start'), [
            'purpose' => 'enroll', 'worker_id' => 2, 'supervisor_confirmed_identity' => true, 'privacy_notice_acknowledged' => true,
        ])->assertForbidden();
        $scan = $this->startScan('enroll');
        $this->finishScan($scan, [[1], [1], [1]])->assertStatus(422);
        $this->finishScan($scan, $this->samples(0))->assertStatus(422);
        $this->assertDatabaseCount('worker_face_enrollments', 0);
    }

    public function test_confirmation_is_required_and_rejected_match_removes_proof(): void
    {
        $this->enroll();
        $this->finishScan($this->startScan(), $this->samples())->assertOk();
        $payload = ['worker_id' => 1, 'log_date' => now('Asia/Manila')->toDateString(), 'action' => 'time_in', 'verification_method' => 'face'];
        $this->postJson(route('supervisor.attendance.logWorker'), $payload)->assertStatus(422);
        $this->postJson(route('supervisor.face-attendance.cancel'))->assertOk();
        $payload['supervisor_confirmed_identity'] = true;
        $this->postJson(route('supervisor.attendance.logWorker'), $payload)->assertForbidden();
    }

    public function test_ambiguous_face_match_is_rejected(): void
    {
        $this->enroll();
        $row = (array) DB::table('worker_face_enrollments')->first();
        unset($row['id']);
        $row['worker_id'] = 2;
        $row['aws_face_id'] = 'local:test-second';
        DB::table('worker_face_enrollments')->insert($row);
        $this->finishScan($this->startScan(), $this->samples())->assertStatus(422);
        $this->assertDatabaseCount('attendance_logs', 0);
    }

    public function test_same_phone_can_record_two_workers_and_reject_duplicate_time_in(): void
    {
        $this->enroll();
        $this->finishScan($this->startScan('enroll', 2), $this->samples(-1))->assertOk();
        foreach ([1 => 1, 2 => -1] as $id => $value) {
            $this->finishScan($this->startScan(), $this->samples($value))->assertOk()->assertJsonPath('worker.worker_id', $id);
            $this->postJson(route('supervisor.attendance.logWorker'), [
                'worker_id' => $id, 'log_date' => now('Asia/Manila')->toDateString(),
                'action' => 'time_in', 'verification_method' => 'face', 'supervisor_confirmed_identity' => true,
            ])->assertOk();
        }
        $this->assertDatabaseCount('attendance_logs', 2);
        $this->finishScan($this->startScan(), $this->samples())->assertOk();
        $this->postJson(route('supervisor.attendance.logWorker'), [
            'worker_id' => 1, 'log_date' => now('Asia/Manila')->toDateString(),
            'action' => 'time_in', 'verification_method' => 'face', 'supervisor_confirmed_identity' => true,
        ])->assertStatus(409);
    }

    public function test_assignment_revoked_after_matching_prevents_attendance(): void
    {
        $this->enroll();
        $this->finishScan($this->startScan(), $this->samples())->assertOk();
        DB::table('project_supervisors')->update(['is_active' => false]);
        $this->postJson(route('supervisor.attendance.logWorker'), [
            'worker_id' => 1, 'log_date' => now('Asia/Manila')->toDateString(),
            'action' => 'time_in', 'verification_method' => 'face', 'supervisor_confirmed_identity' => true,
        ])->assertForbidden();
        $this->assertDatabaseCount('attendance_logs', 0);
    }

    public function test_camera_page_loads_with_local_models_and_assigned_workers(): void
    {
        $this->get(route('supervisor.face-attendance.index'))->assertOk()
            ->assertSee('Shared phone camera attendance')
            ->assertSee('models/human')
            ->assertSee('Worker 1');
    }

    public function test_saved_faces_are_reusable_after_supervisor_signs_in_with_a_new_session(): void
    {
        $this->enroll();
        $this->finishScan($this->startScan('enroll', 2), $this->samples(-1))->assertOk();
        $supervisor = auth()->user();
        auth()->logout();
        $this->flushSession();
        $this->actingAs($supervisor);
        foreach ([1 => 1, 2 => -1] as $id => $value) {
            $this->finishScan($this->startScan(), $this->samples($value))->assertOk()
                ->assertJsonPath('worker.worker_id', $id);
            $this->postJson(route('supervisor.attendance.logWorker'), [
                'worker_id' => $id, 'log_date' => now('Asia/Manila')->toDateString(),
                'action' => 'time_in', 'verification_method' => 'face', 'supervisor_confirmed_identity' => true,
            ])->assertOk();
        }
        $this->assertDatabaseCount('workers', 2);
        $this->assertDatabaseCount('worker_face_enrollments', 2);
        $this->assertDatabaseCount('attendance_logs', 2);
    }

    public function test_new_worker_registration_needs_no_device_passkey_and_assigns_the_project(): void
    {
        $response = $this->postJson(route('supervisor.workers.register_camera'), [
            'first_name' => 'New', 'last_name' => 'Worker', 'project_id' => 1,
            'schedule_start' => '07:00', 'schedule_end' => '17:00', 'break_minutes' => 60,
            'model_version' => config('face_attendance.model_version'), 'face_samples' => $this->samples(),
        ])->assertOk();
        $id = $response->json('worker.worker_id');
        $this->assertDatabaseHas('project_workers', ['worker_id' => $id, 'project_id' => 1, 'is_active' => 1]);
        $this->finishScan($this->startScan(), $this->samples())->assertOk()->assertJsonPath('worker.worker_id', $id);
        $this->assertDatabaseHas('worker_face_enrollments', ['worker_id' => $id]);
        $this->assertDatabaseCount('workers', 3);
    }

    public function test_main_attendance_screen_uses_face_camera_instead_of_device_passkeys(): void
    {
        $this->get(route('supervisor.attendance'))->assertOk()
            ->assertSee('Facial Recognition')->assertSee('id="face-attendance-inline"', false)
            ->assertDontSee('workerFaceCameraModal')->assertDontSee('<iframe', false)
            ->assertSee('bi-person-bounding-box')->assertSee('regProjectId')
            ->assertDontSee('SimpleWebAuthnBrowser')->assertDontSee('passkeys/login')
            ->assertDontSee('bi-fingerprint')->assertDontSee('Facial Attendance');
        $this->get(route('supervisor.face-attendance.index', ['embedded' => 1]))->assertOk()
            ->assertSee('<title>Worker face recognition</title>', false);
        $this->getJson(route('supervisor.face-attendance.index'))->assertOk()
            ->assertJsonPath('faceWorkerChoices.0.worker_id', 1)
            ->assertJsonMissingPath('faceWorkerChoices.0.face_descriptors');
    }

    public function test_registration_requires_face_capture_and_rejects_duplicate_faces_without_creating_workers(): void
    {
        $data = [
            'first_name' => 'New', 'last_name' => 'Worker', 'project_id' => 1,
            'schedule_start' => '07:00', 'schedule_end' => '17:00', 'break_minutes' => 60,
        ];
        $this->postJson(route('supervisor.workers.register_camera'), $data)->assertUnprocessable()
            ->assertJsonValidationErrors(['face_samples', 'model_version']);
        $this->enroll();
        $this->postJson(route('supervisor.workers.register_camera'), $data + [
            'model_version' => config('face_attendance.model_version'), 'face_samples' => $this->samples(),
        ])->assertUnprocessable();
        $this->assertDatabaseCount('workers', 2);
        $this->assertDatabaseCount('worker_face_enrollments', 1);
    }

    public function test_updating_face_replaces_template_on_same_worker_record_without_checkbox(): void
    {
        $this->enroll();
        $this->finishScan($this->startScan('enroll'), $this->samples(-1))->assertOk();
        $this->finishScan($this->startScan(), $this->samples(-1))->assertOk()->assertJsonPath('worker.worker_id', 1);
        $this->assertDatabaseCount('worker_face_enrollments', 1);
        $this->assertDatabaseCount('workers', 2);
    }

    public function test_registration_preserves_encrypted_full_precision_samples_larger_than_mysql_text_limit(): void
    {
        $samples = array_fill(0, 3, array_fill(0, 1024, 0.12345678901234568));
        $response = $this->postJson(route('supervisor.workers.register_camera'), [
            'first_name' => 'Full', 'last_name' => 'Precision', 'project_id' => 1,
            'schedule_start' => '07:00', 'schedule_end' => '17:00', 'break_minutes' => 60,
            'model_version' => config('face_attendance.model_version'), 'face_samples' => $samples,
        ])->assertOk();
        $encrypted = DB::table('worker_face_enrollments')
            ->where('worker_id', $response->json('worker.worker_id'))->value('face_descriptors');
        $this->assertGreaterThan(65535, strlen($encrypted));
        $this->assertEquals($samples, json_decode(Crypt::decryptString($encrypted), true));
        $this->assertDatabaseCount('workers', 3);
    }

    public function test_admin_can_register_and_enroll_workers_outside_supervisor_assignments(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin-camera@example.com', 'password' => bcrypt('password'), 'role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin);
        $this->get(route('admin.attendance'))->assertOk()
            ->assertSee('Register Worker')->assertSee('Enrolled Workers')
            ->assertSee('adminEnrolledWorkersModal');
        $this->getJson(route('admin.face-attendance.index'))->assertOk()->assertJsonCount(2, 'faceWorkerChoices');
        $this->get(route('admin.face-attendance.index', ['embedded' => 1]))->assertOk()->assertSee('data-enrollment-only="1"', false);
        $scan = $this->postJson(route('admin.face-attendance.start'), ['purpose' => 'enroll', 'worker_id' => 1])->assertOk()->json();
        $this->postJson(route('admin.face-attendance.finish'), ['session_id' => $scan['session_id'], 'model_version' => config('face_attendance.model_version'), 'samples' => $this->samples()])->assertOk();
        $this->assertDatabaseHas('worker_face_enrollments', ['worker_id' => 1, 'enrolled_by' => $admin->user_id]);
        $this->postJson(route('admin.workers.register_camera'), [
            'first_name' => 'Admin', 'last_name' => 'Registered', 'project_id' => 1,
            'schedule_start' => '07:00', 'schedule_end' => '17:00', 'break_minutes' => 60,
            'model_version' => config('face_attendance.model_version'), 'face_samples' => $this->samples(-1),
        ])->assertOk();
        $this->assertDatabaseCount('workers', 3);
        $this->assertDatabaseCount('worker_face_enrollments', 2);
    }

    public function test_supervisors_cannot_use_admin_registration_routes(): void
    {
        $this->getJson(route('admin.face-attendance.index'))->assertRedirect('/login');
        $this->postJson(route('admin.workers.register_camera'), [])->assertRedirect('/login');
    }

    private function addSecondAttendanceProject(): void
    {
        DB::table('projects')->insert(['project_id' => 2, 'project_name' => 'Second Site', 'status' => 'ongoing']);
        DB::table('project_supervisors')->insert(['project_id' => 2, 'supervisor_id' => auth()->id(), 'is_active' => true]);
        DB::table('project_workers')->insert(['project_id' => 2, 'worker_id' => 1, 'deployed_date' => '2026-10-21', 'is_active' => true]);
    }

    public function test_selected_project_is_saved_and_both_supervisor_and_admin_filters_find_it(): void
    {
        $this->addSecondAttendanceProject();
        $this->get(route('supervisor.attendance', ['project_id' => 2]))->assertOk()
            ->assertSee('attendanceProjectInput')->assertSee('Project: Second Site');
        $this->enroll();
        $this->finishScan($this->startScan(), $this->samples())->assertOk();
        $this->postJson(route('supervisor.attendance.logWorker'), [
            'project_id' => 2, 'worker_id' => 1, 'log_date' => now('Asia/Manila')->toDateString(),
            'action' => 'time_in', 'verification_method' => 'face', 'supervisor_confirmed_identity' => true,
        ])->assertOk();
        $deploymentId = DB::table('project_workers')->where('project_id', 2)->value('deployment_id');
        $this->assertDatabaseHas('attendance_logs', ['worker_id' => 1, 'project_id' => 2, 'deployment_id' => $deploymentId]);
        $this->getJson(route('supervisor.attendance.today', ['project_id' => 1, 'date' => now('Asia/Manila')->toDateString()]))->assertOk()->assertJsonCount(0, 'data');
        $this->getJson(route('supervisor.attendance.today', ['project_id' => 2, 'date' => now('Asia/Manila')->toDateString()]))->assertOk()->assertJsonCount(1, 'data');
        $admin = User::create(['name' => 'Filter Admin', 'email' => 'filter-admin@example.com', 'password' => bcrypt('password'), 'role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin);
        $this->get(route('admin.attendance', ['project_id' => 2, 'date' => now('Asia/Manila')->toDateString()]))->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->count() === 1);
        $this->get(route('admin.attendance', ['project_id' => 1, 'date' => now('Asia/Manila')->toDateString()]))->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->count() === 0);
    }

    public function test_manual_actions_stay_in_the_selected_project_and_require_deployment(): void
    {
        $this->addSecondAttendanceProject();
        $payload = ['worker_id' => 1, 'project_id' => 2, 'log_date' => now('Asia/Manila')->toDateString(),
            'action' => 'time_in', 'manual' => true, 'remarks' => 'Camera unavailable', 'time_in' => '07:00'];
        $this->postJson(route('supervisor.attendance.logWorker'), $payload)->assertOk();
        $payload['project_id'] = 1;
        $this->postJson(route('supervisor.attendance.logWorker'), $payload)->assertOk();
        $this->assertDatabaseCount('attendance_logs', 2);
        $payload['project_id'] = 2; $payload['time_in'] = '08:00';
        $this->postJson(route('supervisor.attendance.logWorker'), $payload)->assertOk();
        $this->assertDatabaseHas('attendance_logs', ['project_id' => 1, 'time_in' => '07:00:00']);
        $this->assertDatabaseHas('attendance_logs', ['project_id' => 2, 'time_in' => '08:00:00']);
        $payload['worker_id'] = 2;
        $this->postJson(route('supervisor.attendance.logWorker'), $payload)->assertForbidden();
        $payload['project_id'] = 999;
        $this->postJson(route('supervisor.attendance.logWorker'), $payload)->assertForbidden();
        unset($payload['project_id']);
        $this->postJson(route('supervisor.attendance.logWorker'), $payload)->assertUnprocessable();
    }

    public function test_manual_attendance_requires_a_reason_and_event_time(): void
    {
        $payload = [
            'worker_id' => 1,
            'project_id' => 1,
            'log_date' => now('Asia/Manila')->toDateString(),
            'action' => 'time_in',
            'manual' => true,
        ];

        $this->postJson(route('supervisor.attendance.logWorker'), $payload)
            ->assertUnprocessable()
            ->assertJsonFragment(['message' => 'Please provide a reason for manual attendance.']);

        $payload['remarks'] = 'Camera unavailable';
        $this->postJson(route('supervisor.attendance.logWorker'), $payload)
            ->assertUnprocessable()
            ->assertJsonFragment(['message' => 'Please provide the time for the manual attendance action.']);
    }

    public function test_attendance_summary_counts_assigned_checked_in_and_late_for_selected_date(): void
    {
        DB::table('attendance_logs')->insert([
            'worker_id' => 1, 'project_id' => 1, 'deployment_id' => 1,
            'log_date' => '2026-10-21', 'time_in' => '09:00:00', 'status' => 'late',
        ]);
        $this->getJson(route('supervisor.attendance.today', ['project_id' => 1, 'date' => '2026-10-21']))
            ->assertOk()->assertJsonPath('summary.assigned', 2)->assertJsonPath('summary.checked_in', 1)
            ->assertJsonPath('summary.late', 1)->assertJsonPath('summary.not_checked_in', 1);
        $this->getJson(route('supervisor.attendance.today', ['project_id' => 1, 'date' => '2026-10-22']))
            ->assertOk()->assertJsonPath('summary.assigned', 2)->assertJsonPath('summary.checked_in', 0)
            ->assertJsonPath('summary.late', 0)->assertJsonPath('summary.not_checked_in', 2);
    }
}
