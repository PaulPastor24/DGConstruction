<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ConstructionPhase;
use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class ReportRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_reports_evaluate_route_is_defined(): void
    {
        $this->assertTrue(Route::has('admin.reports.evaluate'));
    }

    public function test_engineer_approval_updates_review_timestamp_and_remarks(): void
    {
        /** @var User $engineer */
        $engineer = User::factory()->create(['role' => 'engineer']);
        $clientUser = User::factory()->create(['role' => 'client']);
        $client = Client::create([
            'user_id' => $clientUser->user_id,
            'company_name' => 'Test Client',
            'address' => 'Client Address',
        ]);

        $project = Project::create([
            'project_name' => 'Test Project',
            'project_location' => 'Site A',
            'client_id' => $client->client_id,
            'engineer_id' => $engineer->user_id,
            'start_date' => now()->toDateString(),
            'target_end_date' => now()->addDays(30)->toDateString(),
            'status' => 'ongoing',
            'created_by' => $engineer->user_id,
        ]);

        $phase = ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Mobilization',
            'phase_order' => 1,
            'planned_start_date' => now()->toDateString(),
            'planned_end_date' => now()->addDays(10)->toDateString(),
            'status' => 'in_progress',
            'completion_percentage' => 30,
        ]);

        $report = Report::create([
            'project_id' => $project->project_id,
            'phase_id' => $phase->phase_id,
            'submitted_by' => $engineer->user_id,
            'report_date' => now()->toDateString(),
            'report_text' => 'Work completed',
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($engineer)
            ->postJson(route('admin.reports.evaluate', ['id' => $report->report_id]), [
                'decision' => 'reject',
                'approval_remarks' => 'Needs correction',
            ]);

        $response->assertOk();

        $report->refresh();
        $this->assertSame('rejected', $report->approval_status);
        $this->assertSame('Needs correction', $report->approval_remarks);
        $this->assertNotNull($report->reviewed_at);
        $this->assertSame($engineer->user_id, $report->reviewed_by);
    }

    public function test_approving_report_updates_phase_progress_without_accepting_manual_percentage_override(): void
    {
        /** @var User $engineer */
        $engineer = User::factory()->create(['role' => 'engineer']);
        $clientUser = User::factory()->create(['role' => 'client']);
        $client = Client::create([
            'user_id' => $clientUser->user_id,
            'company_name' => 'Progress Client',
            'address' => 'Client Address',
        ]);

        $project = Project::create([
            'project_name' => 'Progress Project',
            'project_location' => 'Site B',
            'client_id' => $client->client_id,
            'engineer_id' => $engineer->user_id,
            'start_date' => now()->toDateString(),
            'target_end_date' => now()->addDays(30)->toDateString(),
            'status' => 'ongoing',
            'created_by' => $engineer->user_id,
        ]);

        $phase = ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Foundations',
            'phase_order' => 1,
            'planned_start_date' => now()->toDateString(),
            'planned_end_date' => now()->addDays(10)->toDateString(),
            'status' => 'in_progress',
            'completion_percentage' => 10,
        ]);

        $report = Report::create([
            'project_id' => $project->project_id,
            'phase_id' => $phase->phase_id,
            'submitted_by' => $engineer->user_id,
            'report_date' => now()->toDateString(),
            'report_text' => 'Work completed',
            'approval_status' => 'pending',
        ]);

        DB::table('ai_analysis_results')->insert([
            'report_id' => $report->report_id,
            'phase_id' => $phase->phase_id,
            'identified_activities' => 'Structural completion',
            'computed_progress' => 40.00,
            'confidence_score' => 95.00,
            'raw_ai_output' => '{}',
            'processed_at' => now(),
        ]);

        $response = $this->actingAs($engineer)
            ->postJson(route('admin.reports.approve', ['id' => $report->report_id]), [
                'approval_remarks' => 'Approved',
                'completion_percentage' => 80,
            ]);

        $response->assertOk();

        $phase->refresh();
        $this->assertGreaterThan(10, (float) $phase->completion_percentage);
        $this->assertNotSame(80.0, (float) $phase->completion_percentage);
    }

    public function test_creating_phase_sets_pending_status_and_zero_progress(): void
    {
        /** @var User $engineer */
        $engineer = User::factory()->create(['role' => 'engineer']);
        $clientUser = User::factory()->create(['role' => 'client']);
        $client = Client::create([
            'user_id' => $clientUser->user_id,
            'company_name' => 'Phase Client',
            'address' => 'Client Address',
        ]);

        $project = Project::create([
            'project_name' => 'Create Phase Project',
            'project_location' => 'Site C',
            'client_id' => $client->client_id,
            'engineer_id' => $engineer->user_id,
            'start_date' => now()->toDateString(),
            'target_end_date' => now()->addDays(30)->toDateString(),
            'status' => 'ongoing',
            'created_by' => $engineer->user_id,
        ]);

        $response = $this->actingAs($engineer)
            ->postJson(route('admin.phases.store'), [
                'project_id' => $project->project_id,
                'phase_name' => 'Structural Works',
                'phase_order' => 2,
                'planned_start_date' => now()->toDateString(),
                'planned_end_date' => now()->addDays(10)->toDateString(),
            ]);

        $response->assertOk();

        $phase = ConstructionPhase::query()->where('project_id', $project->project_id)->latest('phase_id')->first();
        $this->assertNotNull($phase);
        $this->assertSame('not_started', $phase->status);
        $this->assertSame('0.00', (string) $phase->completion_percentage);
    }

    public function test_admin_report_details_returns_success_when_attendance_table_is_missing_expected_columns(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'engineer']);
        $clientUser = User::factory()->create(['role' => 'client']);
        $client = Client::create([
            'user_id' => $clientUser->user_id,
            'company_name' => 'Detail Client',
            'address' => 'Client Address',
        ]);

        $project = Project::create([
            'project_name' => 'Test Project',
            'project_location' => 'Site A',
            'client_id' => $client->client_id,
            'engineer_id' => $user->user_id,
            'start_date' => now()->toDateString(),
            'target_end_date' => now()->addDays(30)->toDateString(),
            'status' => 'ongoing',
            'created_by' => $user->user_id,
        ]);

        $phase = ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Mobilization',
            'phase_order' => 1,
            'planned_start_date' => now()->toDateString(),
            'planned_end_date' => now()->addDays(10)->toDateString(),
            'status' => 'in_progress',
            'completion_percentage' => 30,
        ]);

        $report = Report::create([
            'project_id' => $project->project_id,
            'phase_id' => $phase->phase_id,
            'submitted_by' => $user->user_id,
            'report_date' => now()->toDateString(),
            'report_text' => 'Work completed',
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('admin.reports.details', ['id' => $report->report_id]));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('report.id', $report->report_id);
    }
}
