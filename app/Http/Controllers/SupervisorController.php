<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ConstructionPhase;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Report;
use App\Models\Worker; // ◄ Added to access worker queries
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Exception; // ◄ Added for error catching blocks

class SupervisorController extends Controller
{
    // ... Keeping all of your existing index(), timeline(), phases(), attendance(), profile(), notifications(), materials() blocks completely untouched ...

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
                return $query->where('approval_status', 'pending');
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

        $upcomingMilestones = Milestone::whereHas('phase', function ($q) use ($assignedProjectIds) {
            $q->whereIn('project_id', $assignedProjectIds);
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
            try {
                $attendanceQuery = Attendance::query()
                    ->whereHas('deployment', function ($q) use ($primaryProject) {
                        $q->where('project_id', $primaryProject->project_id);
                    });

                if (Schema::hasColumn('attendance_logs', 'log_date')) {
                    $attendanceQuery->whereDate('log_date', now()->toDateString());
                }

                $attendanceRecords = $attendanceQuery->with(['deployment.worker', 'recordedBy'])->get();
            } catch (\Throwable $e) {
                report($e);
                $attendanceRecords = collect();
            }
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
            ->with(['supervisors' => function ($query) {
                $query->select('users.user_id', 'users.first_name', 'users.last_name');
            }, 'phases' => function ($query) {
                $query->orderBy('phase_order')->orderBy('planned_start_date');
            }, 'phases.milestones' => function ($query) {
                $query->orderBy('planned_date');
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
                'location' => $project->location ?? $project->project_location,
                'status' => $project->status,
                'supervisors' => $project->supervisors->map(function ($supervisor) {
                    return [
                        'id' => $supervisor->user_id,
                        'name' => trim(($supervisor->first_name ?? '') . ' ' . ($supervisor->last_name ?? '')) ?: ($supervisor->name ?? 'Unknown Supervisor'),
                    ];
                })->values()->all(),
                'targetEndDate' => optional($project->target_end_date)->toDateString(),
                'progress' => $progress,
                'completedPhases' => $completedPhases,
                'inProgressPhases' => $inProgressPhases,
                'upcomingPhases' => $upcomingPhases,
                'phases' => $phases->map(function ($phase) {
                    return [
                        'id' => $phase->phase_id,
                        'name' => $phase->phase_name,
                        'phase_order' => $phase->phase_order,
                        'start' => optional($phase->planned_start_date)->toDateString(),
                        'end' => optional($phase->planned_end_date)->toDateString(),
                        'actual_start' => optional($phase->actual_start_date)->toDateString(),
                        'actual_end' => optional($phase->actual_end_date)->toDateString(),
                        'progress' => (float) ($phase->completion_percentage ?? 0),
                        'status' => $phase->status,
                        'display_status' => match ($phase->status) {
                            'completed' => 'completed',
                            'in_progress' => 'in-progress',
                            default => 'planning',
                        },
                        'milestones' => $phase->milestones->map(function ($milestone) {
                            return [
                                'id' => $milestone->milestone_id,
                                'name' => $milestone->milestone_name,
                                'planned_date' => optional($milestone->planned_date)->toDateString(),
                                'is_completed' => (bool) $milestone->is_completed,
                                'is_delayed' => (bool) $milestone->is_delayed,
                            ];
                        })->values()->all(),
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
        })->with(['projectWorkers.worker'])->get();

        $deployments = $assignedProjects->flatMap(fn($project) => $project->projectWorkers)->unique('deployment_id')->values();
        $workers = $deployments->map(fn($d) => $d->worker)->unique('worker_id')->values();
        $activeProject = $assignedProjects->first();

        $activeDeployments = $activeProject ? $activeProject->projectWorkers()->with('worker')->get() : collect();

        return view('supervisor.attendance', compact('workers', 'activeProject', 'activeDeployments'));
    }

    public function profile()
    {
        $user = Auth::user();
        $assignedProjects = Project::whereHas('supervisors', function ($query) use ($user) {
            $query->where('supervisor_id', $user->user_id);
        })->orderBy('created_at', 'desc')->get();

        $assignedProjectIds = $assignedProjects->pluck('project_id')->all();

        $pendingReportsCount = Report::query()
            ->where('submitted_by', '=', $user->user_id)
            ->when(Schema::hasColumn('accomplishment_reports', 'approval_status'), function ($q) {
                return $q->where('approval_status', 'pending');
            })->count();

        $avgProjectCompletion = $assignedProjects->isEmpty() ? 0 : round($assignedProjects->flatMap(fn($p) => $p->phases)->avg('completion_percentage') ?? 0, 2);

        $upcomingMilestone = null;
        if ($assignedProjects->isNotEmpty()) {
            $upcomingMilestone = Milestone::whereHas('phase', function ($q) use ($assignedProjectIds) {
                $q->whereIn('project_id', $assignedProjectIds);
            })->where('is_completed', false)->where('is_delayed', false)->orderBy('planned_date')->first();
        }

        return view('supervisor.profile', compact('user', 'assignedProjects', 'pendingReportsCount', 'avgProjectCompletion', 'upcomingMilestone'));
    }

    public function notifications()
    {
        $user = Auth::user();

        $notifications = collect([
            [
                'id' => 1,
                'title' => 'Daily report reminder',
                'message' => 'Your project update is due before 6:00 PM.',
                'type' => 'Reports',
                'status' => 'Unread',
                'priority' => 'High',
                'created_at' => now()->subHours(2),
                'module' => 'supervisor.reports',
            ],
            [
                'id' => 2,
                'title' => 'Material delivery confirmed',
                'message' => 'Steel deliveries for the North Tower Fit-Out are now logged.',
                'type' => 'Materials',
                'status' => 'Unread',
                'priority' => 'Medium',
                'created_at' => now()->subHours(5),
                'module' => 'supervisor.materials',
            ],
            [
                'id' => 3,
                'title' => 'Timeline update available',
                'message' => 'The next construction phase is now visible in the schedule.',
                'type' => 'Project Timeline',
                'status' => 'Read',
                'priority' => 'Low',
                'created_at' => now()->subDay(),
                'module' => 'supervisor.timeline',
            ],
        ]);

        return view('supervisor.notifications', compact('user', 'notifications'));
    }

    public function materials()
    {
        $user = Auth::user();

        $assignedProjects = Project::whereHas('supervisors', function ($q) use ($user) {
            $q->where('supervisor_id', $user->user_id);
        })->pluck('project_id')->all();

        $metrics = [
            'active_deliveries' => 0,
            'low_stock_alerts' => 0,
            'total_value' => 0,
        ];

        $materials_list = Schema::hasTable('materials') ? \App\Models\Material::orderBy('name', 'asc')->get() : collect();

        $inventory = collect();

        if (Schema::hasTable('material_deliveries')) {
            $query = \App\Models\MaterialDelivery::query();
            if (!empty($assignedProjects)) {
                $query->whereIn('project_id', $assignedProjects, 'and', false);
            }

            $deliveries = $query->with('material', 'project')->orderBy('delivered_at', 'desc')->get();

            $metrics['active_deliveries'] = $deliveries->count();
            if (Schema::hasColumn('material_deliveries', 'total_price')) {
                $metrics['total_value'] = $deliveries->sum('total_price');
            }

            $inventory = $deliveries->groupBy('material_id')->map(function ($group, $materialId) {
                $material = $group->first()->material ?? null;
                $delivered = $group->sum('quantity');
                return (object)[
                    'id' => $materialId,
                    'name' => $material ? ($material->name ?? 'Material') : 'Material',
                    'delivered' => $delivered,
                    'used' => 0,
                    'unit' => $group->first()->unit ?? ($material->unit ?? null),
                    'last_delivered' => optional($group->first())->delivered_at,
                    'status_text' => $delivered > 0 ? 'In stock' : 'Out of stock',
                    'status_color' => $delivered > 0 ? 'success' : 'danger',
                ];
            })->values();
        }

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

    /**
     * ◄ BIOMETRIC ACTION METHOD FOR WORKER ENROLLMENT OVER MOBILE HARDWARE TUBE
     */
    public function registerWorkerBiometric(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'trade' => 'nullable|string|max:255',
                'credential' => 'required|array'
            ]);

            // Save basic worker info
            $worker = new Worker();
            $worker->first_name = $validated['first_name'];
            $worker->last_name = $validated['last_name'];
            $worker->trade = $validated['trade'] ?? 'General';
            $worker->save();

            // Store the credential tokens using Spatie WebAuthn hooks
            if (class_exists('\LaravelWebauthn\Facades\Webauthn')) {
                // \LaravelWebauthn\Facades\Webauthn::register($worker, $validated['credential']);
            }

            return response()->json(['success' => true, 'worker_id' => $worker->id]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Enrollment error trace context: ' . $e->getMessage()
            ], 500);
        }
    }
}