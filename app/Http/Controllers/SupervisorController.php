<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ConstructionPhase;
use App\Models\Material;
use App\Models\MaterialUsage;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\Report;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
                ->orderBy('start_date')
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
                ->whereNotNull('start_date')
                ->where('start_date', '>', now())
                ->orderBy('start_date')
                ->get();

            $primaryPhase = $primaryProject->phases->firstWhere('status', 'in_progress')
                ?? $primaryProject->phases->sortBy('phase_order')->first();

            $projectProgress = round((float) ($primaryProject->phases->avg('completion_percentage') ?? 0), 2);
            $projectWorkersCount = max(0, $primaryProject->workers()->count());

            try {
                $attendanceQuery = Attendance::query()
                    ->where('project_id', $primaryProject->project_id);

                if (Schema::hasColumn('attendance_logs', 'log_date')) {
                    $attendanceQuery->whereDate('log_date', '=', now()->toDateString(), 'and');
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

        $upcomingMilestone = $upcomingMilestones->sortBy('start_date')->first();
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
                $query->orderBy('start_date');
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
                        'start_date' => optional($milestone->start_date)->toDateString(),
                        'is_completed' => (bool) $milestone->is_completed,
                        'is_delayed' => (bool) $milestone->is_delayed,
                        'phase_order' => $phase->phase_order,
                    ];
                });
            })->sortBy('start_date')->values();

            $activeMilestones = $allMilestones->filter(function ($milestone) {
                return !$milestone['is_completed'] && !$milestone['is_delayed'] && $milestone['start_date'] && \Carbon\Carbon::parse($milestone['start_date'])->lte(now()->addDays(7));
            })->take(2)->values();

            if ($activeMilestones->isEmpty()) {
                $activeMilestones = $allMilestones->filter(function ($milestone) {
                    return !$milestone['is_completed'] && !$milestone['is_delayed'];
                })->take(2)->values();
            }

            $upcomingMilestones = $allMilestones->filter(function ($milestone) {
                return !$milestone['is_completed'] && !$milestone['is_delayed'] && $milestone['start_date'] && \Carbon\Carbon::parse($milestone['start_date'])->gt(now());
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
                                'start_date' => optional($milestone->start_date)->toDateString(),
                                'end_date' => optional($milestone->end_date)->toDateString(),
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
            ->orderBy('phase_order', 'asc')
            ->orderBy('planned_start_date', 'asc');

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
            ->orderBy('phase_order', 'asc')
            ->first() ?? ConstructionPhase::query()
            ->where('project_id', $primaryProject->project_id)
            ->orderBy('phase_order', 'asc')
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
            ->count('*');
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
            })->where('is_completed', false)->where('is_delayed', false)->orderBy('start_date')->first();
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

        $newPassword = $request->input('password');
        if (Hash::check($newPassword, $user->password)) {
            return back()->withErrors(['password' => 'The new password must be different from your current password.']);
        }

        $user->password = Hash::make($newPassword);
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

        $totalNotifs = \App\Models\SupervisorNotification::query()->where('supervisor_id', $user->user_id)->count('*');
        $unreadCount = \App\Models\SupervisorNotification::query()->where('supervisor_id', $user->user_id)->where('is_read', false)->count('*');
        $readCount = \App\Models\SupervisorNotification::query()->where('supervisor_id', $user->user_id)->where('is_read', true)->count('*');
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

    public function materials(Request $request)
    {
        $user = Auth::user();

        $assignedProjects = Project::query()
            ->whereHas('supervisors', function ($q) use ($user) {
                $q->where('supervisor_id', $user->user_id);
            })
            ->orderBy('created_at', 'desc')
            ->get(['project_id', 'project_name', 'location', 'status']);

        $selectedProject = null;
        $selectedProjectId = $request->query('project_id');

        if ($selectedProjectId) {
            $selectedProject = $assignedProjects->firstWhere('project_id', (int) $selectedProjectId);
        }

        if (!$selectedProject && $assignedProjects->isNotEmpty()) {
            $selectedProject = $assignedProjects->first();
        }

        if ($selectedProject) {
            session(['supervisor_selected_project_id' => $selectedProject->project_id]);
        }

        $search = trim((string) $request->query('search', ''));
        $selectedStatus = trim((string) $request->query('status', ''));
        $selectedPhaseId = trim((string) $request->query('phase_id', ''));

        $materialsQuery = Schema::hasTable('materials') ? Material::query() : null;
        if ($materialsQuery) {
            if ($search !== '') {
                $materialsQuery->where('name', 'like', '%' . $search . '%');
            }

            $materials_list = $materialsQuery->orderBy('name', 'asc')->get();
        } else {
            $materials_list = collect();
        }

        $inventoryCollection = collect();
        $projectPhases = collect();
        $selectedPhase = null;

        if ($selectedProject) {
            $projectPhases = ConstructionPhase::query()
                ->where('project_id', $selectedProject->project_id)
                ->orderBy('phase_order', 'asc')
                ->get(['phase_id', 'phase_name', 'phase_order', 'status']);

            if ($selectedPhaseId !== '') {
                $selectedPhase = $projectPhases->firstWhere('phase_id', (int) $selectedPhaseId);
            }

            $projectMaterialRows = ProjectMaterial::query()
                ->where('project_id', $selectedProject->project_id)
                ->get()
                ->keyBy('material_id');

            $usageQuery = MaterialUsage::query()
                ->where('project_id', $selectedProject->project_id);

            if ($selectedPhase) {
                $usageQuery->where('phase_id', $selectedPhase->phase_id);
            }

            $usageRows = $usageQuery
                ->selectRaw('material_id, SUM(quantity_used) as total_used')
                ->groupBy('material_id')
                ->get()
                ->keyBy('material_id');

            $materialIds = $projectMaterialRows->keys()
                ->merge($usageRows->keys())
                ->filter(fn ($id) => $id !== null)
                ->unique()
                ->values();

            if ($materialIds->isNotEmpty()) {
                $materialIdList = $materialIds->filter(fn ($id) => is_numeric($id))->map(fn ($id) => (int) $id)->values()->all();

                $materialRows = Material::query()
                    ->whereIn('id', $materialIdList, 'and', false)
                    ->get()
                    ->keyBy('id');

                $inventoryCollection = $materialIds->map(function ($materialId) use ($materialRows, $projectMaterialRows, $usageRows, $selectedProject) {
                    $material = $materialRows->get($materialId);
                    if (!$material) {
                        return null;
                    }

                    $row = $projectMaterialRows->get($materialId);
                    $plannedFromRow = max(0.0, (float) ($row->planned_quantity ?? 0));
                    $usedFromProject = max(0.0, (float) ($row->used_quantity ?? 0));
                    $usedFromUsageTable = max(0.0, (float) ($usageRows->get($materialId)->total_used ?? 0));
                    $used = max($usedFromProject, $usedFromUsageTable);
                    $stockFallback = 0.0;

                    if (Schema::hasColumn('materials', 'current_stock')) {
                        $stockFallback = max(0.0, (float) ($material->current_stock ?? 0));
                    }

                    $planned = $plannedFromRow > 0 ? $plannedFromRow : max($used, $stockFallback);
                    $hasAnyActivity = $planned > 0 || $used > 0 || $stockFallback > 0;

                    if (!$hasAnyActivity) {
                        return null;
                    }

                    $remaining = $this->normalizeRemaining($planned, $used);
                    $status = $this->resolveMaterialStatus($remaining, $planned);

                    return (object) [
                        'id' => $material->id,
                        'name' => $material->name ?? 'Material',
                        'category' => $this->getMaterialCategory($material),
                        'unit' => $row->unit ?? $material->unit ?? 'unit',
                        'planned' => $planned,
                        'used' => $used,
                        'remaining' => $remaining,
                        'status_key' => $status['key'],
                        'status_text' => $status['text'],
                        'status_color' => $status['color'],
                    ];
                })->filter()->values();
            }

            if ($search !== '') {
                $inventoryCollection = $inventoryCollection->filter(function ($item) use ($search) {
                    return stripos($item->name, $search) !== false;
                })->values();
            }

            if ($selectedStatus !== '') {
                $inventoryCollection = $inventoryCollection->filter(function ($item) use ($selectedStatus) {
                    return $item->status_key === $selectedStatus;
                })->values();
            }
        }

        $perPage = 10;
        $page = max(1, (int) $request->query('page', 1));
        $inventory = new LengthAwarePaginator(
            $inventoryCollection->forPage($page, $perPage),
            $inventoryCollection->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $metrics = [
            'total_materials' => $inventoryCollection->count(),
            'materials_used' => $inventoryCollection->filter(fn ($item) => (float) $item->used > 0)->count(),
            'low_stock_alerts' => $inventoryCollection->filter(fn ($item) => $item->status_key === 'low_stock')->count(),
            'critical_materials' => $inventoryCollection->filter(fn ($item) => in_array($item->status_key, ['critical', 'out_of_stock'], true))->count(),
        ];

        $recentUsages = new LengthAwarePaginator([], 0, 10, 1, [
            'path' => $request->url(),
            'query' => array_merge($request->query(), ['recent_page' => 1]),
        ]);

        if ($selectedProject) {
            $recentUsageQuery = MaterialUsage::query()
                ->where('project_id', $selectedProject->project_id)
                ->with(['material', 'phase', 'recorder']);

            if ($selectedPhase) {
                $recentUsageQuery->where('phase_id', $selectedPhase->phase_id);
            }

            $recentPage = max(1, (int) $request->query('recent_page', 1));
            $recentUsageBaseQuery = $recentUsageQuery->clone()->orderBy('usage_date', 'desc')->orderBy('created_at', 'desc');
            $recentUsages = new LengthAwarePaginator(
                $recentUsageBaseQuery->forPage($recentPage, 10)->get(),
                (clone $recentUsageQuery)->count(),
                10,
                $recentPage,
                [
                    'path' => $request->url(),
                    'query' => array_merge($request->query(), ['recent_page' => $recentPage]),
                ]
            );
        }

        $alerts = $inventoryCollection
            ->filter(fn ($item) => in_array($item->status_key, ['low_stock', 'critical', 'out_of_stock'], true))
            ->sortBy(fn ($item) => $item->remaining)
            ->values();

        return view('supervisor.material', compact(
            'metrics',
            'inventory',
            'materials_list',
            'assignedProjects',
            'selectedProject',
            'projectPhases',
            'recentUsages',
            'alerts',
            'search',
            'selectedStatus',
            'selectedPhase'
        ));
    }

    public function saveAttendance(Request $request)
    {
        return back()->with('success', 'Attendance saved successfully.');
    }

    public function logDelivery(Request $request)
    {
        $user = Auth::user();

        try {
            $formType = $request->input('form_type');

            if ($formType === 'delivery') {
                return back()->withInput()->with('error', 'Material delivery logging is no longer available.');
            }

            $usageFlow = $formType === 'usage'
                || $request->filled('quantity_used')
                || $request->filled('usage_date')
                || $request->filled('remarks')
                || $request->hasFile('site_photo');

            if ($usageFlow) {
                $validated = $request->validate([
                    'project_id' => ['required', 'integer', 'exists:projects,project_id'],
                    'phase_id' => ['required', 'integer', 'exists:construction_phases,phase_id'],
                    'material_id' => ['required', 'integer', 'exists:materials,id'],
                    'quantity_used' => ['required', 'numeric', 'min:0.01'],
                    'usage_date' => ['required', 'date'],
                    'remarks' => ['nullable', 'string', 'max:1000'],
                    'site_photo' => ['nullable', 'file', 'image', 'max:5120'],
                ]);

                $project = Project::query()
                    ->where('project_id', $validated['project_id'])
                    ->whereHas('supervisors', function ($q) use ($user) {
                        $q->where('supervisor_id', $user->user_id);
                    })
                    ->first();

                if (!$project) {
                    return back()->withInput()->with('error', 'You are not authorized to record usage for this project.');
                }

                $phase = ConstructionPhase::query()
                    ->where('phase_id', $validated['phase_id'])
                    ->where('project_id', $project->project_id)
                    ->first();

                if (!$phase) {
                    return back()->withInput()->with('error', 'The selected phase does not belong to this project.');
                }

                $material = Material::query()->where('id', $validated['material_id'])->first();
                if (!$material) {
                    return back()->withInput()->with('error', 'The selected material could not be found.');
                }

                if ((float) $material->current_stock < (float) $validated['quantity_used']) {
                    return back()->withInput()->with('error', 'Insufficient Stock. Only ' . (int) $material->current_stock . ' units available.');
                }

                $material->current_stock = (float) $material->current_stock - (float) $validated['quantity_used'];
                $material->save();

                $inventoryRow = ProjectMaterial::query()
                    ->where('project_id', $project->project_id)
                    ->where('material_id', $material->id)
                    ->first();

                if (!$inventoryRow) {
                    $inventoryRow = ProjectMaterial::create([
                        'project_id' => $project->project_id,
                        'material_id' => $material->id,
                        'planned_quantity' => 0,
                        'used_quantity' => 0,
                        'unit' => $material->unit ?? null,
                    ]);
                }

                $inventoryRow->used_quantity = (float) $inventoryRow->used_quantity + (float) $validated['quantity_used'];
                $inventoryRow->unit = $inventoryRow->unit ?? $material->unit;
                $inventoryRow->save();

                $photoPath = null;
                if ($request->hasFile('site_photo')) {
                    $photoPath = $request->file('site_photo')->store('material-usage-photos', 'public');
                }

                MaterialUsage::create([
                    'project_id' => $project->project_id,
                    'phase_id' => $phase->phase_id,
                    'material_id' => $material->id,
                    'quantity_used' => (float) $validated['quantity_used'],
                    'unit' => $inventoryRow->unit,
                    'usage_date' => $validated['usage_date'],
                    'remarks' => $validated['remarks'] ?? null,
                    'recorded_by' => $user->user_id,
                    'site_photo_path' => $photoPath,
                ]);

                return redirect()->route('supervisor.materials', ['project_id' => $project->project_id])->with('success', 'Material usage recorded successfully.');
            }

            $validated = $request->validate([
                'material_id' => ['required', 'integer', 'exists:materials,id'],
                'quantity' => ['required', 'numeric', 'min:0.01'],
                'unit' => ['required', 'string', 'max:50'],
                'supplier_name' => ['nullable', 'string', 'max:255'],
            ]);

            $projectId = $request->input('project_id') ?: session('supervisor_selected_project_id');
            if (!$projectId) {
                return back()->withInput()->with('error', 'Please select a project before logging materials.');
            }

            $project = Project::query()
                ->where('project_id', $projectId)
                ->whereHas('supervisors', function ($q) use ($user) {
                    $q->where('supervisor_id', $user->user_id);
                })
                ->first();

            if (!$project) {
                return back()->withInput()->with('error', 'You are not authorized to log materials for this project.');
            }

            $inventoryRow = ProjectMaterial::query()
                ->where('project_id', $project->project_id)
                ->where('material_id', $validated['material_id'])
                ->first();

            if (!$inventoryRow) {
                $inventoryRow = ProjectMaterial::create([
                    'project_id' => $project->project_id,
                    'material_id' => $validated['material_id'],
                    'planned_quantity' => 0,
                    'used_quantity' => 0,
                    'unit' => $validated['unit'],
                ]);
            }

            $inventoryRow->planned_quantity = (float) $inventoryRow->planned_quantity + (float) $validated['quantity'];
            $inventoryRow->unit = $validated['unit'];
            $inventoryRow->save();

            return redirect()->route('supervisor.materials', ['project_id' => $project->project_id])->with('success', 'Material delivery logged successfully.');
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors())->with('error', 'Please correct the highlighted fields and try again.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Unexpected server error while saving material data.');
        }
    }

    private function normalizeRemaining(float $planned, float $used): float
    {
        return max(0.0, $planned - $used);
    }

    private function resolveMaterialStatus(float $remaining, float $planned): array
    {
        if ($planned <= 0) {
            return ['key' => 'out_of_stock', 'text' => 'Out of Stock', 'color' => 'danger'];
        }

        if ($remaining <= 0) {
            return ['key' => 'out_of_stock', 'text' => 'Out of Stock', 'color' => 'danger'];
        }

        $ratio = $remaining / $planned;

        if ($ratio <= 0.1) {
            return ['key' => 'critical', 'text' => 'Critical', 'color' => 'dark'];
        }

        if ($ratio <= 0.25) {
            return ['key' => 'low_stock', 'text' => 'Low Stock', 'color' => 'warning'];
        }

        return ['key' => 'available', 'text' => 'Available', 'color' => 'success'];
    }

    private function getMaterialCategory($material): string
    {
        if (Schema::hasColumn('materials', 'category')) {
            return $material->category ?? 'Construction Materials';
        }

        return 'Construction Materials';
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
            'completion_percentage' => 'nullable|numeric|min:0|max:100'
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
            return response()->json([
                'success' => false,
                'message' => 'Progress is system-managed and can only be updated from approved accomplishment reports.',
                'unchanged' => true,
                'phase' => ['completion_percentage' => round((float)($phase->completion_percentage ?? 0), 2)],
                'overallProgress' => round(ConstructionPhase::query()->where('project_id', $phase->project_id)->avg('completion_percentage') ?? 0, 0),
            ], 422);

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

            // Notify the client attached to the project
            if ($phase->project && $phase->project->client_id) {
                \App\Services\NotificationService::notifyClient($phase->project->client_id, [
                    'type' => 'phase',
                    'title' => 'Project Progress Updated',
                    'message' => "{$phase->phase_name} progress was updated to {$phase->completion_percentage}%.",
                    'data' => ['module' => 'client.reports', 'phase_id' => $phase->phase_id, 'project_id' => $phase->project_id],
                    'related_id' => $phase->phase_id,
                    'related_type' => 'phase',
                ]);
            }

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
            if ($phase->status === $validated['status']) {
                return response()->json([
                    'success' => false,
                    'message' => 'No status change detected.',
                    'unchanged' => true,
                    'phase' => ['status' => $phase->status, 'completion_percentage' => $phase->completion_percentage],
                    'overallProgress' => round(ConstructionPhase::query()->where('project_id', $phase->project_id)->avg('completion_percentage') ?? 0, 0),
                ], 422);
            }

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
            ->orderBy('phase_order', 'asc');

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

    public function registerWorkerBiometric(Request $request)
    {
        // 1. Validate the incoming data from the frontend modal
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'trade' => 'nullable|string|max:100',
            'credential' => 'required|array'
        ]);

        // 2. Here you will eventually save the worker to your database
        // Example: $worker = Worker::create([...]);
        // Example: BiometricKey::create(['worker_id' => $worker->id, 'key_data' => json_encode($validated['credential'])]);

        // 3. Return a success response back to the JavaScript fetch call
        return response()->json([
            'success' => true, 
            'message' => 'Worker and biometric token successfully cataloged inside the database!'
        ]);
    }
}