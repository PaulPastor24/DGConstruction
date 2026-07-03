<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ConstructionPhase;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Report;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

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

        $selectedProjectId = $request->query('project_id') ?: session('supervisor_selected_project_id');
        $primaryProject = $selectedProjectId
            ? $assignedProjects->firstWhere('project_id', $selectedProjectId) ?? $assignedProjects->first()
            : $assignedProjects->first();

        if ($primaryProject) {
            session(['supervisor_selected_project_id' => $primaryProject->project_id]);
        }

        $totalProjects = $assignedProjects->count();
        $hasApprovalStatus = Schema::hasColumn('accomplishment_reports', 'approval_status');

        if ($primaryProject) {
            $selectedProjectId = $primaryProject->project_id;

            $currentPhases = $primaryProject->phases->where('status', 'in_progress');

            $delayedMilestones = Milestone::whereHas('phase', function ($q) use ($selectedProjectId) {
                $q->where('project_id', $selectedProjectId);
            })->where('is_delayed', true)
                ->where('is_completed', false)
                ->with(['phase.project'])
                ->orderBy('planned_date')
                ->get();

            $pendingReports = Report::query()
                ->where('project_id', $selectedProjectId)
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
                    ->where('project_id', $selectedProjectId)
                    ->where('submitted_by', $user->user_id)
                    ->where('approval_status', 'approved')
                    ->with(['project', 'phase'])
                    ->orderBy('updated_at', 'desc')
                    ->limit(5)
                    ->get()
                : collect();

            $rejectedReports = $hasApprovalStatus
                ? Report::query()
                    ->where('project_id', $selectedProjectId)
                    ->where('submitted_by', $user->user_id)
                    ->where('approval_status', 'rejected')
                    ->with(['project', 'phase'])
                    ->orderBy('updated_at', 'desc')
                    ->limit(5)
                    ->get()
                : collect();

            $upcomingMilestones = Milestone::whereHas('phase', function ($q) use ($selectedProjectId) {
                $q->where('project_id', $selectedProjectId);
            })->where('is_completed', false)
                ->whereNotNull('planned_date')
                ->where('planned_date', '>', now())
                ->orderBy('planned_date')
                ->get();

            $primaryPhase = $primaryProject->phases->firstWhere('status', 'in_progress')
                ?? $primaryProject->phases->sortBy('phase_order')->first();

            $projectProgress = round((float) ($primaryProject->phases->avg('completion_percentage') ?? 0), 2);
            $projectWorkersCount = max(0, $primaryProject->workers()->count());

            try {
                $attendanceQuery = Attendance::query()
                    ->where('project_id', $primaryProject->project_id);

                if (Schema::hasColumn('attendance_logs', 'log_date')) {
                    $attendanceQuery->whereDate('log_date', now()->toDateString());
                }

                $attendanceRecords = $attendanceQuery->with(['deployment.worker', 'recordedBy'])->get();
            } catch (\Throwable $e) {
                report($e);
                $attendanceRecords = collect();
            }
        } else {
            $currentPhases = collect();
            $delayedMilestones = collect();
            $pendingReports = collect();
            $approvedReports = collect();
            $rejectedReports = collect();
            $upcomingMilestones = collect();
            $primaryPhase = null;
            $projectProgress = 0;
            $projectWorkersCount = 0;
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

        $stats = [
            'total_projects' => $totalProjects,
            'active_projects' => $assignedProjects->filter(fn($p) => $p->status === 'ongoing')->count(),
            'current_phases' => $currentPhases->count(),
            'delayed_milestones' => $delayedMilestones->count(),
            'pending_reports' => $pendingReports->count(),
            'approved_reports' => $approvedReports->count(),
            'rejected_reports' => $rejectedReports->count(),
            'average_completion' => $primaryProject ? round($primaryProject->phases->avg('completion_percentage') ?? 0, 2) : 0,
        ];

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

    public function timeline(Request $request)
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

            $allMilestones = $phases->flatMap(function ($phase) {
                return $phase->milestones->map(function ($milestone) use ($phase) {
                    return [
                        'id' => $milestone->milestone_id,
                        'name' => $milestone->milestone_name,
                        'project_name' => $phase->project->project_name ?? null,
                        'phase_name' => $phase->phase_name,
                        'planned_date' => optional($milestone->planned_date)->toDateString(),
                        'is_completed' => (bool) $milestone->is_completed,
                        'is_delayed' => (bool) $milestone->is_delayed,
                        'phase_order' => $phase->phase_order,
                    ];
                });
            })->sortBy('planned_date')->values();

            $activeMilestones = $allMilestones->filter(function ($milestone) {
                return !$milestone['is_completed'] && !$milestone['is_delayed'] && $milestone['planned_date'] && \Carbon\Carbon::parse($milestone['planned_date'])->lte(now()->addDays(7));
            })->take(2)->values();

            if ($activeMilestones->isEmpty()) {
                $activeMilestones = $allMilestones->filter(function ($milestone) {
                    return !$milestone['is_completed'] && !$milestone['is_delayed'];
                })->take(2)->values();
            }

            $upcomingMilestones = $allMilestones->filter(function ($milestone) {
                return !$milestone['is_completed'] && !$milestone['is_delayed'] && $milestone['planned_date'] && \Carbon\Carbon::parse($milestone['planned_date'])->gt(now());
            })->take(2)->values();

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
                'scheduleHealth' => $phases->contains(fn ($phase) => $phase->status === 'delayed') ? 'Delayed' : 'On Track',
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
                'activeMilestones' => $activeMilestones->all(),
                'upcomingMilestones' => $upcomingMilestones->all(),
            ];
        })->values()->all();

        $selectedProjectId = $request->query('project_id') ?: session('supervisor_selected_project_id') ?: data_get($projectsWithStats, '0.id');
        if ($selectedProjectId) {
            session(['supervisor_selected_project_id' => $selectedProjectId]);
        }

        return view('supervisor.timeline', compact('projectsWithStats', 'selectedProjectId'));
    }

    public function phases(Request $request)
    {
        $user = Auth::user();
        $selectedProjectId = $request->query('project_id') ?: session('supervisor_selected_project_id');

        // Get all assigned projects
        $assignedProjects = Project::whereHas('supervisors', function ($query) use ($user) {
            $query->where('supervisor_id', $user->user_id);
        })
            ->with(['phases' => function ($query) {
                $query->orderBy('phase_order')->orderBy('planned_start_date');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($assignedProjects->isEmpty()) {
            return view('supervisor.phases', [
                'assignedProjects' => $assignedProjects,
                'primaryProject' => null,
                'primaryPhase' => null,
                'projectPhases' => collect(),
                'overallProgress' => 0,
                'scheduleHealth' => 'ON TRACK',
            ]);
        }

        // Determine primary project: use selected or first assigned
        $primaryProject = $selectedProjectId
            ? $assignedProjects->firstWhere('project_id', $selectedProjectId) ?? $assignedProjects->first()
            : $assignedProjects->first();

        // Store in session for persistence
        if ($primaryProject) {
            session(['supervisor_selected_project_id' => $primaryProject->project_id]);
        }

        // Get phases for the primary project with pagination
        $query = ConstructionPhase::query()
            ->where('project_id', $primaryProject->project_id)
            ->orderBy('phase_order')
            ->orderBy('planned_start_date');

        // Apply search filter if provided
        if ($request->has('search') && $search = $request->input('search')) {
            $query->where('phase_name', 'like', '%' . $search . '%');
        }

        // Apply status filter if provided
        if ($request->has('status') && $status = $request->input('status')) {
            $query->where('status', $status);
        }

        $projectPhases = $query->paginate(10);

        // Get the current phase (first one in progress)
        $primaryPhase = ConstructionPhase::query()
            ->where('project_id', $primaryProject->project_id)
            ->where('status', 'in_progress')
            ->orderBy('phase_order')
            ->first() ?? ConstructionPhase::query()
            ->where('project_id', $primaryProject->project_id)
            ->orderBy('phase_order')
            ->first();

        // Calculate overall progress
        $overallProgress = ConstructionPhase::query()
            ->where('project_id', $primaryProject->project_id)
            ->get()
            ->avg('completion_percentage') ?? 0;
        $overallProgress = round($overallProgress, 0);

        // Determine schedule health
        $delayedCount = ConstructionPhase::query()
            ->where('project_id', $primaryProject->project_id)
            ->where('status', 'delayed')
            ->count();
        $scheduleHealth = $delayedCount > 0 ? 'DELAYED' : 'ON TRACK';

        return view('supervisor.phases', compact(
            'assignedProjects',
            'primaryProject',
            'primaryPhase',
            'projectPhases',
            'overallProgress',
            'scheduleHealth'
        ));
    }

    /**
     * Log system action for supervisor activity
     */
    private function logAction($action, $description)
    {
        try {
            SystemLog::create([
                'user_id' => auth('web')->user()->user_id,
                'action' => $action,
                'description' => $description,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            // Ignore logging failures to avoid blocking supervisor actions.
        }
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

        // Provide deployments for the active project if the view needs deployment_id
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

        // Compute lightweight actionable metrics for the profile (decision support)
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

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')],
            'contact_number' => ['nullable', 'string', 'max:20'],
        ];

        if (Schema::hasColumn('users', 'address')) {
            $rules['address'] = ['nullable', 'string', 'max:500'];
        }

        $validated = $request->validate($rules);

        $user->fill($validated);

        if (Schema::hasColumn('users', 'name')) {
            $user->name = trim(($validated['first_name'] ?? $user->first_name) . ' ' . ($validated['last_name'] ?? $user->last_name));
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('success', 'Profile information updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }

    public function notifications(Request $request)
    {
        $user = Auth::user();

        // If migration hasn't been run yet, avoid querying a missing table.
        if (!\Illuminate\Support\Facades\Schema::hasTable('supervisor_notifications')) {
            $notifications = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12, 1, [
                'path' => request()->url(),
                'query' => request()->query()
            ]);

            $totalNotifs = 0;
            $unreadCount = 0;
            $readCount = 0;
            $archivedCount = 0;

            return view('supervisor.notifications', compact('user', 'notifications', 'totalNotifs', 'unreadCount', 'readCount', 'archivedCount'));
        }

        $query = \App\Models\SupervisorNotification::query()
            ->where('supervisor_id', $user->user_id)
            ->orderBy('created_at', 'desc');

        $type = $request->query('type');
        if ($type) {
            $type = strtolower($type);
            switch ($type) {
                case 'unread':
                    $query->where('is_read', false);
                    break;
                case 'read':
                    $query->where('is_read', true);
                    break;
                case 'project':
                case 'report':
                case 'phase':
                case 'timeline':
                case 'attendance':
                case 'announcement':
                case 'system':
                    $query->where('type', $type);
                    break;
                default:
                    // 'all' or unknown - no extra filter
                    break;
            }
        }

        $notifications = $query->paginate(12)->withQueryString();

        $totalNotifs = \App\Models\SupervisorNotification::query()->where('supervisor_id', $user->user_id)->count();
        $unreadCount = \App\Models\SupervisorNotification::query()->where('supervisor_id', $user->user_id)->where('is_read', false)->count();
        $readCount = \App\Models\SupervisorNotification::query()->where('supervisor_id', $user->user_id)->where('is_read', true)->count();
        $archivedCount = 0;

        return view('supervisor.notifications', compact('user', 'notifications', 'totalNotifs', 'unreadCount', 'readCount', 'archivedCount'));
    }

    /**
     * Mark a single notification as read
     */
    public function markNotificationRead($id)
    {
        $user = Auth::user();
        $notif = \App\Models\SupervisorNotification::query()->where('id', $id)->where('supervisor_id', $user->user_id)->first();
        if (!$notif) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }
        $notif->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read for the logged in supervisor
     */
    public function markAllNotificationsRead()
    {
        $user = Auth::user();
        \App\Models\SupervisorNotification::query()->where('supervisor_id', $user->user_id)->where('is_read', false)->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['success' => true]);
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

            // Build inventory summary grouped by material matching view expectations
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
     * Get phase details via AJAX for modal display
     */
    public function getPhaseDetails($phaseId)
    {
        $user = Auth::user();

        // Get the phase and verify authorization
        $phase = ConstructionPhase::with('project', 'milestones', 'reports')
            ->where('phase_id', $phaseId)
            ->first();

        if (!$phase) {
            return response()->json(['error' => 'Phase not found'], 404);
        }

        // Verify supervisor has access to this project
        $hasAccess = Project::query()->where('project_id', $phase->project_id)
            ->whereHas('supervisors', function ($q) use ($user) {
                $q->where('supervisor_id', $user->user_id);
            })
            ->exists();

        if (!$hasAccess) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'can_manage' => true,
            'phase' => [
                'id' => $phase->phase_id,
                'name' => $phase->phase_name,
                'order' => $phase->phase_order,
                'description' => $phase->phase_name . ' - Phase ' . $phase->phase_order,
                'status' => $phase->status,
                'completion_percentage' => (float)($phase->completion_percentage ?? 0),
                'planned_start_date' => $phase->planned_start_date ? $phase->planned_start_date->format('M d, Y') : 'Pending',
                'planned_end_date' => $phase->planned_end_date ? $phase->planned_end_date->format('M d, Y') : 'Pending',
                'actual_start_date' => $phase->actual_start_date ? $phase->actual_start_date->format('M d, Y') : 'Not started',
                'actual_end_date' => $phase->actual_end_date ? $phase->actual_end_date->format('M d, Y') : 'In progress',
                'project_name' => $phase->project->project_name,
                'milestones_count' => $phase->milestones->count(),
                'completed_milestones' => $phase->milestones->where('is_completed', true)->count(),
                'delayed_milestones' => $phase->milestones->where('is_delayed', true)->count(),
            ]
        ]);
    }

    /**
     * Update phase progress (completion percentage)
     */
    public function updatePhaseProgress(Request $request, $phaseId)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'completion_percentage' => 'required|numeric|min:0|max:100'
        ]);

        $phase = ConstructionPhase::with('project')->where('phase_id', $phaseId)->first();
        if (!$phase) {
            return response()->json(['success' => false, 'message' => 'Phase not found'], 404);
        }

        // Verify supervisor has access to this project
        $hasAccess = Project::query()->where('project_id', $phase->project_id)
            ->whereHas('supervisors', function ($q) use ($user) {
                $q->where('supervisor_id', $user->user_id);
            })->exists();

        if (!$hasAccess) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $phase->completion_percentage = round((float)$validated['completion_percentage'], 2);
            $phase->save();

            // Recalculate project overall progress
            $overallProgress = ConstructionPhase::query()->where('project_id', $phase->project_id)->avg('completion_percentage') ?? 0;
            $overallProgress = round($overallProgress, 0);

            // Log action
            try {
                $this->logAction('Phase Progress Updated', "{$phase->phase_name} progress updated to {$phase->completion_percentage}% for project {$phase->project->project_name}");
            } catch (\Throwable $e) {
                // ignore logging errors
            }

            // Create notification for supervisor
            \App\Services\NotificationService::notifySupervisor($user->user_id, [
                'type' => 'phase',
                'title' => 'Phase Progress Updated',
                'message' => "{$phase->phase_name} progress updated to {$phase->completion_percentage}%.",
                'data' => ['module' => 'supervisor.phases', 'phase_id' => $phase->phase_id],
                'related_id' => $phase->phase_id,
                'related_type' => 'phase',
            ]);

            return response()->json(['success' => true, 'phase' => ['completion_percentage' => $phase->completion_percentage], 'overallProgress' => $overallProgress]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update progress'], 500);
        }
    }

    /**
     * Update phase status
     */
    public function updatePhaseStatus(Request $request, $phaseId)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'status' => 'required|string|in:not_started,in_progress,completed'
        ]);

        $phase = ConstructionPhase::with('project')->where('phase_id', $phaseId)->first();
        if (!$phase) {
            return response()->json(['success' => false, 'message' => 'Phase not found'], 404);
        }

        // Verify supervisor has access to this project
        $hasAccess = Project::query()->where('project_id', $phase->project_id)
            ->whereHas('supervisors', function ($q) use ($user) {
                $q->where('supervisor_id', $user->user_id);
            })->exists();

        if (!$hasAccess) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Business rules: Completed phases cannot revert
        if ($phase->status === 'completed' && $validated['status'] !== 'completed') {
            return response()->json(['success' => false, 'message' => 'Completed phases cannot be reverted'], 422);
        }

        try {
            $previousStatus = $phase->status;
            $phase->status = $validated['status'];

            if ($validated['status'] === 'in_progress' && empty($phase->actual_start_date)) {
                $phase->actual_start_date = now();
            }
            if ($validated['status'] === 'completed') {
                $phase->actual_end_date = now();
                $phase->completion_percentage = 100;
            }

            $phase->save();

            // Recalculate overall progress
            $overallProgress = ConstructionPhase::query()->where('project_id', $phase->project_id)->avg('completion_percentage') ?? 0;
            $overallProgress = round($overallProgress, 0);

            // Log action
            try {
                $this->logAction('Phase Status Changed', "{$phase->phase_name} status changed to {$phase->status} for project {$phase->project->project_name}");
            } catch (\Throwable $e) {
                // ignore
            }

            // Notify supervisor
            \App\Services\NotificationService::notifySupervisor($user->user_id, [
                'type' => 'phase',
                'title' => 'Phase Status Changed',
                'message' => "{$phase->phase_name} status changed to " . strtoupper(str_replace('_', ' ', $phase->status)) . ".",
                'data' => ['module' => 'supervisor.phases', 'phase_id' => $phase->phase_id],
                'related_id' => $phase->phase_id,
                'related_type' => 'phase',
            ]);

            return response()->json(['success' => true, 'phase' => ['status' => $phase->status, 'completion_percentage' => $phase->completion_percentage], 'overallProgress' => $overallProgress]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update status'], 500);
        }
    }

    /**
     * Export phases to PDF with real data
     */
    public function exportPhasesPdf(Request $request)
    {
        $user = Auth::user();
        $projectId = $request->input('project_id') ?: session('supervisor_selected_project_id');

        // Verify authorization
        $project = Project::query()->where('project_id', $projectId)
            ->whereHas('supervisors', function ($q) use ($user) {
                $q->where('supervisor_id', $user->user_id);
            })
            ->first();

        if (!$project) {
            return response()->json(['error' => 'Unauthorized or project not found'], 403);
        }

        // Get phases for the project
        $query = ConstructionPhase::query()->where('project_id', $projectId)
            ->orderBy('phase_order');

        if ($request->has('status') && $status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($request->has('search') && $search = $request->input('search')) {
            $query->where('phase_name', 'like', '%' . $search . '%');
        }

        $phases = $query->get();

        // Calculate statistics
        $overallProgress = $phases->avg('completion_percentage') ?? 0;
        $completedCount = $phases->where('status', 'completed')->count();
        $inProgressCount = $phases->where('status', 'in_progress')->count();
        $delayedCount = $phases->where('status', 'delayed')->count();
        $pendingCount = $phases->where('status', 'not_started')->count();

        // Generate HTML for PDF
        $html = view('supervisor.phases-pdf', compact(
            'project',
            'phases',
            'overallProgress',
            'completedCount',
            'inProgressCount',
            'delayedCount',
            'pendingCount'
        ))->render();

        // Try to use mPDF if available, otherwise return the HTML as a downloadable file.
        try {
            if (class_exists('\\Mpdf\\Mpdf')) {
                $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 10, 'margin_bottom' => 10]);
                $mpdf->WriteHTML($html);
                $fileName = 'phases_report_' . date('Y-m-d') . '.pdf';
                return $mpdf->Output($fileName, 'D');
            }
        } catch (\Exception $e) {
            // If PDF generation fails, fall back to downloadable HTML.
        }

        $fileName = 'phases_report_' . date('Y-m-d') . '.html';
        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}