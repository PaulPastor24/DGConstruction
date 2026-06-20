<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ConstructionPhase;
use App\Models\Milestone;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $client = $user->client;

        if (!$client) {
            abort(403, 'User is not associated with a client account');
        }

        // Get all projects for this client
        $projects = Project::query()
            ->where('client_id', $client->client_id)
            ->with(['phases', 'engineer', 'supervisors'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate overall project metrics
        $totalProjects = $projects->count();
        $completedProjects = $projects->filter(fn($p) => $p->status === 'completed')->count();
        $ongoingProjects = $projects->filter(fn($p) => $p->status === 'ongoing')->count();

        // Calculate overall completion percentage (weighted average)
        $overallCompletion = $projects->isEmpty() ? 0 : round(
            $projects->flatMap(fn($p) => $p->phases)->avg('completion_percentage') ?? 0,
            2
        );

        // Get current phases across all projects
        $projectIds = $projects->pluck('project_id')->all();

        $currentPhases = ConstructionPhase::query()
            ->where(function ($query) use ($projectIds) {
                foreach ($projectIds as $projectId) {
                    $query->orWhere('project_id', $projectId);
                }
            })
            ->where('status', 'in_progress')
            ->with('project')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get delayed milestones
        $delayedMilestones = Milestone::whereHas('phase', function ($q) use ($projects) {
            $q->whereIn('project_id', $projects->pluck('project_id'));
        })->where('is_delayed', true)
            ->where('is_completed', false)
            ->with(['phase.project'])
            ->orderBy('planned_date')
            ->get();

        // Get upcoming milestones (next 14 days)
        $upcomingMilestones = Milestone::whereHas('phase', function ($q) use ($projects) {
            $q->whereIn('project_id', $projects->pluck('project_id'));
        })->where('is_completed', false)
            ->where('is_delayed', false)
            ->whereBetween('planned_date', [now(), now()->addDays(14)])
            ->with(['phase.project'])
            ->orderBy('planned_date')
            ->get();

        // Get recent accomplishment reports from the project's phases
        $recentReports = Report::query()
            ->where(function ($query) use ($projectIds) {
                foreach ($projectIds as $projectId) {
                    $query->orWhere('project_id', $projectId);
                }
            })
            ->with(['project', 'phase', 'submittedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get projects with summary data
        $projectSummaries = $projects->map(function ($project) {
            $phases = $project->phases;
            $completedPhases = $phases->filter(fn($p) => $p->status === 'completed')->count();
            
            return [
                'project' => $project,
                'total_phases' => $phases->count(),
                'completed_phases' => $completedPhases,
                'current_phase' => $phases->firstWhere('status', 'in_progress'),
                'completion' => round($phases->avg('completion_percentage') ?? 0, 2),
            ];
        })->sortByDesc('completion');

        $stats = [
            'total_projects' => $totalProjects,
            'completed_projects' => $completedProjects,
            'ongoing_projects' => $ongoingProjects,
            'overall_completion' => $overallCompletion,
            'delayed_milestones_count' => $delayedMilestones->count(),
            'upcoming_milestones_count' => $upcomingMilestones->count(),
            'recent_updates_count' => $recentReports->count(),
        ];

        return view('client.dashboard', compact(
            'user',
            'client',
            'projects',
            'projectSummaries',
            'currentPhases',
            'delayedMilestones',
            'upcomingMilestones',
            'recentReports',
            'stats'
        ));
    }
}