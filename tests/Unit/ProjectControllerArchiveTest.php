<?php

namespace Tests\Unit;

use App\Http\Controllers\ProjectController;
use Tests\TestCase;

class ProjectControllerArchiveTest extends TestCase
{
    public function test_archive_status_value_uses_supported_status(): void
    {
        $controller = new class extends ProjectController {
            public function exposeArchiveStatusValue(): string
            {
                return $this->getArchiveStatusValue();
            }

            public function exposeRestoreStatusValue(): string
            {
                return $this->getRestoreStatusValue();
            }
        };

        $this->assertSame('completed', $controller->exposeArchiveStatusValue());
        $this->assertSame('planning', $controller->exposeRestoreStatusValue());
    }
}
