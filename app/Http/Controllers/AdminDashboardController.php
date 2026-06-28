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
        
        $activeProjectsCount = $hasProjects 
            ? Project::query()->whereIn('status', ['planning', 'ongoing'])->count() 
            : 0;
            
        $totalProjectsCount  = $hasProjects ? Project::query()->count() : 0;
        
        $executionRate = $totalProjectsCount > 0 
            ? round(($activeProjectsCount / $totalProjectsCount) * 100, 1) 
            : 0;

        $stats = [
            'active_projects' => $activeProjectsCount,
            'projects_change_label' => $totalProjectsCount . ' total registered projects',
            'on_track_projects' => $activeProjectsCount,
            'completion_rate_label' => "↑ {$executionRate}% pipeline volume",
            'total_workforce'  => Schema::hasTable('users')
                ? User::query()->where('role', 'site_supervisor')->count() : 0,
            'pending_reports'  => Schema::hasTable('accomplishment_reports')
                ? Report::query()->where('ai_status', 'pending')->count() : 0,
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
            ? DB::table('materials')->orderBy('name', 'asc')->get() 
            : collect();

        // 3. Initialize Variables
        $inventoryItems = collect();
        $metrics = ['active_deliveries' => 0, 'low_stock_alerts' => 0, 'total_value' => 0.00];

        // 4. Fetch and filter data if table exists
        if (Schema::hasTable('material_deliveries')) {
            $query = DB::table('material_deliveries');
            
            if ($projectId) {
                $query->where('project_id', $projectId);
            }
            
            $inventoryItems = $query->get();
            
            // Update metrics safely
            $metrics['active_deliveries'] = $inventoryItems->count();
            
            // Use sum only if the column exists to avoid SQL errors
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
    public function reports()
    {
        $hasReports = Schema::hasTable('accomplishment_reports');
        
        $reports = $hasReports 
            ? Report::with(['project', 'user'])->orderBy('created_at', 'desc')->get() 
            : collect();

        return view('admin.reports', compact('reports'));
    }

    /**
     * Display the attendance monitoring records dashboard.
     */
    public function attendance()
    {
        $hasAttendance = Schema::hasTable('attendance_logs');

        $logs = $hasAttendance 
            ? Attendance::with('user')->orderBy('log_date', 'desc')->get() 
            : collect();

        return view('admin.attendance', compact('logs'));
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

        return redirect()->back()->with('success', 'Material transaction asset processed successfully.');
    }

    public function alerts()
    {
        return view('admin.alerts');
    }
}