<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ConstructionPhase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TimelineController extends Controller
{
    /**
     * Admin Timeline - View all projects
     */
    public function adminTimeline()
    {
        $projects = Project::with(['client.user', 'engineer', 'supervisors', 'phases'])
            ->orderBy('created_at', 'desc')
            ->get();

        $projectsWithStats = $projects->map(function ($project) {
            return $this->enrichProjectData($project);
        });

        return view('admin.timeline', compact('projectsWithStats'));
    }

    /**
     * Supervisor Timeline - View only assigned projects
     */
    public function supervisorTimeline()
    {
        $user = Auth::user();
        
        $projects = Project::with(['client.user', 'engineer', 'supervisors', 'phases'])
            ->whereHas('supervisors', function ($query) use ($user) {
                $query->where('project_supervisors.supervisor_id', $user->user_id)
                    ->where('project_supervisors.is_active', true);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $projectsWithStats = $projects->map(function ($project) {
            return $this->enrichProjectData($project);
        });

        return view('supervisor.timeline', compact('projectsWithStats'));
    }

    /**
     * Client Timeline - View only their assigned projects
     */
    public function clientTimeline()
    {
        $user = Auth::user();
        $client = $user->client;

        if (!$client) {
            return view('client.timeline', ['projectsWithStats' => collect()]);
        }

        $projects = Project::with(['client.user', 'engineer', 'supervisors', 'phases'])
            ->where('client_id', $client->client_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $projectsWithStats = $projects->map(function ($project) {
            return $this->enrichProjectData($project);
        });

        return view('client.timeline', compact('projectsWithStats'));
    }

    /**
     * Enrich project data with calculated stats
     */
    private function enrichProjectData($project)
    {
        $phases = $project->phases()->orderBy('phase_order', 'asc')->get();
        
        $overallProgress = 0;
        if ($phases->isNotEmpty()) {
            $overallProgress = round($phases->average('completion_percentage'), 1);
        }

        $currentPhase = $phases->where('status', 'in_progress')->first() 
            ?? $phases->where('status', 'not_started')->first()
            ?? $phases->first();

        // Map database status to display status
        $completedPhases = $phases->where('status', 'completed')->count();
        $inProgressPhases = $phases->where('status', 'in_progress')->count();
        $upcomingPhases = $phases->whereIn('status', ['not_started', 'delayed'])->count();

        // Enrich each phase with display status
        $phases = $phases->map(function ($phase) {
            $phase->display_status = match($phase->status) {
                'completed' => 'completed',
                'in_progress' => 'in-progress',
                'not_started', 'delayed' => 'planning',
                default => 'planning'
            };
            return $phase;
        });

        return [
            'id' => $project->project_id,
            'name' => $project->project_name,
            'location' => $project->project_location,
            'description' => $project->description,
            'status' => $project->status,
            'progress' => $overallProgress,
            'startDate' => $project->start_date,
            'targetEndDate' => $project->target_end_date,
            'actualEndDate' => $project->actual_end_date,
            'client' => $project->client,
            'engineer' => $project->engineer,
            'supervisors' => $project->supervisors,
            'phases' => $phases,
            'currentPhase' => $currentPhase,
            'completedPhases' => $completedPhases,
            'inProgressPhases' => $inProgressPhases,
            'upcomingPhases' => $upcomingPhases,
            'totalPhases' => $phases->count(),
        ];
    }
}
