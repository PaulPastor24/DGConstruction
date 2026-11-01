<?php

namespace App\Console\Commands;

use App\Services\ProjectSupervisorAssignment;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class DistributeProjectsToSupervisors extends Command
{
    protected $signature = 'projects:distribute-supervisors {--apply : Save the assignments; otherwise preview only}';
    protected $description = 'Distribute non-archived projects evenly among exactly four active Site Supervisors';

    public function handle(ProjectSupervisorAssignment $assignments): int
    {
        try {
            $rows = $assignments->distribute((bool) $this->option('apply'));
        } catch (ValidationException $error) {
            $this->error($error->getMessage());
            return self::FAILURE;
        }
        $this->table(['ID', 'Supervisor', 'Email', 'Projects'], $rows);
        $this->info($this->option('apply') ? 'Assignments saved. Project data was preserved.' : 'Preview only. Run with --apply to save these assignments.');

        return self::SUCCESS;
    }
}
