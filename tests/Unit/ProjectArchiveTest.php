<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\ProjectArchive;
use Tests\TestCase;

class ProjectArchiveTest extends TestCase
{
    public function test_it_can_create_an_archive_snapshot_from_a_project(): void
    {
        $project = new Project([
            'project_id' => 42,
            'project_name' => 'North Tower',
            'project_location' => 'Cebu City',
            'client_id' => 7,
            'engineer_id' => 3,
            'start_date' => '2026-01-15',
            'target_end_date' => '2026-08-01',
            'actual_end_date' => null,
            'status' => 'completed',
            'description' => 'Ready for archive.',
        ]);

        $archive = ProjectArchive::fromProject($project);

        $this->assertInstanceOf(ProjectArchive::class, $archive);
        $this->assertSame(42, $archive->getAttribute('project_id'));
        $this->assertSame('North Tower', $archive->project_name);
        $this->assertSame('Cebu City', $archive->project_location);
        $this->assertSame('archived', $archive->status);
        $this->assertSame('Ready for archive.', $archive->description);
    }

    public function test_it_falls_back_to_the_legacy_location_attribute_when_needed(): void
    {
        $project = new Project();
        $project->forceFill([
            'project_id' => 43,
            'project_name' => 'South Wing',
            'location' => 'Davao City',
            'client_id' => 8,
            'engineer_id' => 4,
            'start_date' => '2026-02-10',
            'target_end_date' => '2026-09-15',
            'actual_end_date' => null,
            'status' => 'completed',
            'description' => 'Archive fallback.',
        ]);

        $archive = ProjectArchive::fromProject($project);

        $this->assertSame('Davao City', $archive->project_location);
        $this->assertSame('South Wing', $archive->project_name);
    }
}
