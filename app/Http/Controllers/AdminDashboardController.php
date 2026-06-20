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
        $activeProjectsCount = $hasProjects ? Project::query()->where('status', 'ongoing')->count() : 0;
        $totalProjectsCount  = $hasProjects ? Project::query()->count() : 0;
        
        $executionRate = $totalProjectsCount > 0 
            ? round(($activeProjectsCount / $totalProjectsCount) * 100, 1) 
            : 0;

        $stats = [
            'active_projects' => $activeProjectsCount,
            'projects_change_label' => 'Updated live',
            'on_track_projects' => $activeProjectsCount,
            'completion_rate_label' => "↑ {$executionRate}% execution rate",
        'total_workforce'  => Schema::hasTable('users')
            ? User::query()->where('role', 'site_supervisor')->count() : 0,
        'pending_reports'  => Schema::hasTable('accomplishment_reports')
            ? Report::query()->where('ai_status', 'pending')->count() : 0,
        ];

        // 2. Active Projects Collection
        $activeProjects = collect();
        if ($hasProjects) {
            $activeProjects = Project::query()->where('status', 'ongoing')
                ->orderBy('target_end_date', 'asc')
                ->take(5)
                ->get()
                ->map(function ($project) {
                    $progressPercentage = $project->progress_percentage ?? 0;
                    $color = 'blue';
                    if ($progressPercentage >= 80) $color = 'green';
                    if ($progressPercentage < 40) $color = 'orange';

                    return (object)[
                        'id' => $project->project_id,
                        'name' => $project->project_name,
                        'location' => $project->project_location,
                        'status_label' => ucfirst($project->status),
                        'current_phase' => $project->current_phase ?? 'Phase 1: Mobilization',
                        'progress_percentage' => $progressPercentage,
                        'progress_color_class' => $color,
                        'target_end_date' => $project->target_end_date
                    ];
                });
        }

        // 3. Dynamic Daily Attendance Tracking
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
