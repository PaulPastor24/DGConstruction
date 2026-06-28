<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    /**
     * Can the user view this report
     */
    public function view(User $user, Report $report): bool
    {
        // Engineer can view all reports
        if ($user->role === 'engineer') {
            return true;
        }

        // Supervisor can view reports from their assigned projects
        if ($user->role === 'supervisor') {
            return $report->project->supervisors()
                ->where('supervisor_id', $user->user_id)
                ->exists();
        }

        // Client can view reports from their project
        if ($user->role === 'client') {
            return $report->project->client_id === $user->client?->client_id;
        }

        return false;
    }

    /**
     * Can the user submit a report
     */
    public function submit(User $user, Report $report): bool
    {
        // Only supervisors can submit reports, and for their assigned projects
        if ($user->role !== 'supervisor') {
            return false;
        }

        return $report->project->supervisors()
            ->where('supervisor_id', $user->user_id)
            ->exists();
    }

    /**
     * Can the user approve this report
     */
    public function approve(User $user, Report $report): bool
    {
        // Only engineers (project owners) can approve
        if ($user->role !== 'engineer') {
            return false;
        }

        return $user->user_id === $report->project->engineer_id;
    }

    /**
     * Can the user reject this report
     */
    public function reject(User $user, Report $report): bool
    {
        // Only engineers (project owners) can reject
        if ($user->role !== 'engineer') {
            return false;
        }

        return $user->user_id === $report->project->engineer_id;
    }

    /**
     * Can the user delete this report
     */
    public function delete(User $user, Report $report): bool
    {
        // Engineer (project owner) can always delete
        if ($user->role === 'engineer' && $user->user_id === $report->project->engineer_id) {
            return true;
        }

        // Supervisor can only delete their own pending reports
        if ($user->role === 'supervisor' && $user->user_id === $report->submitted_by) {
            return $report->approval_status === 'pending';
        }

        return false;
    }
}
