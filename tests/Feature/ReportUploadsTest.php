<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackUserPresence;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportUploadsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(TrackUserPresence::class);
        Storage::fake('local');
        Storage::fake('public');
        Schema::create('users', function ($t) {
            $t->id('user_id'); $t->string('role'); $t->boolean('is_active')->default(true); $t->timestamps();
        });
        Schema::create('projects', function ($t) {
            $t->id('project_id'); $t->string('project_name'); $t->string('status'); $t->timestamps();
        });
        Schema::create('construction_phases', function ($t) {
            $t->id('phase_id'); $t->integer('project_id'); $t->string('phase_name');
            $t->string('status'); $t->float('completion_percentage')->default(0); $t->timestamps();
        });
        Schema::create('project_supervisors', function ($t) {
            $t->integer('project_id'); $t->integer('supervisor_id'); $t->boolean('is_active')->default(false); $t->date('assigned_date')->nullable();
        });
        Schema::create('system_logs', function ($t) {
            $t->id('log_id'); $t->integer('user_id'); $t->string('action'); $t->text('description'); $t->string('ip_address')->nullable();
        });
        Schema::create('accomplishment_reports', function ($t) {
            $t->id('report_id'); $t->integer('project_id')->nullable(); $t->integer('phase_id')->nullable();
            $t->integer('submitted_by')->nullable(); $t->date('report_date')->nullable();
            foreach (['site_images', 'admin_site_images', 'report_text', 'admin_report_text', 'admin_explanation', 'approval_remarks'] as $field) $t->text($field)->nullable();
            $t->string('approval_status')->default('pending');
            $t->float('accomplishment_percentage')->nullable(); $t->boolean('is_published_to_client')->default(false);
            foreach (['published_at', 'reviewed_at', 'approved_at', 'rejected_at'] as $field) $t->timestamp($field)->nullable();
            $t->integer('reviewed_by')->nullable(); $t->integer('approved_by')->nullable(); $t->timestamps();
        });
        (require database_path('migrations/2026_10_28_000001_add_attachments_to_accomplishment_reports.php'))->up();
        DB::table('users')->insert([['user_id' => 1, 'role' => 'admin'], ['user_id' => 2, 'role' => 'supervisor']]);
        DB::table('projects')->insert(['project_id' => 1, 'project_name' => 'Test', 'status' => 'ongoing']);
        DB::table('construction_phases')->insert(['phase_id' => 1, 'project_id' => 1, 'phase_name' => 'Test phase', 'status' => 'in_progress']);
        DB::table('project_supervisors')->insert(['project_id' => 1, 'supervisor_id' => 2, 'is_active' => true]);
    }

    public function test_all_admin_roles_remove_supervisor_and_client_images_on_prepare_approval_and_update(): void
    {
        foreach (['admin', 'administrator', 'engineer'] as $role) {
            $user = User::findOrFail(1); $user->update(['role' => $role]); $this->actingAs($user);
            foreach (['prepare', 'approve', 'update'] as $action) {
                Storage::disk('public')->put('reports/test.jpg', 'image');
                $report = Report::create(['project_id' => 1, 'phase_id' => 1, 'site_images' => ['reports/test.jpg'], 'admin_site_images' => ['reports/test.jpg']]);
                $this->postJson(route('admin.reports.'.$action, $report->report_id), [
                    'remove_site_images' => ['reports/test.jpg'], 'approval_remarks' => 'Reviewed',
                ])->assertOk()->assertJson(['success' => true]);
                $this->assertNull($report->fresh()->site_images);
                $this->assertNull($report->fresh()->admin_site_images);
                Storage::disk('public')->assertMissing('reports/test.jpg');
            }
        }
    }

    public function test_image_used_by_another_report_is_preserved(): void
    {
        $this->actingAs(User::findOrFail(1));
        Storage::disk('public')->put('reports/shared.jpg', 'image');
        $report = Report::create(['site_images' => ['reports/shared.jpg']]);
        Report::create(['admin_site_images' => ['reports/shared.jpg']]);
        $this->postJson(route('admin.reports.prepare', $report->report_id), ['remove_admin_images' => ['reports/shared.jpg']])->assertOk();
        Storage::disk('public')->assertExists('reports/shared.jpg');
    }

    public function test_supervisor_uploads_a_private_pdf_and_admin_downloads_it(): void
    {
        $this->actingAs(User::findOrFail(2));
        $this->postJson(route('supervisor.reports.submit'), $this->submission([
            'attachments' => [UploadedFile::fake()->createWithContent('evidence.pdf', "%PDF-1.4\nTest document")],
        ]))->assertOk()->assertJson(['success' => true]);
        $report = Report::firstOrFail();
        $this->assertSame('evidence.pdf', $report->attachments[0]['name']);
        Storage::disk('local')->assertExists($report->attachments[0]['path']);
        Storage::disk('public')->assertMissing($report->attachments[0]['path']);
        $this->actingAs(User::findOrFail(1))->get(route('admin.reports.attachments.download', [$report->report_id, 0]))->assertDownload('evidence.pdf');
        $this->get(route('admin.reports.attachments.download', [$report->report_id, 1]))->assertNotFound();
        $this->actingAs(User::findOrFail(2))
            ->get(route('supervisor.reports.attachments.download', [$report->report_id, 0]))
            ->assertDownload('evidence.pdf');
        DB::table('project_supervisors')->where('project_id', 1)->where('supervisor_id', 2)->update(['is_active' => false]);
        $this->get(route('supervisor.reports.attachments.download', [$report->report_id, 0]))->assertForbidden();
        $this->get(route('supervisor.reports.show', $report->report_id))->assertForbidden();
        $this->get(route('supervisor.api.reports.details', $report->report_id))->assertNotFound();
        $this->get(route('supervisor.api.reports.phases', 1))->assertNotFound();
        $this->postJson(route('supervisor.reports.submit'), $this->submission([]))->assertForbidden();
        $this->postJson(route('supervisor.reports.update', $report->report_id), [
            'project_id' => 1,
            'phase_id' => 1,
            'report_date' => now()->format('Y-m-d\TH:i'),
            'report_text' => 'Attempted update after assignment revocation',
        ])->assertNotFound();
    }

    public function test_invalid_documents_and_upload_limits_are_rejected(): void
    {
        $this->actingAs(User::findOrFail(2));
        foreach ([
            ['attachments' => [UploadedFile::fake()->create('script.exe', 1, 'application/x-msdownload')]],
            ['attachments' => [UploadedFile::fake()->create('large.pdf', 10241, 'application/pdf')]],
            ['attachments' => array_fill(0, 11, UploadedFile::fake()->create('file.pdf', 1, 'application/pdf'))],
            ['site_images' => array_fill(0, 21, UploadedFile::fake()->create('image.jpg', 1, 'image/jpeg'))],
        ] as $input) {
            $this->postJson(route('supervisor.reports.submit'), $this->submission($input))->assertUnprocessable();
        }
        $this->assertDatabaseCount('accomplishment_reports', 0);
    }

    public function test_supervisor_edits_preserve_documents_and_enforce_combined_limits(): void
    {
        $this->actingAs(User::findOrFail(2));
        $documents = array_fill(0, 9, ['path' => 'reports/attachments/old.pdf', 'name' => 'old.pdf']);
        $report = Report::create(['project_id' => 1, 'phase_id' => 1, 'submitted_by' => 2, 'attachments' => $documents]);
        $input = $this->submission([
            'report_date' => now()->subMinute()->format('Y-m-d\TH:i'),
            'attachments' => [UploadedFile::fake()->createWithContent('new.pdf', "%PDF-1.4\nDocument")],
        ]);
        $this->postJson(route('supervisor.reports.update', $report->report_id), $input)->assertOk();
        $this->assertCount(10, $report->fresh()->attachments);
        $this->assertSame($documents[0], $report->fresh()->attachments[0]);
        $input['attachments'] = [UploadedFile::fake()->createWithContent('extra.pdf', "%PDF-1.4\nDocument")];
        $this->postJson(route('supervisor.reports.update', $report->report_id), $input)->assertUnprocessable();
        $this->assertCount(10, $report->fresh()->attachments);
    }

    private function submission(array $extra): array
    {
        return array_merge(['project_id' => 1, 'phase_id' => 1, 'report_date' => now()->toDateString(), 'report_text' => 'Daily progress'], $extra);
    }
}
