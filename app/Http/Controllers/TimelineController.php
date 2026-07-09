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
    public function clientTimeline(Request $request)
    {
        $user = Auth::user();
        $client = $user->client;

        if (!$client) {
            return view('client.timeline', ['projectsWithStats' => collect(), 'allProjects' => collect(), 'selectedProjectId' => null]);
        }

        $allProjects = Project::with(['client.user', 'engineer', 'supervisors', 'phases'])
            ->where('client_id', $client->client_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $selectedProjectId = $request->query('project_id');
        $selectedProject = null;

        if ($selectedProjectId) {
            $selectedProject = $allProjects->firstWhere('project_id', $selectedProjectId);
        }

        $projects = $selectedProject ? collect([$selectedProject]) : $allProjects;

        $projectsWithStats = $projects->map(function ($project) {
            return $this->enrichProjectData($project);
        });

        return view('client.timeline', compact('projectsWithStats', 'allProjects', 'selectedProjectId'));
    }

    /**
     * Return refreshed project timeline data for the admin UI.
     */
    public function timelineData(Request $request, Project $project)
    {
        $project = Project::with(['client.user', 'engineer', 'supervisors', 'phases'])
            ->where('project_id', $project->project_id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'project' => $this->enrichProjectData($project),
        ]);
    }

    /**
     * Enrich project data with calculated stats
     */
    private function enrichProjectData($project)
    {
        $phases = $project->phases()->with(['milestones'])->withCount('milestones')->orderBy('phase_order', 'asc')->get();
        
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

        // Enrich each phase with display status and database-backed fields for the timeline UI
        $phases = $phases->map(function ($phase) {
            $phase->display_status = match($phase->status) {
                'completed' => 'completed',
                'in_progress' => 'in-progress',
                'not_started', 'delayed' => 'planning',
                default => 'planning'
            };
            $phase->name = $phase->phase_name;
            $phase->phase_code = 'P' . str_pad((string) ($phase->phase_order ?? 1), 2, '0', STR_PAD_LEFT);
            $phase->start = $phase->planned_start_date?->toDateString();
            $phase->end = $phase->planned_end_date?->toDateString();
            $phase->progress = (float) ($phase->completion_percentage ?? 0);
            $phase->duration_days = $this->calculateDurationDays($phase->planned_start_date, $phase->planned_end_date);
            $phase->milestone_count = (int) ($phase->milestones_count ?? 0);

            return $phase;
        });

        $milestones = $phases->flatMap(function ($phase) {
            return $phase->milestones->map(function ($milestone) use ($phase) {
                return [
                    'milestone_id' => $milestone->milestone_id,
                    'phase_id' => $milestone->phase_id,
                    'milestone_name' => $milestone->milestone_name,
                    'start_date' => $milestone->start_date?->toDateString(),
                    // Prefer the canonical `end_date`, but fall back to legacy fields if present
                    'end_date' => $milestone->end_date?->toDateString()
                        ?: (data_get($milestone, 'actual_date') ? Carbon::parse(data_get($milestone, 'actual_date'))->toDateString() : (data_get($milestone, 'actual_end_date') ? Carbon::parse(data_get($milestone, 'actual_end_date'))->toDateString() : null)),
                    'planned_start_date' => $phase->planned_start_date?->toDateString(),
                    'planned_end_date' => $phase->planned_end_date?->toDateString(),
                    'is_completed' => (bool) $milestone->is_completed,
                    'is_delayed' => (bool) $milestone->is_delayed,
                    'status' => $milestone->is_completed ? 'completed' : ($milestone->is_delayed ? 'delayed' : 'upcoming'),
                    'phase_name' => $phase->phase_name,
                    'phase_code' => $phase->phase_code,
                ];
            });
        })->values();

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
            'milestones' => $milestones,
            'currentPhase' => $currentPhase,
            'completedPhases' => $completedPhases,
            'inProgressPhases' => $inProgressPhases,
            'upcomingPhases' => $upcomingPhases,
            'totalPhases' => $phases->count(),
        ];
    }

    private function calculateDurationDays($startDate, $endDate)
    {
        if (!$startDate || !$endDate) {
            return 0;
        }

        try {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            return max(1, $start->diffInDays($end) + 1);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
