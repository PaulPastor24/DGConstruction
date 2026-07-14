<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\Attendance;
use App\Models\ConstructionPhase;
use App\Models\Material;
use App\Models\MaterialDelivery;
use App\Models\MaterialUsage;
use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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

            'inventory_count' => Schema::hasTable('materials')
                ? DB::table('materials')->count()
                : 0,
        ];

        /*
        |--------------------------------------------------------------------------
        | Active projects
        |--------------------------------------------------------------------------
        */

        $activeProjects = collect();

        $overallProgress = [
            'percentage' => 0,
            'on_track' => 0,
            'delayed' => 0,
            'total' => 0,
        ];

        if ($hasProjects) {
            $projectsQuery = Project::with(['client.user', 'phases'])
                ->whereIn(
                    'status',
                    ['planning', 'ongoing']
                )
                ->orderByDesc('created_at')
                ->take(5);

            $activeProjects = $projectsQuery->get()->map(function ($project) {
                $phases = $project->phases;

                $progressPercentage = $phases->isNotEmpty()
                    ? round($phases->avg('completion_percentage'), 2)
                    : 0;

                $color = 'blue';

                if ($progressPercentage >= 80) {
                    $color = 'green';
                } elseif ($progressPercentage < 40) {
                    $color = 'orange';
                }

                $currentPhase = $phases->firstWhere('status', 'in_progress');

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
                        $currentPhase->phase_name
                        ?? 'Phase 1: Mobilization',

                    'image' =>
                        $project->image_url,

                    'progress_percentage' =>
                        $progressPercentage,

                    'progress_color_class' =>
                        $color,

                    'target_end_date' =>
                        $project->target_end_date,
                ];
            });

            $allActiveProjects = Project::with('phases')
                ->whereIn('status', ['planning', 'ongoing'])
                ->get();

            $overallProgress['total'] = $allActiveProjects->count();

            if ($overallProgress['total'] > 0) {
                $totalProgress = $allActiveProjects->reduce(function ($carry, $project) {
                    $phases = $project->phases;

                    return $carry + ($phases->isNotEmpty()
                        ? $phases->avg('completion_percentage')
                        : 0);
                }, 0);

                $overallProgress['percentage'] = round($totalProgress / $overallProgress['total'], 2);

                $overallProgress['on_track'] = $allActiveProjects->filter(function ($project) {
                    $phases = $project->phases;

                    return $phases->isNotEmpty()
                        ? $phases->avg('completion_percentage') >= 50
                        : false;
                })->count();

                $overallProgress['delayed'] = $overallProgress['total'] - $overallProgress['on_track'];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Recent accomplishment reports
        |--------------------------------------------------------------------------
        */

        $recentReports = collect();

        if ($hasReportsTable) {
            $recentReports = Report::with(['project', 'phase', 'submittedBy'])
                ->orderByDesc('report_date')
                ->orderByDesc('created_at')
                ->take(2)
                ->get()
                ->map(function ($report) {
                    return (object) [
                        'id' => $report->report_id,
                        'title' => $report->report_title,
                        'project_name' => optional($report->project)->project_name ?? 'Unassigned Project',
                        'phase_name' => optional($report->phase)->phase_name ?? 'Unassigned Phase',
                        'supervisor_name' => optional($report->submittedBy)->name ?? 'Unassigned',
                        'status' => $report->approval_status,
                        'status_label' => $report->status_label,
                        'status_class' => $report->status_badge_class,
                        'submitted_at' => optional($report->report_date)->format('M d, Y') ?? $report->created_at->format('M d, Y'),
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
                ->whereDate('log_date', '=', $today, 'and')
                ->where('status', '=', 'present', 'and')
                ->count('*');

            $absent = Attendance::query()
                ->whereDate('log_date', '=', $today, 'and')
                ->where('status', '=', 'absent', 'and')
                ->count('*');

            $late = Attendance::query()
                ->whereDate('log_date', '=', $today, 'and')
                ->whereIn('status', ['late', 'half_day', 'half day'], 'and', false)
                ->count('*');

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
                'burnRateData',
                'overallProgress',
                'recentReports'
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
            ? Project::query()->orderBy('project_name', 'asc')->get()
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
            $selectedProject = Project::query()
                ->with('phases')
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
        $search = trim((string) $request->input('search', ''));
        $category = trim((string) $request->input('category', ''));
        $stockStatus = trim((string) $request->input('stock_status', ''));
        $usageCategory = trim((string) $request->input('usage_category', ''));
        $usageStatus = trim((string) $request->input('usage_status', ''));
        $activeView = $request->input('view', 'inventory');
        $activeView = in_array($activeView, ['inventory', 'usage'], true) ? $activeView : 'inventory';
        $searchForUsage = $search;

        $query = Material::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('supplier', 'like', '%' . $search . '%');
            });
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        if ($stockStatus !== '') {
            $query->where(function ($q) use ($stockStatus) {
                    if ($stockStatus === 'low_stock') {
                        $q->whereColumn('current_stock', '<=', 'minimum_stock_level', 'and')
                            ->where('current_stock', '>', 0);
                    } elseif ($stockStatus === 'normal') {
                        $q->whereColumn('current_stock', '>', 'minimum_stock_level', 'and');
                } elseif ($stockStatus === 'out_of_stock') {
                    $q->where('current_stock', '<=', 0);
                }
            });
        }

        $materials = $query
            ->orderByDesc('updated_at')
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->appends($request->only(['search', 'category', 'stock_status', 'usage_category', 'usage_status']));

        $usageQuery = MaterialUsage::query()->with(['project', 'phase', 'material', 'recorder']);
        $hasUserNameColumn = Schema::hasTable('users') && Schema::hasColumn('users', 'name');
        $hasUserFirstNameColumn = Schema::hasTable('users') && Schema::hasColumn('users', 'first_name');
        $hasUserLastNameColumn = Schema::hasTable('users') && Schema::hasColumn('users', 'last_name');
        $canSearchUsers = $hasUserNameColumn || $hasUserFirstNameColumn || $hasUserLastNameColumn;

        if ($usageCategory !== '') {
            $usageQuery->whereHas('material', function ($materialQuery) use ($usageCategory) {
                $materialQuery->where('category', $usageCategory);
            });
        }

        if ($usageStatus !== '') {
            $usageQuery->when($usageStatus === 'with_remarks', function ($query) {
                $query->whereNotNull('remarks')->where('remarks', '!=', '');
            })->when($usageStatus === 'without_remarks', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereNull('remarks')->orWhere('remarks', '');
                });
            });
        }

        if ($search !== '') {
            $usageSearchValue = $search;
            $usageQuery->where(function ($q) use ($usageSearchValue, $canSearchUsers, $hasUserNameColumn, $hasUserFirstNameColumn, $hasUserLastNameColumn) {
                $q->whereHas('material', function ($materialQuery) use ($usageSearchValue) {
                    $materialQuery->where('name', 'like', '%' . $usageSearchValue . '%');
                })
                ->orWhereHas('project', function ($projectQuery) use ($usageSearchValue) {
                    $projectQuery->where('project_name', 'like', '%' . $usageSearchValue . '%');
                })
                ->when(Schema::hasTable('construction_phases'), function ($query) use ($usageSearchValue) {
                    $query->orWhereHas('phase', function ($phaseQuery) use ($usageSearchValue) {
                        $phaseQuery->where('phase_name', 'like', '%' . $usageSearchValue . '%');
                    });
                })
                ->when($canSearchUsers, function ($query) use ($usageSearchValue, $hasUserNameColumn, $hasUserFirstNameColumn, $hasUserLastNameColumn) {
                    $query->orWhereHas('recorder', function ($userQuery) use ($usageSearchValue, $hasUserNameColumn, $hasUserFirstNameColumn, $hasUserLastNameColumn) {
                        $userQuery->where(function ($nestedQuery) use ($usageSearchValue, $hasUserNameColumn, $hasUserFirstNameColumn, $hasUserLastNameColumn) {
                            if ($hasUserNameColumn) {
                                $nestedQuery->where('name', 'like', '%' . $usageSearchValue . '%');
                            }

                            if ($hasUserFirstNameColumn) {
                                $nestedQuery->orWhere('first_name', 'like', '%' . $usageSearchValue . '%');
                            }

                            if ($hasUserLastNameColumn) {
                                $nestedQuery->orWhere('last_name', 'like', '%' . $usageSearchValue . '%');
                            }
                        });
                    });
                })
                ->orWhere('remarks', 'like', '%' . $usageSearchValue . '%')
                ->orWhere('unit', 'like', '%' . $usageSearchValue . '%')
                ->orWhere('quantity_used', 'like', '%' . $usageSearchValue . '%');
            });
        }

        $totalMaterials = Material::count('*');
        $availableMaterials = Material::where('current_stock', '>', 0, 'and')->count('*');
        $lowStockAlerts = Material::whereColumn('current_stock', '<=', 'minimum_stock_level', 'and')->where('current_stock', '>', 0, 'and')->count('*');
        $outOfStock = Material::where('current_stock', '<=', 0, 'and')->count('*');
        $metrics = [
            'total_materials' => $totalMaterials,
            'available_materials' => $availableMaterials,
            'low_stock_alerts' => $lowStockAlerts,
            'out_of_stock' => $outOfStock,
            'available_percentage' => $totalMaterials > 0 ? round(($availableMaterials / $totalMaterials) * 100, 1) : 0,
            'low_stock_percentage' => $totalMaterials > 0 ? round(($lowStockAlerts / $totalMaterials) * 100, 1) : 0,
            'out_of_stock_percentage' => $totalMaterials > 0 ? round(($outOfStock / $totalMaterials) * 100, 1) : 0,
        ];

          $lowStockMaterials = Material::query()
              ->whereColumn('current_stock', '<=', 'minimum_stock_level', 'and')
            ->where('current_stock', '>', 0, 'and')
            ->orderBy('current_stock', 'asc')
            ->orderBy('name', 'asc')
            ->take(4)
            ->get();

        $allLowStockMaterials = Material::query()
              ->whereColumn('current_stock', '<=', 'minimum_stock_level', 'and')
            ->where('current_stock', '>', 0, 'and')
            ->orderBy('current_stock', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(10);

        $recentlyUpdatedMaterials = Material::query()
            ->where('current_stock', '>', 0)
            ->orderByDesc('updated_at')
            ->take(3)
            ->get();

        $allRecentlyUpdatedMaterials = Material::query()
            ->where('current_stock', '>', 0)
            ->orderByDesc('updated_at')
            ->paginate(10);

        $usageLogs = $usageQuery
            ->orderByDesc('usage_date')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->appends($request->only(['search', 'category', 'stock_status', 'usage_category', 'usage_status']));

        $categories = Material::query()->distinct()->pluck('category')->filter()->sort()->values();

        return view('admin.inventory', compact('materials', 'metrics', 'usageLogs', 'categories', 'search', 'category', 'stockStatus', 'usageCategory', 'usageStatus', 'activeView', 'lowStockMaterials', 'allLowStockMaterials', 'recentlyUpdatedMaterials', 'allRecentlyUpdatedMaterials'));
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
        $projects = Project::query()
            ->whereIn('status', ['planning', 'ongoing'], 'and', false)
            ->orderBy('project_name', 'asc')
            ->get();
        $phases = $this->buildPhaseOptions($request->input('project_id'));
        $supervisors = User::query()
            ->where('role', 'supervisor')
            ->whereHas('submittedReports')
            ->orderBy('user_id', 'asc')
            ->select([
                'user_id',
                DB::raw("CONCAT_WS(' ', first_name, last_name) as name")
            ])
            ->get();
        $selectedProject = $request->filled('project_id') ? Project::query()->find($request->input('project_id')) : null;

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
            'projects' => Project::query()
                ->whereIn('status', ['planning', 'ongoing'], 'and', false)
                ->orderBy('project_name', 'asc')
                ->select(['project_id', 'project_name'])
                ->get(),
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
            ? Project::query()->orderBy('project_name', 'asc')->get()
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
     * Display system alerts / admin notifications.
     */
    public function alerts(Request $request)
    {
        $user = Auth::user();

        // Avoid querying a missing table if the migration hasn't run yet.
        if (!Schema::hasTable('admin_notifications')) {
            $notifications = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

            $summary = [
                'total_count' => 0,
                'unread_count' => 0,
                'sent_this_month' => 0,
                'total_recipients' => 0,
            ];

            return view('admin.alerts', compact('notifications', 'summary'));
        }

        $baseQuery = AdminNotification::query()->where('admin_id', $user->user_id);

        $totalCount = (clone $baseQuery)->count('*');
        $unreadCount = (clone $baseQuery)->where('is_read', false)->count('*');
        $sentThisMonth = (clone $baseQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count('*');
        $totalRecipients = (int) DB::table('users')
            ->whereIn('role', ['engineer', 'admin', 'administrator'])
            ->count();

        $summary = [
            'total_count' => $totalCount,
            'unread_count' => $unreadCount,
            'sent_this_month' => $sentThisMonth,
            'total_recipients' => $totalRecipients,
        ];

        $query = AdminNotification::query()
            ->where('admin_id', $user->user_id)
            ->orderBy('created_at', 'desc');

        $type = strtolower((string) $request->query('type', 'all'));

        if ($type !== 'all' && $type !== '') {
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
                case 'milestone':
                case 'assignment':
                case 'system':
                case 'user':
                case 'reminder':
                case 'announcement':
                    $query->where('type', $type);
                    break;
                default:
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = strtolower((string) $request->query('status'));
            if ($status === 'unread') {
                $query->where('is_read', false);
            } elseif ($status === 'read') {
                $query->where('is_read', true);
            }
        }

        $notifications = $query->paginate(10)->withQueryString();

        return view('admin.alerts', compact('notifications', 'summary'));
    }

    /**
     * Mark a single admin notification as read.
     */
    public function markNotificationRead($id)
    {
        $user = Auth::user();

        $notification = AdminNotification::query()
            ->where('id', $id)
            ->where('admin_id', $user->user_id)
            ->first();

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $notification->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all admin notifications as read for the logged in admin.
     */
    public function markAllNotificationsRead()
    {
        $user = Auth::user();

        AdminNotification::query()
            ->where('admin_id', $user->user_id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a single admin notification.
     */
    public function destroyNotification($id)
    {
        $user = Auth::user();

        $notification = AdminNotification::query()
            ->where('id', $id)
            ->where('admin_id', $user->user_id)
            ->first();

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        DB::table('admin_notifications')
            ->where('id', $notification->getKey())
            ->delete();

        return response()->json(['success' => true]);
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

    public function storeMaterial(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('materials', 'name')],
                'category' => ['nullable', 'string', 'max:255'],
                'unit' => ['required', 'string', 'max:50'],
                'current_stock' => ['required', 'numeric', 'min:0', 'max:1000000000'],
                'minimum_stock_level' => ['required', 'numeric', 'min:0', 'max:1000000000'],
                'supplier' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
            ], [
                'name.required' => 'Material name is required.',
                'name.unique' => 'A material with this name already exists.',
                'unit.required' => 'Please provide the material unit.',
                'current_stock.min' => 'Current stock cannot be negative.',
                'minimum_stock_level.min' => 'Minimum stock cannot be negative.',
            ]);

            $validated['name'] = trim((string) ($validated['name'] ?? ''));
            $validated['category'] = trim((string) ($validated['category'] ?? '')) ?: null;
            $validated['unit'] = trim((string) ($validated['unit'] ?? ''));
            $validated['supplier'] = trim((string) ($validated['supplier'] ?? '')) ?: null;
            $validated['description'] = trim((string) ($validated['description'] ?? '')) ?: null;

            Material::create($validated);

            return redirect()->back()->with('success', 'Material added successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to add material right now. Please try again.')->withInput();
        }
    }

    public function updateMaterial(Request $request, Material $material)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('materials', 'name')->ignore($material->id)],
                'category' => ['nullable', 'string', 'max:255'],
                'unit' => ['required', 'string', 'max:50'],
                'minimum_stock_level' => ['required', 'numeric', 'min:0', 'max:1000000000'],
                'supplier' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
            ], [
                'name.required' => 'Material name is required.',
                'name.unique' => 'A material with this name already exists.',
                'unit.required' => 'Please provide the material unit.',
                'minimum_stock_level.min' => 'Minimum stock cannot be negative.',
            ]);

            $validated['name'] = trim((string) ($validated['name'] ?? ''));
            $validated['category'] = trim((string) ($validated['category'] ?? '')) ?: null;
            $validated['unit'] = trim((string) ($validated['unit'] ?? ''));
            $validated['supplier'] = trim((string) ($validated['supplier'] ?? '')) ?: null;
            $validated['description'] = trim((string) ($validated['description'] ?? '')) ?: null;

            $material->update($validated);

            return redirect()->back()->with('success', 'Material updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to update material right now. Please try again.')->withInput();
        }
    }

    public function receiveStock(Request $request, ?Material $material = null)
    {
        try {
            $validator = Validator::make($request->all(), [
                'material_id' => ['nullable'],
                'material_name' => ['nullable', 'string', 'max:255'],
                'category' => ['nullable', 'string', 'max:255'],
                'quantity_received' => ['required', 'numeric', 'min:0.01', 'max:1000000000'],
                'received_date' => ['required', 'date'],
                'supplier' => ['nullable', 'string', 'max:255'],
                'notes' => ['nullable', 'string'],
                'remarks' => ['nullable', 'string'],
            ], [
                'quantity_received.required' => 'Please enter a quantity to receive.',
                'quantity_received.min' => 'Received quantity must be greater than zero.',
                'received_date.required' => 'Please select a received date.',
                'received_date.date' => 'Please enter a valid date.',
            ]);

            $validator->after(function ($validator) use ($request, $material) {
                if ($material instanceof Material) {
                    return;
                }

                $selectedMaterialId = $request->input('material_id');
                $materialName = trim((string) $request->input('material_name'));

                if ((empty($selectedMaterialId) || $selectedMaterialId === 'new') && $materialName === '') {
                    $validator->errors()->add('material_name', 'Please select a material or enter a new material name.');
                }

                // When creating a new material, category should be provided
                $categoryValue = trim((string) $request->input('category', ''));
                if (($selectedMaterialId === 'new' || empty($selectedMaterialId)) && $materialName !== '' && $categoryValue === '') {
                    $validator->errors()->add('category', 'Please enter a category for the new material.');
                }

                if (is_numeric($selectedMaterialId) && (int) $selectedMaterialId > 0 && !Material::query()->where('id', (int) $selectedMaterialId)->exists()) {
                    $validator->errors()->add('material_id', 'The selected material does not exist.');
                }
            });

            $validated = $validator->validate();

            $materialName = trim((string) ($validated['material_name'] ?? ''));
            $selectedMaterialId = $request->input('material_id');

            if ($material instanceof Material) {
                $materialRecord = $material;
            } elseif (is_numeric($selectedMaterialId) && (int) $selectedMaterialId > 0) {
                $materialRecord = Material::query()->find((int) $selectedMaterialId);

                if (!$materialRecord) {
                    throw new \InvalidArgumentException('Selected material was not found.');
                }
            } elseif ($materialName !== '') {

                $materialRecord = Material::query()->whereRaw('LOWER(name) = ?', [Str::lower($materialName)], 'and')->first();
                if (!$materialRecord) {
                    $materialRecord = Material::create([
                        'name' => $materialName,
                        'category' => trim((string) ($validated['category'] ?? '')) ?: null,
                        'unit' => 'Unit',
                        'current_stock' => 0,
                        'minimum_stock_level' => 0,
                        'supplier' => trim((string) ($validated['supplier'] ?? '')) ?: null,
                        'description' => null,
                    ]);
                }
            } else {
                throw new \InvalidArgumentException('Material is required.');
            }

            // Update category if provided (for existing material the user may update category here)
            if (!empty($validated['category'])) {
                $materialRecord->category = trim((string) $validated['category']);
            }

            $materialRecord->current_stock = max(0, (float) $materialRecord->current_stock + (float) $validated['quantity_received']);
            $materialRecord->supplier = trim((string) ($validated['supplier'] ?? '')) ?: $materialRecord->supplier;
            $materialRecord->save();

            // If stock is at or below minimum after receiving (possible when receiving negative adjustments), notify admins
            try {
                if ($materialRecord->minimum_stock_level !== null && $materialRecord->current_stock <= $materialRecord->minimum_stock_level) {
                    \App\Services\NotificationService::notifyAdmins([
                        'type' => 'material',
                        'title' => 'Low Material Stock',
                        'message' => "Material '{$materialRecord->name}' stock is low (current: {$materialRecord->current_stock}).",
                        'data' => ['module' => 'admin.inventory', 'material_id' => $materialRecord->id, 'material_name' => $materialRecord->name, 'recipient' => 'Admin'],
                        'related_id' => $materialRecord->id,
                        'related_type' => 'material',
                    ]);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to notify admins on low stock: ' . $e->getMessage());
            }

            if (Schema::hasTable('material_deliveries')) {
                $notes = trim((string) ($validated['remarks'] ?? $validated['notes'] ?? '')) ?: null;

                MaterialDelivery::create([
                    'material_id' => $materialRecord->id,
                    'project_id' => null,
                    'quantity' => (float) $validated['quantity_received'],
                    'unit' => $materialRecord->unit,
                    'total_price' => null,
                    'supplier_name' => trim((string) ($validated['supplier'] ?? '')) ?: null,
                    'delivered_at' => $validated['received_date'],
                    'notes' => $notes,
                ]);
            }

            return redirect()->back()->with('success', 'Stock received successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['material_name' => $e->getMessage()])->withInput();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->with('error', 'Unable to receive stock right now. Please try again.')->withInput();
        }
    }

    public function destroyMaterial(Material $material)
    {
        try {
            $hasUsage = MaterialUsage::query()->where('material_id', $material->id)->exists();
            $hasDelivery = Schema::hasTable('material_deliveries')
                ? DB::table('material_deliveries')->where('material_id', $material->id)->exists()
                : false;

            if ($hasUsage || $hasDelivery) {
                return redirect()->back()->with('error', 'Unable to delete. This material has already been used in project records.');
            }

            Material::destroy($material->id);

            return redirect()->back()->with('success', 'Material deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to delete material right now. Please try again.');
        }
    }

    public function profile()
    {
        $user = Auth::user();

        return view('admin.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')],
            'contact_number' => ['nullable', 'string', 'max:20'],
        ]);

        if (Schema::hasColumn('users', 'name')) {
            $validated['name'] = trim(($validated['first_name'] ?? $user->first_name) . ' ' . ($validated['last_name'] ?? $user->last_name));
        }

        $user->fill($validated);

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
}