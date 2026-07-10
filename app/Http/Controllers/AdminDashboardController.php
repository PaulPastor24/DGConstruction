<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ConstructionPhase;
use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    /**
     * Display the management dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        $hasProjects = Schema::hasTable('projects');

        $hasReportsTable = Schema::hasTable(
            'accomplishment_reports'
        );

        $activeProjectsCount = $hasProjects
            ? DB::table('projects')
                ->whereIn('status', ['planning', 'ongoing'])
                ->count()
            : 0;

        $totalProjectsCount = $hasProjects
            ? DB::table('projects')->count()
            : 0;

        $executionRate = $totalProjectsCount > 0
            ? round(
                ($activeProjectsCount / $totalProjectsCount) * 100,
                1
            )
            : 0;

        $stats = [
            'active_projects' => $activeProjectsCount,

            'projects_change_label' =>
                $totalProjectsCount .
                ' total registered projects',

            'on_track_projects' => $activeProjectsCount,

            'completion_rate_label' =>
                "↑ {$executionRate}% execution rate",

            'total_workforce' => Schema::hasTable('users')
                ? DB::table('users')
                    ->where('role', 'supervisor')
                    ->count()
                : 0,

            'pending_reports' => $hasReportsTable
                ? DB::table('accomplishment_reports')
                    ->count()
                : 0,
        ];

        /*
        |--------------------------------------------------------------------------
        | Active projects
        |--------------------------------------------------------------------------
        */

        $activeProjects = collect();

        if ($hasProjects) {
            $activeProjects = Project::with('client.user')
                ->whereIn(
                    'status',
                    ['planning', 'ongoing']
                )
                ->orderByDesc('created_at')
                ->take(5)
                ->get()
                ->map(function ($project) {
                    $progressPercentage =
                        $project->progress_percentage ?? 0;

                    $color = 'blue';

                    if ($progressPercentage >= 80) {
                        $color = 'green';
                    } elseif ($progressPercentage < 40) {
                        $color = 'orange';
                    }

                    return (object) [
                        'id' => $project->project_id,

                        'name' =>
                            $project->project_name
                            ?? $project->name
                            ?? 'Unnamed Project',

                        'location' =>
                            $project->project_location
                            ?? $project->location
                            ?? 'No location',

                        'status_label' =>
                            ucfirst(
                                $project->status ?? 'planning'
                            ),

                        'current_phase' =>
                            $project->current_phase
                            ?? 'Phase 1: Mobilization',

                        'progress_percentage' =>
                            $progressPercentage,

                        'progress_color_class' =>
                            $color,

                        'target_end_date' =>
                            $project->target_end_date,
                    ];
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Today's attendance statistics
        |--------------------------------------------------------------------------
        */

        $today = Carbon::today();

        $attendance = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'rate' => 0,
        ];

        if (Schema::hasTable('attendance_logs')) {
            $present = Attendance::query()
                ->whereDate('log_date', $today)
                ->where('status', 'present')
                ->count();

            $absent = Attendance::query()
                ->whereDate('log_date', $today)
                ->where('status', 'absent')
                ->count();

            $late = Attendance::query()
                ->whereDate('log_date', $today)
                ->whereIn(
                    'status',
                    ['late', 'half_day', 'half day']
                )
                ->count();

            $totalExpected =
                $present + $absent + $late;

            $attendance = [
                'present' => $present,
                'absent' => $absent,
                'late' => $late,

                'rate' => $totalExpected > 0
                    ? round(
                        ($present / $totalExpected) * 100
                    )
                    : 0,
            ];
        }

        $burnRateData =
            $this->calculateMonthlyBurnRate();

        return view(
            'admin.dashboard',
            compact(
                'user',
                'stats',
                'activeProjects',
                'attendance',
                'burnRateData'
            )
        );
    }

    /**
     * Prepare monthly burn-rate data.
     */
    private function calculateMonthlyBurnRate(): array
    {
        $months = [];
        $bars = [];

        for ($i = 4; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);

            $months[] = $date->format('M');

            $monthlyCost = rand(15, 85);

            $bars[] = [
                'percentage' => $monthlyCost,
                'is_active' => $i === 0,
            ];
        }

        return [
            'bars' => $bars,
            'months' => $months,
            'current_cost' => 2.4,
            'trend_up' => false,
            'variance_percentage' => 5,
        ];
    }

    /**
     * Display the project timeline.
     */
    public function timeline(Request $request)
    {
        $hasProjects = Schema::hasTable('projects');

        $projects = $hasProjects
            ? Project::orderBy('project_name')->get()
            : collect();

        $selectedProject = null;
        $phases = collect();
        $milestones = collect();

        $stats = [
            'phases_done' => 0,
            'phases_processing' => 0,
            'phases_upcoming' => 0,
        ];

        if (
            $request->filled('project_id')
            && $hasProjects
        ) {
            $selectedProject = Project::with('phases')
                ->find($request->input('project_id'));

            if ($selectedProject) {
                $selectedProject->id =
                    $selectedProject->project_id;

                $selectedProject->name =
                    $selectedProject->project_name;

                $phases = $selectedProject->phases
                    ->map(function ($phase) {
                        $color = '#cbd5e1';
                        $statusText = 'Upcoming';

                        if (
                            $phase->status === 'completed'
                        ) {
                            $color = '#22c55e';
                            $statusText = 'Completed';
                        } elseif (
                            $phase->status === 'in_progress'
                        ) {
                            $color = '#eab308';
                            $statusText = 'In Progress';
                        }

                        return (object) [
                            'title' =>
                                $phase->phase_name,

                            'start_date' =>
                                $phase->start_date,

                            'end_date' =>
                                $phase->end_date,

                            'color_code' =>
                                $color,

                            'is_current' =>
                                $phase->status
                                === 'in_progress',

                            'status_note' =>
                                $phase->description
                                ?? 'Phase operations verified',

                            'progress_percentage' =>
                                $phase->completion_percentage
                                ?? 0,

                            'status_text' =>
                                $statusText,
                        ];
                    });

                $stats = [
                    'phases_done' =>
                        $selectedProject->phases
                            ->where(
                                'status',
                                'completed'
                            )
                            ->count(),

                    'phases_processing' =>
                        $selectedProject->phases
                            ->where(
                                'status',
                                'in_progress'
                            )
                            ->count(),

                    'phases_upcoming' =>
                        $selectedProject->phases
                            ->where(
                                'status',
                                'pending'
                            )
                            ->count(),
                ];

                $milestones = collect([
                    (object) [
                        'type' => 'info',

                        'type_label' =>
                            'MOBILIZATION',

                        'title' =>
                            'Site Operations Initialized',

                        'description' =>
                            'Equipment arrival and safety perimeter configurations cleared.',

                        'logged_at' =>
                            $selectedProject->start_date
                            ?? Carbon::now(),
                    ],

                    (object) [
                        'type' => 'warning',

                        'type_label' =>
                            'TARGET DEADLINE',

                        'title' =>
                            'Structural Hand-over Target',

                        'description' =>
                            'Core shell completion targeted baseline estimation.',

                        'logged_at' =>
                            $selectedProject->target_end_date
                            ?? Carbon::now()
                                ->addMonths(3),
                    ],
                ]);
            }
        }

        return view(
            'admin.timeline',
            compact(
                'projects',
                'selectedProject',
                'phases',
                'milestones',
                'stats'
            )
        );
    }

    /**
     * Display materials and inventory.
     */
    public function inventory(Request $request)
    {
        $projectId =
            $request->input('project_id');

        $projects = Schema::hasTable('projects')
            ? Project::orderBy('project_name')->get()
            : collect();

        $availableMaterials =
            Schema::hasTable('materials')
                ? DB::table('materials')
                    ->orderBy('name')
                    ->get()
                : collect();

        $inventoryItems = collect();

        $metrics = [
            'active_deliveries' => 0,
            'low_stock_alerts' => 0,
            'total_value' => 0.00,
        ];

        if (
            Schema::hasTable(
                'material_deliveries'
            )
        ) {
            $query = DB::table(
                'material_deliveries'
            );

            if ($projectId) {
                $query->where(
                    'project_id',
                    $projectId
                );
            }

            if (
                Schema::hasColumn(
                    'material_deliveries',
                    'delivered_at'
                )
            ) {
                $query->orderByDesc(
                    'delivered_at'
                );
            }

            $inventoryItems = $query->get();

            $metrics['active_deliveries'] =
                $inventoryItems->count();

            if (
                Schema::hasColumn(
                    'material_deliveries',
                    'total_price'
                )
            ) {
                $metrics['total_value'] =
                    $inventoryItems->sum(
                        'total_price'
                    );
            }
        }

        $haulingTrips = collect();
        $locations = collect();

        return view(
            'admin.inventory',
            compact(
                'metrics',
                'inventoryItems',
                'availableMaterials',
                'haulingTrips',
                'locations',
                'projects'
            )
        );
    }

    /**
     * Display admin reports.
     */
    public function reports(Request $request)
    {
        $hasReports = Schema::hasTable('accomplishment_reports');

        if (!$hasReports) {
            return view('admin.reports', [
                'reports' => collect(),
                'stats' => ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0],
                'projects' => collect(),
                'phases' => collect(),
                'supervisors' => collect(),
                'selectedProject' => null,
            ]);
        }

        $query = $this->buildReportQuery($request);
        $reports = $query->orderByDesc('created_at')->paginate(10)->appends($request->only(['project_id', 'phase_id', 'supervisor_id', 'status', 'search']));

        $stats = $this->buildReportStats($request);
        $projects = Project::query()->whereIn('status', ['planning', 'ongoing'])->orderBy('project_name')->get();
        $phases = $this->buildPhaseOptions($request->input('project_id'));
        $supervisors = User::query()
            ->where('role', 'supervisor')
            ->whereHas('submittedReports')
            ->orderBy('user_id')
            ->select([
                'user_id',
                DB::raw("CONCAT_WS(' ', first_name, last_name) as name")
            ])
            ->get();
        $selectedProject = $request->filled('project_id') ? Project::find($request->input('project_id')) : null;

        return view('admin.reports', compact('reports', 'stats', 'projects', 'phases', 'supervisors', 'selectedProject'));
    }

    public function reportsData(Request $request)
    {
        $hasReports = Schema::hasTable('accomplishment_reports');
        if (!$hasReports) {
            return response()->json([
                'reports' => [],
                'stats' => ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0],
                'projects' => [],
                'phases' => [],
                'supervisors' => [],
                'empty_message' => 'No accomplishment reports found.',
            ]);
        }

        $query = $this->buildReportQuery($request);
        $reports = $query->orderByDesc('created_at')->paginate(10)->appends($request->only(['project_id', 'phase_id', 'supervisor_id', 'status', 'search']));

        $payload = [
            'reports' => $reports->getCollection()->map(function (Report $report) {
                return [
                    'id' => $report->report_id,
                    'report_id' => $report->report_id,
                    'report_title' => $report->report_title,
                    'project_name' => optional($report->project)->project_name ?? 'Unassigned Project',
                    'phase_name' => optional($report->phase)->phase_name ?? 'Unassigned Phase',
                    'supervisor_name' => optional($report->submittedBy)->name ?? 'Unassigned Supervisor',
                    'submitted_at' => optional($report->report_date)->format('M d, Y') ?? $report->created_at->format('M d, Y'),
                    'submitted_time' => optional($report->report_date)->format('h:i A') ?? $report->created_at->format('h:i A'),
                    'status' => $report->approval_status,
                    'status_label' => $report->status_label,
                    'status_class' => $report->status_badge_class,
                    'project_id' => $report->project_id,
                    'phase_id' => $report->phase_id,
                    'submitted_by' => $report->submitted_by,
                    'report_text' => $report->report_text,
                    'approval_remarks' => $report->approval_remarks,
                    'approved_by' => optional($report->approvedBy)->name,
                    'approved_at' => optional($report->approved_at)->format('M d, Y h:i A'),
                    'completion_percentage' => round((float) optional($report->phase)->completion_percentage ?? 0, 2),
                    'site_images' => array_values(array_filter(array_map(function ($image) {
                        return is_string($image) && $image ? asset('storage/' . ltrim($image, '/')) : null;
                    }, (array) ($report->site_images ?? [])))),
                    'site_images_count' => count(array_filter((array) ($report->site_images ?? []))),
                ];
            })->values(),
            'stats' => $this->buildReportStats($request),
            'projects' => Project::query()->whereIn('status', ['planning', 'ongoing'])->orderBy('project_name')->get(['project_id', 'project_name']),
            'phases' => $this->buildPhaseOptions($request->input('project_id')),
            'supervisors' => User::query()
                ->where('role', 'supervisor')
                ->whereHas('submittedReports')
                ->orderBy('user_id')
                ->select([
                    'user_id',
                    DB::raw("CONCAT_WS(' ', first_name, last_name) as name")
                ])
                ->get(),
            'pagination' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'total' => $reports->total(),
                'from' => $reports->firstItem(),
                'to' => $reports->lastItem(),
            ],
            'empty_message' => $request->filled(['project_id', 'phase_id', 'supervisor_id', 'status', 'search']) ? 'No reports match your selected filters.' : 'No accomplishment reports found.',
        ];

        return response()->json($payload);
    }

    public function reportDetails($reportId)
    {
        $report = Report::with(['project', 'phase', 'submittedBy', 'approvedBy', 'reviewedBy'])->findOrFail($reportId);

        $materialUsage = [];
        if (Schema::hasTable('material_usages')) {
            $materialUsage = DB::table('material_usages')->where('project_id', $report->project_id)->where('phase_id', $report->phase_id)->get();
        }

        $attendanceSummary = null;
        try {
            $attendanceRows = DB::table('attendance_logs')
                ->where('project_id', $report->project_id)
                ->whereDate('log_date', $report->report_date)
                ->get();

            $attendanceSummary = [
                'present' => $attendanceRows->where('status', 'present')->count(),
                'absent' => $attendanceRows->where('status', 'absent')->count(),
                'total' => $attendanceRows->count(),
            ];
        } catch (\Throwable $e) {
            $attendanceSummary = null;
        }

        return response()->json([
            'success' => true,
            'report' => [
                'id' => $report->report_id,
                'report_id' => $report->report_id,
                'report_title' => $report->report_title,
                'project_name' => optional($report->project)->project_name ?? 'Unassigned Project',
                'phase_name' => optional($report->phase)->phase_name ?? 'Unassigned Phase',
                'supervisor_name' => optional($report->submittedBy)->name ?? 'Unassigned Supervisor',
                'submitted_at' => optional($report->report_date)->format('M d, Y') ?? $report->created_at->format('M d, Y'),
                'status' => $report->approval_status,
                'status_label' => $report->status_label,
                'report_text' => $report->report_text,
                'approval_remarks' => $report->approval_remarks,
                'approved_by' => optional($report->approvedBy)->name,
                'approved_at' => optional($report->approved_at)->format('M d, Y h:i A'),
                'completion_percentage' => round((float) optional($report->phase)->completion_percentage ?? 0, 2),
                'site_images' => array_values(array_filter(array_map(function ($image) {
                    return is_string($image) && $image ? asset('storage/' . ltrim($image, '/')) : null;
                }, (array) ($report->site_images ?? [])))),
                'material_usage' => $materialUsage,
                'attendance_summary' => $attendanceSummary,
            ],
        ]);
    }

    public function downloadReportPdf($reportId)
    {
        $report = Report::with(['project', 'phase', 'submittedBy', 'approvedBy'])->findOrFail($reportId);
        $pdfContents = $this->buildSimplePdf($report);

        return response($pdfContents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="report-' . $report->report_id . '.pdf"',
        ]);
    }

    private function buildReportQuery(Request $request)
    {
        $query = Report::query()->with(['project', 'phase', 'submittedBy', 'approvedBy']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->input('project_id'));
        }

        if ($request->filled('phase_id')) {
            $query->where('phase_id', $request->input('phase_id'));
        }

        if ($request->filled('supervisor_id')) {
            $query->where('submitted_by', $request->input('supervisor_id'));
        }

        $status = $request->input('status');
        if ($status === 'pending' || $status === 'approved' || $status === 'rejected') {
            $query->where('approval_status', $status);
        } elseif ($status === 'Pending Review') {
            $query->where('approval_status', 'pending');
        } elseif ($status === 'Approved') {
            $query->where('approval_status', 'approved');
        } elseif ($status === 'Rejected') {
            $query->where('approval_status', 'rejected');
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('report_text', 'like', "%{$search}%")
                    ->orWhere('report_id', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(LPAD(report_id, 4, '0')) LIKE ?", ["%{$search}%"])
                    ->orWhereHas('project', function ($projectQuery) use ($search) {
                        $projectQuery->where('project_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('phase', function ($phaseQuery) use ($search) {
                        $phaseQuery->where('phase_name', 'like', "%{$search}%");
                    })
                    ->orWhere(function ($reportQuery) use ($search) {
                        $reportQuery->whereHas('submittedBy', function ($userQuery) use ($search) {
                            $userQuery->where(function ($userQuery) use ($search) {
                                $added = false;

                                if (Schema::hasColumn('users', 'name')) {
                                    $userQuery->where('name', 'like', "%{$search}%");
                                    $added = true;
                                }

                                if (Schema::hasColumn('users', 'full_name')) {
                                    if ($added) {
                                        $userQuery->orWhere('full_name', 'like', "%{$search}%");
                                    } else {
                                        $userQuery->where('full_name', 'like', "%{$search}%");
                                        $added = true;
                                    }
                                }

                                if (Schema::hasColumn('users', 'first_name') || Schema::hasColumn('users', 'last_name')) {
                                    if ($added) {
                                        $userQuery->orWhere('first_name', 'like', "%{$search}%");
                                    } else {
                                        $userQuery->where('first_name', 'like', "%{$search}%");
                                        $added = true;
                                    }

                                    if (Schema::hasColumn('users', 'last_name')) {
                                        $userQuery->orWhere('last_name', 'like', "%{$search}%");
                                    }

                                    if (Schema::hasColumn('users', 'first_name') && Schema::hasColumn('users', 'last_name')) {
                                        $userQuery->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
                                    }
                                }
                            });
                        });
                    });
            });
        }

        return $query;
    }

    private function buildReportStats(Request $request)
    {
        $query = $this->buildReportQuery($request);

        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('approval_status', 'pending')->count(),
            'approved' => (clone $query)->where('approval_status', 'approved')->count(),
            'rejected' => (clone $query)->where('approval_status', 'rejected')->count(),
        ];
    }

    private function buildPhaseOptions($projectId = null)
    {
        return ConstructionPhase::query()
            ->when($projectId, function ($query) use ($projectId) {
                return $query->where('project_id', $projectId);
            })
            ->orderBy('project_id')
            ->orderBy('phase_order')
            ->get(['phase_id', 'phase_name', 'project_id']);
    }

    private function buildSimplePdf(Report $report): string
    {
        $lines = [
            'D&G Construction Management System',
            'Accomplishment Report',
            '',
            'Report ID: ' . $report->report_identifier,
            'Project: ' . optional($report->project)->project_name,
            'Construction Phase: ' . optional($report->phase)->phase_name,
            'Supervisor: ' . optional($report->submittedBy)->name,
            'Report Date: ' . optional($report->report_date)->format('M d, Y'),
            '',
            'Work Summary',
            Str::limit(strip_tags($report->report_text), 4000),
            '',
            'Progress: ' . (optional($report->phase)->completion_percentage ?? 0) . '%',
            'Status: ' . $report->status_label,
            'Approved By: ' . optional($report->approvedBy)->name,
            'Approval Date: ' . optional($report->approved_at)->format('M d, Y h:i A'),
            '',
            'Generated: ' . now()->format('M d, Y h:i A'),
            'Generated By: ' . Auth::user()->name ?? 'System',
        ];

        $text = implode("\n", $lines);
        $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        $stream = "BT\n/F1 10 Tf\n50 760 Td\n($escaped) Tj\nET";

        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";
        $objects[] = "4 0 obj\n<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream\nendobj\n";
        $objects[] = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $startXref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $startXref . "\n%%EOF\n";

        return $pdf;
    }

    /**
     * Display worker attendance.
     */
    public function attendance(Request $request)
    {
        $projects = Schema::hasTable('projects')
            ? Project::orderBy('project_name')->get()
            : collect();

        $filters = [
            'date' => $request->input('date', Carbon::today()->toDateString()),
            'project_id' => $request->input('project_id'),
            'status' => $request->input('status'),
            'biometric' => $request->input('biometric'),
            'search' => $request->input('search'),
        ];

        if (!Schema::hasTable('attendance_logs')) {
            $logs = collect();

            $stats = [
                'total' => 0,
                'present' => 0,
                'late' => 0,
                'absent' => 0,
                'missing_timeout' => 0,
                'break_exceeded' => 0,
                'verified' => 0,
            ];

            $issues = collect();

            return view(
                'admin.attendance',
                compact('logs', 'projects', 'filters', 'stats', 'issues')
            );
        }

        $query = Attendance::query()
            ->with([
                'worker',
                'deployment.worker',
                'deployment.project',
                'recordedBy',
            ]);

        if (!empty($filters['date'])) {
            $query->whereDate('log_date', $filters['date']);
        }

        if (!empty($filters['project_id'])) {
            $query->whereHas('deployment', function ($deploymentQuery) use ($filters) {
                $deploymentQuery->where('project_id', $filters['project_id']);
            });
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'late') {
                $query->whereIn('status', ['late', 'half_day', 'half day']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if ($filters['biometric'] === 'verified') {
            $query->where('biometric_matched', 1);
        }

        if ($filters['biometric'] === 'unverified') {
            $query->where(function ($biometricQuery) {
                $biometricQuery
                    ->where('biometric_matched', 0)
                    ->orWhereNull('biometric_matched');
            });
        }

        if (!empty($filters['search'])) {
            $keyword = '%' . $filters['search'] . '%';

            $query->where(function ($searchQuery) use ($keyword) {
                $searchQuery
                    ->where('status', 'like', $keyword)
                    ->orWhere('remarks', 'like', $keyword)
                    ->orWhereHas('worker', function ($workerQuery) use ($keyword) {
                        $workerQuery
                            ->where('first_name', 'like', $keyword)
                            ->orWhere('last_name', 'like', $keyword)
                            ->orWhere('trade', 'like', $keyword);
                    })
                    ->orWhereHas('deployment.worker', function ($workerQuery) use ($keyword) {
                        $workerQuery
                            ->where('first_name', 'like', $keyword)
                            ->orWhere('last_name', 'like', $keyword)
                            ->orWhere('trade', 'like', $keyword);
                    })
                    ->orWhereHas('deployment.project', function ($projectQuery) use ($keyword) {
                        $projectQuery
                            ->where('project_name', 'like', $keyword)
                            ->orWhere('project_location', 'like', $keyword);
                    })
                    ->orWhereHas('recordedBy', function ($userQuery) use ($keyword) {
                        $userQuery
                            ->where('first_name', 'like', $keyword)
                            ->orWhere('last_name', 'like', $keyword)
                            ->orWhere('name', 'like', $keyword);
                    });
            });
        }

        $logs = $query
            ->orderByDesc('log_date')
            ->orderByDesc('time_in')
            ->orderByDesc('log_id')
            ->get();

        $breakExceeded = function ($log) {
            if (!$log->break_out || !$log->break_in) {
                return false;
            }

            try {
                $date = $log->log_date
                    ? Carbon::parse($log->log_date)->toDateString()
                    : Carbon::today()->toDateString();

                $breakOut = Carbon::parse($date . ' ' . $log->break_out);
                $breakIn = Carbon::parse($date . ' ' . $log->break_in);

                return $breakOut->diffInMinutes($breakIn, false) > 60;
            } catch (\Throwable $error) {
                return false;
            }
        };

        $stats = [
            'total' => $logs->count(),

            'present' => $logs
                ->filter(fn ($log) => strtolower($log->status ?? '') === 'present')
                ->count(),

            'late' => $logs
                ->filter(fn ($log) => in_array(strtolower($log->status ?? ''), ['late', 'half_day', 'half day'], true))
                ->count(),

            'absent' => $logs
                ->filter(fn ($log) => strtolower($log->status ?? '') === 'absent')
                ->count(),

            'missing_timeout' => $logs
                ->filter(function ($log) {
                    $status = strtolower($log->status ?? '');

                    return $log->time_in
                        && !$log->time_out
                        && in_array($status, ['present', 'late', 'half_day', 'half day'], true);
                })
                ->count(),

            'break_exceeded' => $logs
                ->filter($breakExceeded)
                ->count(),

            'verified' => $logs
                ->filter(fn ($log) => (bool) $log->biometric_matched)
                ->count(),
        ];

        $issues = $logs
            ->filter(function ($log) use ($breakExceeded) {
                $status = strtolower($log->status ?? '');

                $isLate = in_array($status, ['late', 'half_day', 'half day'], true);
                $isAbsent = $status === 'absent';
                $missingTimeout = $log->time_in && !$log->time_out && in_array($status, ['present', 'late', 'half_day', 'half day'], true);
                $notVerified = !$log->biometric_matched;

                return $isLate || $isAbsent || $missingTimeout || $breakExceeded($log) || $notVerified;
            })
            ->take(8)
            ->values();

        return view(
            'admin.attendance',
            compact('logs', 'projects', 'filters', 'stats', 'issues')
        );
    }

    /**
     * Display system alerts.
     */
    public function alerts()
    {
        return view('admin.alerts');
    }

    /**
     * Update alert settings.
     */
    public function updateSettings(
        Request $request
    ) {
        return redirect()
            ->back()
            ->with(
                'success',
                'Notification settings updated.'
            );
    }

    /**
     * Store an incoming material delivery.
     */
    public function storeDelivery(
        Request $request
    ) {
        $validated = $request->validate([
            'project_id' => [
                'nullable',
                'integer',
            ],

            'material_id' => [
                'required',
                'integer',
            ],

            'quantity' => [
                'required',
                'numeric',
                'min:1',
            ],

            'unit' => [
                'required',
                'string',
                'max:50',
            ],

            'supplier_name' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $data = [
            'material_id' =>
                $validated['material_id'],

            'quantity' =>
                $validated['quantity'],

            'unit' =>
                $validated['unit'],

            'supplier_name' =>
                $validated['supplier_name'],

            'delivered_at' => now(),
        ];

        if (
            !empty($validated['project_id'])
        ) {
            $data['project_id'] =
                $validated['project_id'];
        }

        if (
            Schema::hasTable(
                'material_deliveries'
            )
        ) {
            DB::table('material_deliveries')
                ->insert($data);
        }

        return redirect()
            ->back()
            ->with(
                'success',
                'Material transaction processed successfully.'
            );
    }
}
