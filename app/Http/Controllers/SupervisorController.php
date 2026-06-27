<?php

namespace App\Http\Controllers;

use App\Models\ConstructionPhase;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SupervisorController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $assignedProjects = Project::whereHas('supervisors', function ($q) use ($user) {
            $q->where('supervisor_id', $user->user_id);
        })->with(['phases', 'client.user', 'engineer'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalProjects = $assignedProjects->count();
        $hasApprovalStatus = Schema::hasColumn('accomplishment_reports', 'approval_status');
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

        $delayedMilestones = Milestone::whereHas('phase', function ($q) use ($assignedProjects) {
            $q->whereIn('project_id', $assignedProjects->pluck('project_id'));
        })->where('is_delayed', true)
            ->where('is_completed', false)
            ->with(['phase.project'])
            ->orderBy('planned_date')
            ->get();

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

    public function timeline()
    {
        return view('supervisor.timeline');
    }

    public function attendance()
    {
        return view('supervisor.attendance');
    }

    public function materials()
    {
        return view('supervisor.material');
    }

    public function saveAttendance(Request $request)
    {
        return back()->with('success', 'Attendance saved successfully.');
    }

    public function logDelivery(Request $request)
    {
        return back()->with('success', 'Delivery logged successfully.');
    }
}