<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectSupervisorAssignment
{
    public function assignNewProject(Project $project, ?int $requestedSupervisor = null): ?int
    {
        return DB::transaction(function () use ($project, $requestedSupervisor) {
            // Use the same lock order as bulk distribution to serialize assignment changes.
            $supervisors = User::where('role', 'supervisor')->where('is_active', true)
                ->orderBy('user_id')->lockForUpdate()->get();
            if ($requestedSupervisor !== null && ! $supervisors->contains('user_id', $requestedSupervisor)) {
                throw ValidationException::withMessages(['supervisor_id' => 'Choose an active Site Supervisor.']);
            }
            if ($supervisors->isEmpty()) {
                return null;
            }
            $loads = DB::table('project_supervisors')->join('projects', 'projects.project_id', '=', 'project_supervisors.project_id')
                ->where('project_supervisors.is_active', true)
                ->whereNotIn('projects.status', Project::statusVariants(Project::STATUS_ARCHIVED))
                ->selectRaw('supervisor_id, COUNT(DISTINCT projects.project_id) as total')
                ->groupBy('supervisor_id')->pluck('total', 'supervisor_id');
            $id = $requestedSupervisor ?? $supervisors->sortBy(fn ($supervisor) => (int) ($loads[$supervisor->user_id] ?? 0))->first()->user_id;
            $project->supervisors()->syncWithoutDetaching([$id => ['assigned_date' => now()->toDateString(), 'is_active' => true]]);

            return (int) $id;
        });
    }

    public function distribute(bool $apply = false, int $expectedSupervisors = 4): array
    {
        return DB::transaction(function () use ($apply, $expectedSupervisors) {
            $supervisors = User::where('role', 'supervisor')->where('is_active', true)
                ->orderBy('user_id')->lockForUpdate()->get();
            if ($supervisors->count() !== $expectedSupervisors) {
                throw ValidationException::withMessages([
                    'supervisors' => "Expected {$expectedSupervisors} active supervisors; found {$supervisors->count()}. No assignments were changed.",
                ]);
            }
            $projects = Project::query()->notArchived()->orderBy('project_id')->lockForUpdate()->get();
            $summary = $supervisors->mapWithKeys(fn ($supervisor) => [$supervisor->user_id => [
                'id' => $supervisor->user_id, 'name' => $supervisor->name,
                'email' => $supervisor->email, 'projects' => 0,
            ]])->all();
            foreach ($projects as $index => $project) {
                $id = $supervisors[$index % $supervisors->count()]->user_id;
                $summary[$id]['projects']++;
                if (! $apply) {
                    continue;
                }
                // Preserve historical assignment rows and all project/report data.
                $project->supervisors()->newPivotStatement()->where('project_id', $project->project_id)
                    ->where('supervisor_id', '!=', $id)->update(['is_active' => false]);
                $project->supervisors()->syncWithoutDetaching([$id => ['assigned_date' => now()->toDateString(), 'is_active' => true]]);
            }

            return array_values($summary);
        });
    }
}
