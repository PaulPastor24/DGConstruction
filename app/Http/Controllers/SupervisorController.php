<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Report;
use App\Models\ConstructionPhase;
use App\Models\Milestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SupervisorController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get all projects assigned to this supervisor
        $assignedProjects = Project::whereHas('supervisors', function ($q) use ($user) {
            $q->where('supervisor_id', $user->user_id);
        })->with(['phases', 'client.user', 'engineer'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get dashboard metrics
        $totalProjects = $assignedProjects->count();
        $hasApprovalStatus = Schema::hasColumn('accomplishment_reports', 'approval_status');

        // Get current phases (phases that are in_progress)
        $assignedProjectIds = $assignedProjects->pluck('project_id')->all();

        $currentPhases = ConstructionPhase::query()
            ->where(function ($query) use ($assignedProjectIds) {
                foreach ($assignedProjectIds as $projectId) {
                    $query->orWhere('project_id', $projectId);
                }
            })
            ->where('status', 'in_progress')
            ->with('project')
            ->get();

        // Get delayed milestones
        $delayedMilestones = Milestone::whereHas('phase', function ($q) use ($assignedProjects) {
            $q->whereIn('project_id', $assignedProjects->pluck('project_id'));
        })->where('is_delayed', true)
            ->where('is_completed', false)
            ->with(['phase.project'])
            ->orderBy('planned_date')
            ->get();

        // Get pending reports (fall back to all reports if the approval column is not available)
        $pendingReports = Report::query()
            ->where(function ($query) use ($assignedProjectIds) {
                foreach ($assignedProjectIds as $projectId) {
                    $query->orWhere('project_id', $projectId);
                }
            })
            ->where('submitted_by', $user->user_id)
            ->when($hasApprovalStatus, function ($query) {
                $query->where('approval_status', 'pending');
            })
            ->with(['project', 'phase'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent approved reports (only when the approval workflow columns exist)
        $approvedReports = $hasApprovalStatus
            ? Report::query()
                ->where(function ($query) use ($assignedProjectIds) {
                    foreach ($assignedProjectIds as $projectId) {
                        $query->orWhere('project_id', $projectId);
                    }
                })
                ->where('submitted_by', $user->user_id)
                ->where('approval_status', 'approved')
                ->with(['project', 'phase'])
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get()
            : collect();

        // Get recent rejected reports (only when the approval workflow columns exist)
        $rejectedReports = $hasApprovalStatus
            ? Report::query()
                ->where(function ($query) use ($assignedProjectIds) {
                    foreach ($assignedProjectIds as $projectId) {
                        $query->orWhere('project_id', $projectId);
                    }
                })
                ->where('submitted_by', $user->user_id)
                ->where('approval_status', 'rejected')
                ->with(['project', 'phase'])
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get()
            : collect();

        // Calculate overall statistics
        $stats = [
            'total_projects' => $totalProjects,
            'active_projects' => $assignedProjects->filter(fn($p) => $p->status === 'ongoing')->count(),
            'current_phases' => $currentPhases->count(),
            'delayed_milestones' => $delayedMilestones->count(),
            'pending_reports' => $pendingReports->count(),
            'approved_reports' => $approvedReports->count(),
            'rejected_reports' => $rejectedReports->count(),
            'average_completion' => $assignedProjects->isEmpty() ? 0 : round(
                $assignedProjects->flatMap(fn($p) => $p->phases)->avg('completion_percentage') ?? 0,
                2
            ),
        ];

        // Get upcoming milestones (next 7 days)
        $upcomingMilestones = Milestone::whereHas('phase', function ($q) use ($assignedProjects) {
            $q->whereIn('project_id', $assignedProjects->pluck('project_id'));
        })->where('is_completed', false)
            ->where('is_delayed', false)
            ->whereBetween('planned_date', [now(), now()->addDays(7)])
            ->with(['phase.project'])
            ->orderBy('planned_date')
            ->get();

        return view('supervisor.dashboard', compact(
            'user',
            'assignedProjects',
            'currentPhases',
            'delayedMilestones',
            'upcomingMilestones',
            'pendingReports',
            'approvedReports',
            'rejectedReports',
            'stats'
        ));
    }
}