<?php

namespace Tests\Unit;

use App\Models\Project;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Tests\TestCase;

class ProjectModelTest extends TestCase
{
    public function test_project_materials_relation_is_available(): void
    {
        $project = new Project();

        $relation = $project->projectMaterials();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertSame('project_id', $relation->getForeignKeyName());
    }

    public function test_project_attendance_logs_relation_is_available(): void
    {
        $project = new Project();

        $relation = $project->attendanceLogs();

        $this->assertInstanceOf(HasManyThrough::class, $relation);
        $this->assertSame('project_id', $relation->getLocalKeyName());
    }
}
