<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
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

        // 1. Core Metrics Calculations (with existence checks)
        $hasProjects = Schema::hasTable('projects');
        $activeProjectsCount = $hasProjects ? Project::where('status', '=', 'On-Track')->count() : 0;
        $totalProjectsCount = $hasProjects ? Project::count() : 0;
        
        $executionRate = $totalProjectsCount > 0 
            ? round(($activeProjectsCount / $totalProjectsCount) * 100, 1) 
            : 0;

        $stats = [
            'active_projects' => $activeProjectsCount,
            'projects_change_label' => 'Updated live',
            'on_track_projects' => $activeProjectsCount,
            'completion_rate_label' => "↑ {$executionRate}% execution rate",
            'total_workforce' => Schema::hasTable('users') ? User::whereIn('role', ['worker', 'site_supervisor'])->count() : 0,
            'pending_reports' => Schema::hasTable('reports') ? Report::where('status', '=', 'Awaiting Review')->count() : 0,
        ];

        // 2. Active Projects Collection
        $activeProjects = $hasProjects ? Project::where('status', '=', 'On-Track')
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get()
            ->map(function ($project) {
                $color = 'blue';
                if ($project->progress_percentage >= 80) $color = 'green';
                if ($project->progress_percentage < 40) $color = 'orange';

                return (object)[
                    'id' => $project->id,
                    'name' => $project->name,
                    'location' => $project->location,
                    'status_label' => $project->status,
                    'current_phase' => $project->current_phase ?? 'Phase 1: Mobilization',
                    'progress_percentage' => $project->progress_percentage,
                    'progress_color_class' => $color,
                    'due_date' => $project->due_date
                ];
            }) : collect();

        // 3. Dynamic Daily Attendance Tracking
        $today = Carbon::today();
        $attendance = ['present' => 0, 'absent' => 0, 'late' => 0, 'rate' => 0];

        if (Schema::hasTable('attendances')) {
            $present = Attendance::whereDate('date', $today)->where('status', '=', 'Present')->count();
            $absent = Attendance::whereDate('date', $today)->where('status', '=', 'Absent')->count();
            $late = Attendance::whereDate('date', $today)->where('status', '=', 'Late')->count();
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
        $currentMonthCost = 0;

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

    // ==================== OPERATIONAL VIEW TARGET LINK WORKSPACES ====================

    public function timeline()
    {
        return view('admin.timeline');
    }

    public function reports()
    {
        return view('admin.reports');
    }

    public function phases()
    {
        return view('admin.phases');
    }

    public function attendance()
    {
        return view('admin.attendance');
    }

    public function inventory()
    {
        return view('admin.inventory');
    }

    public function alerts()
    {
        return view('admin.alerts');
    }
}