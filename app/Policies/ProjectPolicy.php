<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Can the user view this project
     */
    public function view(User $user, Project $project): bool
    {
        // Engineer can view if they're the project engineer
        if ($user->role === 'engineer') {
            return $user->user_id === $project->engineer_id;
        }

        // Supervisor can view if assigned
        if ($user->role === 'site_supervisor') {
            return $project->supervisors()->where('supervisor_id', $user->user_id)->exists();
        }

        // Client can view if it's their project
        if ($user->role === 'client') {
            return $project->client_id === $user->client?->client_id;
        }

        return false;
    }

    /**
     * Can the user create a project
     */
    public function create(User $user): bool
    {
        return $user->role === 'engineer';
    }

    /**
     * Can the user update this project
     */
    public function update(User $user, Project $project): bool
    {
        return $user->role === 'engineer' && $user->user_id === $project->engineer_id;
    }

    /**
     * Can the user delete this project
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->role === 'engineer' && $user->user_id === $project->engineer_id;
    }

    /**
     * Can the user manage phases
     */
    public function managePhases(User $user, Project $project): bool
    {
        return $user->role === 'engineer' && $user->user_id === $project->engineer_id;
    }

    /**
     * Can the user manage milestones
     */
    public function manageMilestones(User $user, Project $project): bool
    {
        return $user->role === 'engineer' && $user->user_id === $project->engineer_id;
    }

    /**
     * Can the user approve reports
     */
    public function approveReports(User $user, Project $project): bool
    {
        return $user->role === 'engineer' && $user->user_id === $project->engineer_id;
    }
}
