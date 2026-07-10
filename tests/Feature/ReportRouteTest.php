<?php

namespace Tests\Feature;

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
        $engineer = User::factory()->create(['role' => 'engineer']);

        $project = Project::create([
            'project_name' => 'Test Project',
            'project_location' => 'Site A',
            'status' => 'ongoing',
            'created_by' => $engineer->user_id,
        ]);

        $phase = ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Mobilization',
            'phase_order' => 1,
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
            ->postJson(route('admin.reports.reject', ['id' => $report->report_id]), [
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

    public function test_admin_report_details_returns_success_when_attendance_table_is_missing_expected_columns(): void
    {
        $user = User::factory()->create(['role' => 'engineer']);

        $project = Project::create([
            'project_name' => 'Test Project',
            'project_location' => 'Site A',
            'status' => 'ongoing',
            'created_by' => $user->user_id,
        ]);

        $phase = ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Mobilization',
            'phase_order' => 1,
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
