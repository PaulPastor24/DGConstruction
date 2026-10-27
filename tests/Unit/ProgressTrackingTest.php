<?php

namespace Tests\Unit;

use App\Models\ConstructionPhase;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class ProgressTrackingTest extends TestCase
{
    public function test_phase_uses_binary_milestone_average_without_override(): void
    {
        $phase = new ConstructionPhase();
        $phase->setRelation('milestones', new Collection([
            new Milestone(['is_completed' => true]),
            new Milestone(['is_completed' => false]),
            new Milestone(['is_completed' => false]),
        ]));

        $this->assertSame(33.33, $phase->progress_percentage);
    }

    public function test_phase_override_replaces_milestone_calculation(): void
    {
        $phase = new ConstructionPhase(['admin_progress_override' => 65]);
        $phase->setRelation('milestones', new Collection([
            new Milestone(['is_completed' => true]),
            new Milestone(['is_completed' => false]),
        ]));

        $this->assertSame(65.0, $phase->progress_percentage);
    }

    public function test_milestones_are_binary_and_have_no_progress_field(): void
    {
        $milestone = new Milestone();

        $this->assertArrayNotHasKey('progress_percentage', $milestone->getFillable());
        $this->assertArrayNotHasKey('progress_percentage', $milestone->getCasts());
    }

    public function test_phase_override_audit_fields_are_mass_assignable(): void
    {
        $phase = new ConstructionPhase();

        $this->assertContains('override_reason', $phase->getFillable());
        $this->assertContains('override_applied_at', $phase->getFillable());
        $this->assertContains('override_applied_by', $phase->getFillable());
    }

    public function test_project_detects_explicitly_delayed_phase_or_milestone(): void
    {
        $phase = new ConstructionPhase(['status' => ConstructionPhase::STATUS_DELAYED]);
        $phase->setRelation('milestones', new Collection());

        $project = new Project();
        $project->setRelation('phases', new Collection([$phase]));

        $this->assertTrue($project->hasDelayedWork());

        $milestone = new Milestone(['is_delayed' => true, 'is_completed' => false]);
        $phase = new ConstructionPhase(['status' => ConstructionPhase::STATUS_IN_PROGRESS]);
        $phase->setRelation('milestones', new Collection([$milestone]));
        $project->setRelation('phases', new Collection([$phase]));

        $this->assertTrue($project->hasDelayedWork());
    }

    public function test_ongoing_status_variants_are_recognized_and_labeled_consistently(): void
    {
        $ongoingStatuses = Project::statusVariants(Project::STATUS_ONGOING);

        $this->assertContains('in_progress', $ongoingStatuses);
        $this->assertContains('active', $ongoingStatuses);
        $this->assertSame('In Progress', Project::statusLabel('in_progress'));
        $this->assertSame('In Progress', Project::statusLabel('active'));
    }

    public function test_project_detects_overdue_project_phase_or_milestone_dates(): void
    {
        $project = new Project([
            'status' => Project::STATUS_ONGOING,
            'target_end_date' => now()->subDay()->toDateString(),
        ]);
        $project->setRelation('phases', new Collection());

        $this->assertTrue($project->hasDelayedWork());

        $phase = new ConstructionPhase([
            'status' => ConstructionPhase::STATUS_IN_PROGRESS,
            'planned_end_date' => now()->subDay()->toDateString(),
        ]);
        $phase->setRelation('milestones', new Collection());
        $project = new Project(['status' => Project::STATUS_ONGOING]);
        $project->setRelation('phases', new Collection([$phase]));

        $this->assertTrue($project->hasDelayedWork());

        $milestone = new Milestone([
            'is_completed' => false,
            'end_date' => now()->subDay()->toDateString(),
        ]);
        $phase = new ConstructionPhase(['status' => ConstructionPhase::STATUS_IN_PROGRESS]);
        $phase->setRelation('milestones', new Collection([$milestone]));
        $project = new Project(['status' => Project::STATUS_ONGOING]);
        $project->setRelation('phases', new Collection([$phase]));

        $this->assertTrue($project->hasDelayedWork());
    }
}
