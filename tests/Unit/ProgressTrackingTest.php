<?php

namespace Tests\Unit;

use App\Models\ConstructionPhase;
use App\Models\Milestone;
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
}
