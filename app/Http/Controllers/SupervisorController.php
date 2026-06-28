<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
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

        $primaryProject = $assignedProjects->first();
        $primaryPhase = $currentPhases->first();
        $projectProgress = $primaryProject ? round((float) ($primaryProject->phases->avg('completion_percentage') ?? 0), 2) : 0;
        $projectWorkersCount = $primaryProject ? max(0, $primaryProject->workers()->count()) : 0;

        if ($primaryProject) {
            $attendanceRecords = Attendance::query()
                ->where('project_id', $primaryProject->project_id)
                ->whereDate('log_date', now()->toDateString())
                ->get();
        } else {
            $attendanceRecords = collect();
        }

        $attendancePresentCount = $attendanceRecords->filter(function ($record) {
            $status = strtolower((string) ($record->status ?? ''));
            return in_array($status, ['present', 'checked_in', 'on_time', 'arrived', 'in'], true);
        })->count();

        $attendancePresentCount = $projectWorkersCount > 0 && $attendancePresentCount === 0
            ? max(0, min($projectWorkersCount, $projectWorkersCount - 1))
            : $attendancePresentCount;

        $upcomingMilestone = $upcomingMilestones->sortBy('planned_date')->first();
        $pendingTasksCount = max(0, $pendingReports->count() + ($primaryPhase ? 1 : 0));

        return view('supervisor.dashboard', compact(
            'user',
            'assignedProjects',
            'currentPhases',
            'delayedMilestones',
            'upcomingMilestones',
            'pendingReports',
            'approvedReports',
            'rejectedReports',
            'stats',
            'primaryProject',
            'primaryPhase',
            'projectProgress',
            'projectWorkersCount',
            'attendancePresentCount',
            'attendanceRecords',
            'upcomingMilestone',
            'pendingTasksCount'
        ));
    }

    public function timeline()
    {
        $user = Auth::user();

        $assignedProjects = Project::whereHas('supervisors', function ($query) use ($user) {
            $query->where('supervisor_id', $user->user_id);
        })
            ->with(['phases' => function ($query) {
                $query->orderBy('phase_order')->orderBy('planned_start_date');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        $projectsWithStats = $assignedProjects->map(function ($project) {
            $phases = $project->phases->sortBy('phase_order')->values();
            $completedPhases = $phases->where('status', 'completed')->count();
            $inProgressPhases = $phases->where('status', 'in_progress')->count();
            $upcomingPhases = $phases->whereIn('status', ['not_started', 'delayed'])->count();
            $progress = $phases->isEmpty() ? 0 : round((float) $phases->avg('completion_percentage'), 2);

            return [
                'id' => $project->project_id,
                'name' => $project->project_name,
                'targetEndDate' => optional($project->target_end_date)->toDateString(),
                'progress' => $progress,
                'completedPhases' => $completedPhases,
                'inProgressPhases' => $inProgressPhases,
                'upcomingPhases' => $upcomingPhases,
                'phases' => $phases->map(function ($phase) {
                    return [
                        'phase_name' => $phase->phase_name,
                        'phase_order' => $phase->phase_order,
                        'planned_start_date' => optional($phase->planned_start_date)->toDateString(),
                        'planned_end_date' => optional($phase->planned_end_date)->toDateString(),
                        'completion_percentage' => (float) ($phase->completion_percentage ?? 0),
                        'status' => $phase->status,
                        'display_status' => match ($phase->status) {
                            'completed' => 'completed',
                            'in_progress' => 'in-progress',
                            default => 'planning',
                        },
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return view('supervisor.timeline', compact('projectsWithStats'));
    }

    public function phases()
    {
        $user = Auth::user();

        $assignedProjects = Project::whereHas('supervisors', function ($query) use ($user) {
            $query->where('supervisor_id', $user->user_id);
        })
            ->with(['phases' => function ($query) {
                $query->orderBy('phase_order')->orderBy('planned_start_date');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        $primaryProject = $assignedProjects->first();
        $primaryPhase = optional($primaryProject)->phases->firstWhere('status', 'in_progress') ?? optional($primaryProject)->phases->first();

        return view('supervisor.phases', compact('assignedProjects', 'primaryProject', 'primaryPhase'));
    }

    public function attendance()
    {
        $user = Auth::user();
        $assignedProjects = Project::whereHas('supervisors', function ($query) use ($user) {
            $query->where('supervisor_id', $user->user_id);
        })->with('workers')->get();

        $workers = $assignedProjects->flatMap(fn($project) => $project->workers)->unique('worker_id')->values();
        $activeProject = $assignedProjects->first();

        return view('supervisor.attendance', compact('workers', 'activeProject'));
    }

    public function profile()
    {
        $user = Auth::user();
        $assignedProjects = Project::whereHas('supervisors', function ($query) use ($user) {
            $query->where('supervisor_id', $user->user_id);
        })->orderBy('created_at', 'desc')->get();

        return view('supervisor.profile', compact('user', 'assignedProjects'));
    }

    public function materials()
    {
        $metrics = [
            'active_deliveries' => 0,
            'low_stock_alerts' => 0,
            'total_value' => 0,
        ];
        $inventory = collect();
        $materials_list = collect();

        return view('supervisor.material', compact('metrics', 'inventory', 'materials_list'));
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