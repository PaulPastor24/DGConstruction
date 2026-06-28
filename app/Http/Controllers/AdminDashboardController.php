<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $hasProjects = Schema::hasTable('projects');
        $hasReportsTable = Schema::hasTable('accomplishment_reports');
        $activeProjectsCount = $hasProjects
            ? DB::table('projects')->whereIn('status', ['planning', 'ongoing'])->count()
            : 0;
        $totalProjectsCount = $hasProjects ? DB::table('projects')->count() : 0;
        
        $executionRate = $totalProjectsCount > 0 
            ? round(($activeProjectsCount / $totalProjectsCount) * 100, 1) 
            : 0;

        $stats = [
            'active_projects' => $activeProjectsCount,
            'projects_change_label' => $totalProjectsCount . ' total registered projects',
            'on_track_projects' => $activeProjectsCount,
            'completion_rate_label' => "↑ {$executionRate}% execution rate",
            'total_workforce' => Schema::hasTable('users')
                ? DB::table('users')->where('role', '=', 'supervisor')->count() : 0,
            'pending_reports' => $hasReportsTable
                ? DB::table('accomplishment_reports')->count()
                : 0,
        ];

        $activeProjects = collect();
        if ($hasProjects) {
            $activeProjects = Project::with('client.user')
                ->whereIn('status', ['planning', 'ongoing'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($project) {
                    $progressPercentage = $project->progress_percentage ?? 0;
                    $color = 'blue';
                    if ($progressPercentage >= 80) $color = 'green';
                    if ($progressPercentage < 40) $color = 'orange';

                    return (object)[
                        'id' => $project->project_id,
                        'name' => $project->project_name ?? $project->name,
                        'location' => $project->project_location ?? $project->location,
                        'status_label' => ucfirst($project->status),
                        'current_phase' => $project->current_phase ?? 'Phase 1: Mobilization',
                        'progress_percentage' => $progressPercentage,
                        'progress_color_class' => $color,
                        'target_end_date' => $project->target_end_date
                    ];
                });
        }

        $today = Carbon::today();
        $attendance = ['present' => 0, 'absent' => 0, 'late' => 0, 'rate' => 0];

        if (Schema::hasTable('attendance_logs')) {
            $present = Attendance::query()->where('log_date', $today, '=')->where('status', 'present')->count();
            $absent  = Attendance::query()->where('log_date', $today, '=')->where('status', 'absent')->count();
            $late    = Attendance::query()->where('log_date', $today, '=')->where('status', 'half_day')->count();
            $totalExpected = $present + $absent + $late;
            
            $attendance = [
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'rate' => $totalExpected > 0 ? round(($present / $totalExpected) * 100) : 0
            ];
        }

        $burnRateData = $this->calculateMonthlyBurnRate();

        return view('admin.dashboard', compact('user', 'stats', 'activeProjects', 'attendance', 'burnRateData'));
    }

    private function calculateMonthlyBurnRate()
    {
        $months = [];
        $bars = [];

        for ($i = 4; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M');
            $monthlyCost = rand(15, 85); 
            
            $bars[] = [
                'percentage' => $monthlyCost, 
                'is_active' => ($i === 0)
            ];
        }

        return [
            'bars' => $bars,
            'months' => $months,
            'current_cost' => 2.4,
            'trend_up' => false,
            'variance_percentage' => 5
        ];
    }

    public function timeline(Request $request)
    {
        $hasProjects = Schema::hasTable('projects');
        $projects = $hasProjects ? Project::orderBy('project_name', 'asc')->get() : collect();

        $selectedProject = null;
        $phases = collect();
        $milestones = collect();
        $stats = ['phases_done' => 0, 'phases_processing' => 0, 'phases_upcoming' => 0];

        if ($request->has('project_id') && $hasProjects) {
            $selectedProject = Project::with('phases')->find($request->project_id);
            if ($selectedProject) {
                $selectedProject->id = $selectedProject->project_id;
                $selectedProject->name = $selectedProject->project_name;
                $phases = $selectedProject->phases->map(function($phase) {
                    $color = '#cbd5e1'; $statusText = 'Upcoming';
                    if ($phase->status === 'completed') { $color = '#22c55e'; $statusText = 'Completed'; }
                    elseif ($phase->status === 'in_progress') { $color = '#eab308'; $statusText = 'In Progress'; }
                    return (object)[
                        'title' => $phase->phase_name, 'start_date' => $phase->start_date, 'end_date' => $phase->end_date,
                        'color_code' => $color, 'is_current' => ($phase->status === 'in_progress'),
                        'status_note' => $phase->description ?? 'Phase operations verified',
                        'progress_percentage' => $phase->completion_percentage ?? 0, 'status_text' => $statusText
                    ];
                });
                $stats = [
                    'phases_done' => $selectedProject->phases->where('status', 'completed')->count(),
                    'phases_processing' => $selectedProject->phases->where('status', 'in_progress')->count(),
                    'phases_upcoming' => $selectedProject->phases->where('status', 'pending')->count(),
                ];
                $milestones = collect([
                    (object)['type' => 'info', 'type_label' => 'MOBILIZATION', 'title' => 'Site Operations Initialized', 'description' => 'Equipment arrival and safety perimeter configurations cleared.', 'logged_at' => $selectedProject->start_date ?? Carbon::now()],
                    (object)['type' => 'warning', 'type_label' => 'TARGET DEADLINE', 'title' => 'Structural Hand-over Target', 'description' => 'Core shell completion targeted baseline estimation.', 'logged_at' => $selectedProject->target_end_date ?? Carbon::now()->addMonths(3)]
                ]);
            }
        }
        return view('admin.timeline', compact('projects', 'selectedProject', 'phases', 'milestones', 'stats'));
    }

    public function inventory(Request $request)
    {
        $projectId = $request->input('project_id');
        
        // 1. Get Projects for the Filter Dropdown
        $projects = Project::orderBy('project_name', 'asc')->get();

        // 2. Get Available Materials
        $availableMaterials = Schema::hasTable('materials')
            ? \App\Models\Material::orderBy('name', 'asc')->get()
            : collect();

        // 3. Initialize Variables
        $inventoryItems = collect();
        $metrics = ['active_deliveries' => 0, 'low_stock_alerts' => 0, 'total_value' => 0.00];

        // 4. Fetch and filter data if table exists
        if (Schema::hasTable('material_deliveries')) {
            $query = DB::table('material_deliveries')->orderBy('delivered_at', 'desc');

            if ($projectId) {
                $query->where('project_id', $projectId);
            }

            $inventoryItems = $query->get();

            $metrics['active_deliveries'] = $inventoryItems->count();

            if (Schema::hasColumn('material_deliveries', 'total_price')) {
                $metrics['total_value'] = $inventoryItems->sum('total_price');
            }
        }

        $haulingTrips = collect(); 
        $locations = collect();

        return view('admin.inventory', compact('metrics', 'inventoryItems', 'availableMaterials', 'haulingTrips', 'locations', 'projects'));
    }

    /**
     * Display the admin reports layout interface.
     */
    public function reports(Request $request)
    {
        $queueCount = [
            'awaiting_review' => 0,
            'in_review' => 0,
            'approved' => 0,
            'needs_revision' => 0,
        ];

        $submissions = collect();
        $selectedReport = null;

        if (Schema::hasTable('accomplishment_reports')) {
            $reportColumns = Schema::getColumnListing('accomplishment_reports');
            $hasApprovalStatus = in_array('approval_status', $reportColumns, true);
            $hasStatus = in_array('status', $reportColumns, true);

            $reportsQuery = DB::table('accomplishment_reports as ar')
                ->leftJoin('projects as p', 'p.project_id', '=', 'ar.project_id')
                ->leftJoin('users as u', 'u.user_id', '=', 'ar.submitted_by')
                ->select(
                    'ar.report_id as id',
                    'p.project_name',
                    'u.name as supervisor_name',
                    'u.name as supervisor_fullname',
                    'ar.created_at as submitted_at',
                    'ar.report_text as notes_summary',
                    'ar.project_id',
                    'ar.phase_id'
                );

            if ($hasApprovalStatus) {
                $reportsQuery->addSelect(DB::raw('ar.approval_status as status'));
            } elseif ($hasStatus) {
                $reportsQuery->addSelect(DB::raw('ar.status as status'));
            } else {
                $reportsQuery->addSelect(DB::raw('NULL as status'));
            }

            $reportsQuery->orderBy('ar.created_at', 'desc');
            $submissions = $reportsQuery->get()->map(function ($submission) {
                $submission->status = $submission->status ?? 'pending';
                $submission->phase_name = $submission->phase_id ? 'Phase ' . $submission->phase_id : 'General';
                $submission->supervisor_fullname = $submission->supervisor_fullname ?? $submission->supervisor_name;
                $submission->period_range = 'N/A';
                $submission->completion_percentage = 0;
                $submission->attachments = [];

                return $submission;
            });

            $queueCount['awaiting_review'] = $submissions->where('status', 'pending')->count();
            $queueCount['in_review'] = $submissions->whereIn('status', ['reviewed', 'in_review'])->count();
            $queueCount['approved'] = $submissions->where('status', 'approved')->count();
            $queueCount['needs_revision'] = $submissions->whereIn('status', ['rejected', 'needs_revision'])->count();

            if ($request->filled('report_id')) {
                $selectedReport = $submissions->firstWhere('id', $request->report_id);
            }
        }

        return view('admin.reports', compact('queueCount', 'submissions', 'selectedReport'));
    }

    /**
     * Display the attendance monitoring records dashboard.
     */
    public function attendance()
    {
        $attendance = collect();

        if (Schema::hasTable('attendance_logs')) {
            $attendance = Attendance::query()
                ->with('user')
                ->orderBy('log_date', 'desc')
                ->take(20)
                ->get();
        }

        return view('admin.attendance', compact('attendance'));
    }

    public function updateSettings(Request $request)
    {
        return redirect()->back()->with('success', 'Notification settings updated.');
    }

    /**
     * Store an incoming material delivery request log
     */
    public function storeDelivery(Request $request)
    {
        $request->validate([
            'material_id'   => 'required',
            'quantity'      => 'required|numeric|min:1',
            'unit'          => 'required|string',
            'supplier_name' => 'required|string|max:255',
        ]);

        $data = [
            'material_id' => $request->input('material_id'),
            'quantity' => $request->input('quantity'),
            'unit' => $request->input('unit'),
            'supplier_name' => $request->input('supplier_name'),
            'delivered_at' => now(),
        ];

        if ($request->filled('project_id')) {
            $data['project_id'] = $request->input('project_id');
        }

        // Use DB insert to avoid model PK assumptions
        if (Schema::hasTable('material_deliveries')) {
            DB::table('material_deliveries')->insert($data);
        }

        return redirect()->back()->with('success', 'Material transaction asset processed successfully.');
    }

    public function alerts()
    {
        return view('admin.alerts');
    }
}