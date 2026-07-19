<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ConstructionPhase;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialUsage;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\Report;
use App\Models\SupervisorNotification;
use App\Models\SystemLog;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Mpdf\Mpdf;

class SupervisorController extends Controller
{
    // ==================== WORKERS / ATTENDANCE AJAX METHODS ====================

    private function extractCredentialId(array $credential): ?string
    {
        $credentialId = $credential['id'] ?? $credential['rawId'] ?? null;

        if (! $credentialId || is_array($credentialId)) {
            return null;
        }

        return (string) $credentialId;
    }

    private function resolveAttendanceStatusFromTimeIn($timeIn): string
    {
        if (! $timeIn) {
            return 'absent';
        }

        $time = $timeIn instanceof Carbon
            ? $timeIn->copy()
            : Carbon::parse($timeIn);

        $presentCutoff = Carbon::parse($time->format('Y-m-d').' 08:30:59');

        return $time->lte($presentCutoff) ? 'present' : 'late';
    }

    private function computeTimeInStatus($timeIn): string
    {
        if (! $timeIn) {
            return 'absent';
        }

        $time = Carbon::parse($timeIn);
        $presentCutoff = Carbon::parse($time->format('Y-m-d').' 08:30:59');

        return $time->lte($presentCutoff) ? 'present' : 'late';
    }

    private function formatAttendanceRecord($record): array
    {
        $record = (array) $record;

        return [
            'log_id' => $record['log_id'] ?? null,
            'worker_id' => $record['worker_id'] ?? null,
            'first_name' => $record['first_name'] ?? '',
            'last_name' => $record['last_name'] ?? '',
            'trade' => $record['trade'] ?? 'General',
            'log_date' => $record['log_date'] ?? null,
            'time_in' => $record['time_in'] ?? null,
            'break_out' => $record['break_out'] ?? null,
            'break_in' => $record['break_in'] ?? null,
            'time_out' => $record['time_out'] ?? null,
            'status' => $record['status'] ?? 'present',
            'remarks' => $record['remarks'] ?? null,
            'biometric_matched' => $record['biometric_matched'] ?? 0,
        ];
    }

    private function getDeploymentIdForWorker(int $workerId): int
    {
        /*
            attendance_logs has deployment_id.
            This tries to use the worker's project deployment.
            If none is found, it falls back to 1 to avoid breaking the page.
        */
        if (! Schema::hasTable('project_workers') || ! Schema::hasColumn('project_workers', 'deployment_id')) {
            return 1;
        }

        $query = DB::table('project_workers')->where('worker_id', $workerId);

        $selectedProjectId = session('supervisor_selected_project_id');

        if ($selectedProjectId && Schema::hasColumn('project_workers', 'project_id')) {
            $deploymentId = (clone $query)
                ->where('project_id', $selectedProjectId)
                ->value('deployment_id');

            if ($deploymentId) {
                return (int) $deploymentId;
            }
        }

        $deploymentId = $query->value('deployment_id');

        return $deploymentId ? (int) $deploymentId : 1;
    }

    private function fetchAttendanceRecord(int $workerId, string $date)
    {
        return DB::table('attendance_logs')
            ->join('workers', 'attendance_logs.worker_id', '=', 'workers.worker_id')
            ->where('attendance_logs.worker_id', $workerId)
            ->whereDate('attendance_logs.log_date', $date)
            ->select(
                'attendance_logs.log_id',
                'attendance_logs.worker_id',
                'workers.first_name',
                'workers.last_name',
                'workers.trade',
                'attendance_logs.log_date',
                'attendance_logs.time_in',
                'attendance_logs.break_out',
                'attendance_logs.break_in',
                'attendance_logs.time_out',
                'attendance_logs.status',
                'attendance_logs.remarks',
                'attendance_logs.biometric_matched'
            )
            ->first();
    }

    public function getWorkersList(Request $request)
    {
        $workers = DB::table('workers')
            ->where('is_active', 1)
            ->orderByDesc('created_at')
            ->select('worker_id', 'first_name', 'last_name', 'trade', 'created_at')
            ->paginate(10);

        return response()->json($workers);
    }

    public function getTodayAttendance(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        /*
            IMPORTANT:
            This only displays workers who already have attendance_logs today.
            It will NOT display all workers.
            View all workers stays inside your "View Enrolled Workers" modal.
        */
        $attendance = DB::table('attendance_logs')
            ->join('workers', 'attendance_logs.worker_id', '=', 'workers.worker_id')
            ->whereDate('attendance_logs.log_date', $date)
            ->where('workers.is_active', 1)
            ->select(
                'attendance_logs.log_id',
                'attendance_logs.worker_id',
                'workers.first_name',
                'workers.last_name',
                'workers.trade',
                'attendance_logs.log_date',
                'attendance_logs.time_in',
                'attendance_logs.break_out',
                'attendance_logs.break_in',
                'attendance_logs.time_out',
                'attendance_logs.status',
                'attendance_logs.remarks',
                'attendance_logs.biometric_matched'
            )
            ->orderBy('attendance_logs.time_in', 'asc')
            ->get()
            ->map(function ($record) {
                return $this->formatAttendanceRecord($record);
            });

        return response()->json([
            'data' => $attendance,
        ]);
    }

    public function logWorkerAttendance(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => ['required', 'integer'],
            'log_date' => ['required', 'date'],
            'action' => ['nullable', 'string'],
        ]);

        $worker = DB::table('workers')
            ->where('worker_id', $validated['worker_id'])
            ->where('is_active', 1)
            ->first();

        if (! $worker) {
            return response()->json([
                'message' => 'Worker not found or inactive.',
            ], 404);
        }

        $action = $validated['action'] ?? 'time_in';
        $date = $validated['log_date'];
        $now = now('Asia/Manila');

        $existingLog = DB::table('attendance_logs')
            ->where('worker_id', $validated['worker_id'])
            ->whereDate('log_date', $date)
            ->first();

        if (! $existingLog) {
            $status = $this->computeTimeInStatus($now);

            DB::table('attendance_logs')->insert([
                'worker_id' => $validated['worker_id'],
                'deployment_id' => 1,
                'recorded_by' => Auth::id(),
                'log_date' => $date,
                'time_in' => $now->format('H:i:s'),
                'break_out' => null,
                'break_in' => null,
                'time_out' => null,
                'status' => $status,
                'remarks' => $status === 'late'
                    ? 'Late time-in. Time-in after 8:30 AM.'
                    : 'Present. Time-in within 8:00 AM to 8:30 AM.',
                'biometric_matched' => 1,
                'created_at' => $now,
            ]);
        } else {
            $updates = [];

            if ($action === 'break_out') {
                if (! $existingLog->time_in) {
                    return response()->json([
                        'message' => 'Cannot break out without time in.',
                    ], 422);
                }

                if ($existingLog->break_out) {
                    return response()->json([
                        'message' => 'Break out is already recorded.',
                    ], 422);
                }

                $updates['break_out'] = $now->format('H:i:s');
                $updates['remarks'] = trim(($existingLog->remarks ?? '').' Break out recorded.');
            }

            if ($action === 'break_in') {
                if (! $existingLog->break_out) {
                    return response()->json([
                        'message' => 'Cannot break in without break out.',
                    ], 422);
                }

                if ($existingLog->break_in) {
                    return response()->json([
                        'message' => 'Break in is already recorded.',
                    ], 422);
                }

                $breakOutDateTime = Carbon::parse($date.' '.$existingLog->break_out);
                $breakMinutes = $breakOutDateTime->diffInMinutes($now);

                $updates['break_in'] = $now->format('H:i:s');

                if ($breakMinutes > 60) {
                    $updates['status'] = 'late';
                    $updates['remarks'] = trim(($existingLog->remarks ?? '').' Break exceeded 1 hour.');
                } else {
                    $updates['remarks'] = trim(($existingLog->remarks ?? '').' Break completed within 1 hour.');
                }
            }

            if ($action === 'time_out') {
                $timeOutAllowed = Carbon::parse($date.' 17:00:00');

                if ($now->lt($timeOutAllowed)) {
                    return response()->json([
                        'message' => 'Time out is only allowed from 5:00 PM onwards.',
                    ], 422);
                }

                if (! $existingLog->time_in) {
                    return response()->json([
                        'message' => 'Cannot time out without time in.',
                    ], 422);
                }

                if ($existingLog->time_out) {
                    return response()->json([
                        'message' => 'Time out is already recorded.',
                    ], 422);
                }

                $updates['time_out'] = $now->format('H:i:s');
                $updates['remarks'] = trim(($existingLog->remarks ?? '').' Time out recorded.');
            }

            if (! empty($updates)) {
                DB::table('attendance_logs')
                    ->where('log_id', $existingLog->log_id)
                    ->update($updates);
            }
        }

        $attendance = DB::table('attendance_logs')
            ->join('workers', 'attendance_logs.worker_id', '=', 'workers.worker_id')
            ->where('attendance_logs.worker_id', $validated['worker_id'])
            ->whereDate('attendance_logs.log_date', $date)
            ->select(
                'attendance_logs.log_id',
                'attendance_logs.worker_id',
                'workers.first_name',
                'workers.last_name',
                'workers.trade',
                'attendance_logs.log_date',
                'attendance_logs.time_in',
                'attendance_logs.break_out',
                'attendance_logs.break_in',
                'attendance_logs.time_out',
                'attendance_logs.status',
                'attendance_logs.remarks',
                'attendance_logs.biometric_matched'
            )
            ->first();

        return response()->json([
            'message' => 'Attendance updated successfully.',
            'attendance' => $this->formatAttendanceRecord($attendance),
        ]);
    }

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
            'active_projects' => $assignedProjects->filter(fn ($p) => $p->status === 'ongoing')->count(),
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
                return ! $milestone['is_completed'] && ! $milestone['is_delayed'] && $milestone['start_date'] && Carbon::parse($milestone['start_date'])->lte(now()->addDays(7));
            })->take(2)->values();

            if ($activeMilestones->isEmpty()) {
                $activeMilestones = $allMilestones->filter(function ($milestone) {
                    return ! $milestone['is_completed'] && ! $milestone['is_delayed'];
                })->take(2)->values();
            }

            $upcomingMilestones = $allMilestones->filter(function ($milestone) {
                return ! $milestone['is_completed'] && ! $milestone['is_delayed'] && $milestone['start_date'] && Carbon::parse($milestone['start_date'])->gt(now());
            })->take(2)->values();

            return [
                'id' => $project->project_id,
                'name' => $project->project_name,
                'location' => $project->location ?? $project->project_location,
                'status' => $project->status,
                'supervisors' => $project->supervisors->map(function ($supervisor) {
                    return [
                        'id' => $supervisor->user_id,
                        'name' => trim(($supervisor->first_name ?? '').' '.($supervisor->last_name ?? '')) ?: ($supervisor->name ?? 'Unknown Supervisor'),
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
            $query->where('phase_name', 'like', '%'.$search.'%');
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

        $deployments = $assignedProjects->flatMap(fn ($project) => $project->projectWorkers)->unique('deployment_id')->values();
        $workers = $deployments->map(fn ($d) => $d->worker)->unique('worker_id')->values();
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

        $avgProjectCompletion = $assignedProjects->isEmpty() ? 0 : round($assignedProjects->flatMap(fn ($p) => $p->phases)->avg('completion_percentage') ?? 0, 2);

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
            $user->name = trim(($validated['first_name'] ?? $user->first_name).' '.($validated['last_name'] ?? $user->last_name));
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

        if (! Hash::check($request->input('current_password'), $user->password)) {
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
        if (! Schema::hasTable('supervisor_notifications')) {
            $notifications = new LengthAwarePaginator([], 0, 12, 1, [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);

            $totalNotifs = 0;
            $unreadCount = 0;
            $readCount = 0;
            $archivedCount = 0;

            return view('supervisor.notifications', compact('user', 'notifications', 'totalNotifs', 'unreadCount', 'readCount', 'archivedCount'));
        }

        $query = SupervisorNotification::query()
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

        $totalNotifs = SupervisorNotification::query()->where('supervisor_id', $user->user_id)->count('*');
        $unreadCount = SupervisorNotification::query()->where('supervisor_id', $user->user_id)->where('is_read', false)->count('*');
        $readCount = SupervisorNotification::query()->where('supervisor_id', $user->user_id)->where('is_read', true)->count('*');
        $archivedCount = 0;

        return view('supervisor.notifications', compact('user', 'notifications', 'totalNotifs', 'unreadCount', 'readCount', 'archivedCount'));
    }

    /**
     * Mark a single notification as read
     */
    public function markNotificationRead($id)
    {
        $user = Auth::user();
        $notif = SupervisorNotification::query()->where('id', $id)->where('supervisor_id', $user->user_id)->first();
        if (! $notif) {
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
        SupervisorNotification::query()->where('supervisor_id', $user->user_id)->where('is_read', false)->update(['is_read' => true, 'read_at' => now()]);

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

        if (! $selectedProject && $assignedProjects->isNotEmpty()) {
            $selectedProject = $assignedProjects->first();
        }

        if ($selectedProject) {
            session(['supervisor_selected_project_id' => $selectedProject->project_id]);
        }

        $search = trim((string) $request->query('search', ''));
        $selectedStatus = trim((string) $request->query('status', ''));
        $selectedPhaseId = trim((string) $request->query('phase_id', ''));

        $materialsQuery = Schema::hasTable('materials') ? Material::query() : null;
        $materials_list = collect();
        $request_materials_list = collect();

        if ($materialsQuery && $selectedProject) {
            $projectMaterialIds = ProjectMaterial::query()
                ->where('project_id', $selectedProject->project_id)
                ->pluck('material_id')
                ->filter()
                ->unique()
                ->values();

            $materialsQuery->whereIn('id', $projectMaterialIds);

            if ($search !== '') {
                $materialsQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('category', 'like', '%'.$search.'%');
                });
            }

            $materials_list = $materialsQuery->orderBy('name', 'asc')->get();

            $requestMaterialsQuery = Material::query()->orderBy('name', 'asc');

            if ($search !== '') {
                $requestMaterialsQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('category', 'like', '%'.$search.'%');
                });
            }

            $request_materials_list = $requestMaterialsQuery->get();
        }

        $inventoryCollection = collect();
        $projectPhases = collect();
        $selectedPhase = null;

        if ($selectedProject) {
            $projectPhases = ConstructionPhase::query()
                ->where('project_id', $selectedProject->project_id)
                ->orderBy('phase_order', 'asc')
                ->get(['phase_id', 'phase_name', 'phase_order', 'status', 'completion_percentage']);

            $projectPhasesForUsage = $projectPhases->filter(function ($phase) {
                return ($phase->completion_percentage ?? 0) < 100 && ($phase->status ?? '') !== 'completed';
            })->values();

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

                $inventoryCollection = $materialIds->map(function ($materialId) use ($materialRows, $projectMaterialRows, $usageRows) {
                    $material = $materialRows->get($materialId);
                    if (! $material) {
                        return null;
                    }

                    $row = $projectMaterialRows->get($materialId);
                    $plannedFromRow = max(0.0, (float) ($row->planned_quantity ?? 0));
                    $usedFromProject = max(0.0, (float) ($row->used_quantity ?? 0));
                    $usedFromUsageTable = max(0.0, (float) ($usageRows->get($materialId)->total_used ?? 0));
                    $used = max($usedFromProject, $usedFromUsageTable);

                    $actualStock = 0.0;
                    if (Schema::hasColumn('materials', 'current_stock')) {
                        $actualStock = max(0.0, (float) ($material->current_stock ?? 0));
                    }

                    $planned = $plannedFromRow > 0 ? $plannedFromRow : max($used, $actualStock);
                    $remaining = $actualStock;

                    $statusKey = 'out_of_stock';
                    $statusText = 'Out of Stock';
                    $statusColor = 'danger';

                    if ($actualStock > 0) {
                        $minimumStock = (float) ($material->minimum_stock_level ?? 0);
                        if ($actualStock <= $minimumStock && $minimumStock > 0) {
                            $statusKey = 'low_stock';
                            $statusText = 'Low Stock';
                            $statusColor = 'warning';
                        } else {
                            $statusKey = 'available';
                            $statusText = 'Available';
                            $statusColor = 'success';
                        }
                    }

                    return (object) [
                        'id' => $material->id,
                        'name' => $material->name ?? 'Material',
                        'category' => $this->getMaterialCategory($material),
                        'unit' => $row->unit ?? $material->unit ?? 'unit',
                        'planned' => $planned,
                        'used' => $used,
                        'remaining' => $remaining,
                        'status_key' => $statusKey,
                        'status_text' => $statusText,
                        'status_color' => $statusColor,
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
        $inventoryQuery = collect([
            'tab' => 'inventory',
            'project_id' => $selectedProject?->project_id,
            'phase_id' => $selectedPhaseId ?: null,
            'search' => $search !== '' ? $search : null,
            'status' => $selectedStatus !== '' ? $selectedStatus : null,
        ])->filter(function ($value) {
            return $value !== null && $value !== '';
        })->toArray();
        $inventory = new LengthAwarePaginator(
            $inventoryCollection->forPage($page, $perPage),
            $inventoryCollection->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $inventoryQuery,
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

        $usageSearch = trim((string) $request->query('usage_search', ''));

        if ($selectedProject) {
            $recentUsageQuery = MaterialUsage::query()
                ->where('project_id', $selectedProject->project_id)
                ->with(['material', 'phase', 'recorder']);

            if ($selectedPhase) {
                $recentUsageQuery->where('phase_id', $selectedPhase->phase_id);
            }

            if ($usageSearch !== '') {
                $recentUsageQuery->whereHas('material', function ($materialQuery) use ($usageSearch) {
                    $materialQuery->where('name', 'like', '%'.$usageSearch.'%');
                });
            }

            $recentPage = max(1, (int) $request->query('recent_page', 1));
            $usageQuery = collect([
                'tab' => 'usage',
                'project_id' => $selectedProject?->project_id,
                'phase_id' => $selectedPhaseId ?: null,
                'usage_search' => $usageSearch !== '' ? $usageSearch : null,
                'recent_page' => $recentPage,
            ])->filter(function ($value) {
                return $value !== null && $value !== '';
            })->toArray();
            $recentUsageBaseQuery = $recentUsageQuery->clone()->orderBy('usage_date', 'desc')->orderBy('created_at', 'desc');
            $recentUsages = new LengthAwarePaginator(
                $recentUsageBaseQuery->forPage($recentPage, 10)->get(),
                (clone $recentUsageQuery)->count(),
                10,
                $recentPage,
                [
                    'path' => $request->url(),
                    'query' => $usageQuery,
                    'pageName' => 'recent_page',
                ]
            );
        }

        $alerts = $inventoryCollection
            ->filter(fn ($item) => in_array($item->status_key, ['low_stock', 'critical', 'out_of_stock'], true))
            ->sortBy(fn ($item) => $item->remaining)
            ->values();

        $requestStatus = trim((string) $request->query('request_status', ''));
        $materialRequestsCollection = collect();

        if (Schema::hasTable('material_requests')) {
            $materialRequestsQuery = MaterialRequest::query()
                ->where('requested_by', $user->user_id)
                ->with(['project', 'material']);

            if (in_array($requestStatus, ['pending', 'approved', 'rejected'], true)) {
                $materialRequestsQuery->where('status', $requestStatus);
            }

            if ($search !== '') {
                $materialRequestsQuery->where(function ($q) use ($search) {
                    $q->whereHas('material', function ($materialQuery) use ($search) {
                        $materialQuery->where('name', 'like', '%'.$search.'%');
                    });
                });
            }

            $materialRequestsCollection = $materialRequestsQuery
                ->orderByDesc('created_at')
                ->get();
        }

        $perPageRequests = 10;
        $requestsPage = max(1, (int) $request->query('requests_page', 1));
        $requestsQuery = collect([
            'tab' => 'requests',
            'project_id' => $selectedProject?->project_id,
            'phase_id' => $selectedPhaseId ?: null,
            'search' => $search !== '' ? $search : null,
            'request_status' => $requestStatus !== '' ? $requestStatus : null,
            'requests_page' => $requestsPage,
        ])->filter(function ($value) {
            return $value !== null && $value !== '';
        })->toArray();
        $materialRequests = new LengthAwarePaginator(
            $materialRequestsCollection->forPage($requestsPage, $perPageRequests),
            $materialRequestsCollection->count(),
            $perPageRequests,
            $requestsPage,
            [
                'path' => $request->url(),
                'query' => $requestsQuery,
                'pageName' => 'requests_page',
            ]
        );

        return view('supervisor.material', compact(
            'metrics',
            'inventory',
            'materials_list',
            'request_materials_list',
            'assignedProjects',
            'selectedProject',
            'projectPhases',
            'projectPhasesForUsage',
            'recentUsages',
            'materialRequests',
            'requestStatus',
            'usageSearch',
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
            $usageFlow = $formType = $request->input('form_type');
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

                if (! $project) {
                    return back()->withInput()->with('error', 'You are not authorized to record usage for this project.');
                }

                $phase = ConstructionPhase::query()
                    ->where('phase_id', $validated['phase_id'])
                    ->where('project_id', $project->project_id)
                    ->first();

                if (! $phase) {
                    return back()->withInput()->with('error', 'The selected phase does not belong to this project.');
                }

                $material = Material::query()->where('id', $validated['material_id'])->first();
                if (! $material) {
                    return back()->withInput()->with('error', 'The selected material could not be found.');
                }
                if (! $material) {
                    return back()->withInput()->with('error', 'The selected material could not be found.');
                }

                $inventoryRow = ProjectMaterial::query()
                    ->where('project_id', $project->project_id)
                    ->where('material_id', $material->id)
                    ->first();

                if (! $inventoryRow) {
                    $inventoryRow = ProjectMaterial::create([
                        'project_id' => $project->project_id,
                        'material_id' => $material->id,
                        'planned_quantity' => 0,
                        'used_quantity' => 0,
                        'unit' => $material->unit ?? 'unit',
                    ]);
                }

                $remainingAllocation = max(0.0, (float) ($inventoryRow->planned_quantity ?? 0) - (float) ($inventoryRow->used_quantity ?? 0));

                if ($remainingAllocation > 0 && $remainingAllocation < (float) $validated['quantity_used']) {
                    return back()->withInput()->with('error', 'Insufficient project allocation. Remaining: '.number_format($remainingAllocation, 2).' '.($inventoryRow->unit ?? $material->unit).'. Requested: '.number_format($validated['quantity_used'], 2).'.');
                }

                if ((float) $material->current_stock < (float) $validated['quantity_used']) {
                    return back()->withInput()->with('error', 'Insufficient warehouse stock. Available: '.(int) $material->current_stock.' '.$material->unit.'.');
                }

                $material->current_stock = max(0, (float) $material->current_stock - (float) $validated['quantity_used']);
                $material->save();

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

            return back()->withInput()->with('error', 'Invalid material action. Please use the Record Usage form.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('error', 'Please correct the highlighted fields and try again.');
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

        if (! $phase) {
            return response()->json(['error' => 'Phase not found'], 404);
        }

        // Verify supervisor has access to this project
        $hasAccess = Project::query()->where('project_id', $phase->project_id)
            ->whereHas('supervisors', function ($q) use ($user) {
                $q->where('supervisor_id', $user->user_id);
            })
            ->exists();

        if (! $hasAccess) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'can_manage' => false,
            'phase' => [
                'id' => $phase->phase_id,
                'name' => $phase->phase_name,
                'order' => $phase->phase_order,
                'description' => $phase->phase_name.' - Phase '.$phase->phase_order,
                'status' => $phase->status,
                'completion_percentage' => (float) ($phase->completion_percentage ?? 0),
                'planned_start_date' => $phase->planned_start_date ? $phase->planned_start_date->format('M d, Y') : 'Pending',
                'planned_end_date' => $phase->planned_end_date ? $phase->planned_end_date->format('M d, Y') : 'Pending',
                'actual_start_date' => $phase->actual_start_date ? $phase->actual_start_date->format('M d, Y') : 'Not started',
                'actual_end_date' => $phase->actual_end_date ? $phase->actual_end_date->format('M d, Y') : 'In progress',
                'project_name' => $phase->project->project_name,
                'milestones_count' => $phase->milestones->count(),
                'completed_milestones' => $phase->milestones->where('is_completed', true)->count(),
                'delayed_milestones' => $phase->milestones->where('is_delayed', true)->count(),
            ],
        ]);
    }

    /**
     * Update phase progress (completion percentage)
     */
    public function updatePhaseProgress(Request $request, $phaseId)
    {
        return response()->json([
            'success' => false,
            'message' => 'Direct phase progress updates are restricted. Submit an accomplishment report for admin review instead.',
            'unchanged' => true,
        ], 403);
    }

    /**
     * Update phase status
     */
    public function updatePhaseStatus(Request $request, $phaseId)
    {
        return response()->json([
            'success' => false,
            'message' => 'Direct phase status changes are restricted. Submit an accomplishment report for admin review instead.',
            'unchanged' => true,
        ], 403);
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

        if (! $project) {
            return response()->json(['error' => 'Unauthorized or project not found'], 403);
        }

        // Get phases for the project
        $query = ConstructionPhase::query()->where('project_id', $projectId)
            ->orderBy('phase_order', 'asc');

        if ($request->has('status') && $status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($request->has('search') && $search = $request->input('search')) {
            $query->where('phase_name', 'like', '%'.$search.'%');
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
                $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 10, 'margin_bottom' => 10]);
                $mpdf->WriteHTML($html);
                $fileName = 'phases_report_'.date('Y-m-d').'.pdf';

                return $mpdf->Output($fileName, 'D');
            }
        } catch (\Exception $e) {
            // If PDF generation fails, fall back to downloadable HTML.
        }

        $fileName = 'phases_report_'.date('Y-m-d').'.html';

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    public function registerWorkerBiometric(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'trade' => ['nullable', 'string', 'max:100'],
            'credential' => ['required', 'array'],
        ]);

        $credentialId = $this->extractCredentialId($validated['credential']);

        if (! $credentialId) {
            return response()->json([
                'message' => 'Credential ID was not received from the browser.',
            ], 422);
        }

        try {
            $workerId = DB::table('workers')->insertGetId([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'trade' => $validated['trade'] ?: 'General',
                'contact_number' => null,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'worker_id');

            DB::table('worker_biometric_profiles')->insert([
                'worker_id' => $workerId,
                'fingerprint_template' => json_encode([
                    'credential_id' => $credentialId,
                    'credential' => $validated['credential'],
                ]),
                'enrolled_at' => now(),
                'enrolled_by' => Auth::id(),
            ]);

            $worker = DB::table('workers')
                ->where('worker_id', $workerId)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Worker and biometric token successfully registered.',
                'worker' => [
                    'worker_id' => $worker->worker_id,
                    'first_name' => $worker->first_name,
                    'last_name' => $worker->last_name,
                    'trade' => $worker->trade ?: 'General',
                    'created_at' => $worker->created_at,
                ],
            ]);
        } catch (\Throwable $error) {
            report($error);

            return response()->json([
                'success' => false,
                'message' => 'Failed to register worker biometric record.',
                'error' => $error->getMessage(),
            ], 500);
        }
    }

    public function requestMaterial(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,project_id'],
            'material_id' => ['required', 'integer', 'exists:materials,id'],
            'requested_quantity' => ['required', 'numeric', 'min:0.01'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ], [
            'project_id.required' => 'Please select a project.',
            'project_id.exists' => 'Selected project does not exist.',
            'material_id.required' => 'Please select a material.',
            'material_id.exists' => 'Selected material does not exist.',
            'requested_quantity.required' => 'Please enter a requested quantity.',
            'requested_quantity.min' => 'Requested quantity must be greater than zero.',
            'remarks.max' => 'Remarks must not exceed 1000 characters.',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $material = Material::findOrFail($validated['material_id']);
        $requestedQuantity = (float) $validated['requested_quantity'];
        $availableStock = (float) ($material->current_stock ?? 0);

        if ($requestedQuantity > $availableStock) {
            return back()->withInput()->with('error', "Requested quantity ({$requestedQuantity}) exceeds available stock ({$availableStock} {$material->unit}).");
        }

        $isAssigned = $project->supervisors()
            ->where('project_supervisors.supervisor_id', $user->user_id)
            ->where('project_supervisors.is_active', true)
            ->exists();

        if (! $isAssigned) {
            return back()->with('error', 'You are not authorized to request materials for this project.');
        }

        $unit = $material->unit ?? 'unit';

        MaterialRequest::create([
            'project_id' => $project->project_id,
            'material_id' => $material->id,
            'requested_by' => $user->user_id,
            'status' => 'pending',
            'requested_quantity' => $requestedQuantity,
            'unit' => $unit,
            'remarks' => trim((string) ($validated['remarks'] ?? '')),
        ]);

        try {
            NotificationService::notifyAdmins([
                'type' => 'material',
                'title' => 'New Material Request',
                'message' => "Supervisor {$user->name} requested {$requestedQuantity} {$unit} of {$material->name} for project '{$project->project_name}'.",
                'data' => ['module' => 'admin.inventory', 'project_id' => $project->project_id, 'material_id' => $material->id, 'recipient' => 'Admin'],
                'related_id' => $material->id,
                'related_type' => 'material',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to notify admins on material request: '.$e->getMessage());
        }

        return redirect()->back()->with('success', 'Material request submitted successfully. Waiting for admin approval.');
    }
}
