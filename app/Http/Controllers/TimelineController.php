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

        // Honour an explicit ?project_id (e.g. carried over from the Dashboard) but
        // always RENDER every assigned project so the timeline's project dropdown can
        // switch between them. Previously we filtered the collection to a single
        // project, which made every other project vanish from the selector.
        $selectedProjectId = $request->query('project_id') ?? session('client_selected_project_id');

        $projectsWithStats = $allProjects->map(function ($project) {
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
            $overallProgress = round($phases->map(function ($phase) {
                return $this->normalizeCompletionPercentage($phase->completion_percentage ?? 0);
            })->average(), 1);
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
            $phase->progress = $this->normalizeCompletionPercentage($phase->completion_percentage ?? 0);
            $phase->duration_days = $this->calculateDurationDays($phase->planned_start_date, $phase->planned_end_date);
            $phase->milestone_count = (int) ($phase->milestones_count ?? 0);

            return $phase;
        });

        $projectStart = $project->start_date ? Carbon::parse($project->start_date) : null;
        $projectEnd = $project->target_end_date ? Carbon::parse($project->target_end_date) : null;

        // The Workflow Progress bar is a single project-wide timeline, so flags are
        // positioned against the project's start -> target-end span. If those top-level
        // dates are missing, fall back to the earliest phase start / latest phase end
        // so the milestones still land on a meaningful timeline.
        if (!$projectStart || !$projectEnd || !$projectEnd->gt($projectStart)) {
            $phaseStarts = $phases->pluck('planned_start_date')->filter();
            $phaseEnds = $phases->pluck('planned_end_date')->filter();
            if ($phaseStarts->isNotEmpty() && $phaseEnds->isNotEmpty()) {
                $projectStart = Carbon::parse($phaseStarts->min());
                $projectEnd = Carbon::parse($phaseEnds->max());
            }
        }

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

        // Position every milestone as a flag along the overall Workflow Progress bar.
        // The percent is date-driven: how far the milestone's planned start sits between
        // the project's start and target end dates - mirroring the supervisor timeline's
        // phase-progress flag approach, but scoped to the whole project instead of a phase.
        $milestones = $milestones->map(function ($milestone) use ($projectStart, $projectEnd) {
            $markerPercent = 0;
            $milestoneDate = !empty($milestone['start_date']) ? Carbon::parse($milestone['start_date']) : null;

            if ($projectStart && $projectEnd && $milestoneDate && $projectEnd->gt($projectStart)) {
                $totalDays = max(1, $projectStart->diffInDays($projectEnd, false));
                $elapsedDays = $projectStart->diffInDays($milestoneDate, false);
                $markerPercent = max(0, min(100, round(($elapsedDays / $totalDays) * 100, 1)));
            }

            $milestone['marker_percent'] = $markerPercent;

            return $milestone;
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
            'milestones' => $milestones,
            'currentPhase' => $currentPhase,
            'completedPhases' => $completedPhases,
            'inProgressPhases' => $inProgressPhases,
            'upcomingPhases' => $upcomingPhases,
            'totalPhases' => $phases->count(),
        ];
    }

    private function normalizeCompletionPercentage($value)
    {
        $percentage = (float) $value;

        if (!is_finite($percentage)) {
            return 0.0;
        }

        if ($percentage <= 1) {
            return $percentage * 100;
        }

        return min(100.0, max(0.0, $percentage));
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
