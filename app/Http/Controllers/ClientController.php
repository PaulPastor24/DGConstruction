<?php

namespace App\Http\Controllers;

use App\Models\ConstructionPhase;
use App\Models\Milestone;
use App\Models\Project;
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

        $selectedProjectId = $request->input('project_id');

        $allProjects = Project::query()
            ->where('client_id', '=', $client->client_id)
            ->with(['phases', 'engineer', 'supervisors'])
            ->orderBy('created_at', 'desc')
            ->get();

        $primaryProject = $selectedProjectId
            ? $allProjects->firstWhere('project_id', $selectedProjectId)
            : null;

        if (!$primaryProject) {
            $primaryProject = $allProjects->first();
        }

        $projects = $allProjects;
        $primaryProjectName = optional($primaryProject)->project_name ?? 'No Project Assigned';

        $totalProjects = $projects->count();
        $completedProjects = $projects->filter(fn($p) => $p->status === 'completed')->count();
        $ongoingProjects = $projects->filter(fn($p) => $p->status === 'ongoing')->count();

        $overallCompletion = $primaryProject
            ? round($primaryProject->phases->avg('completion_percentage') ?? 0, 2)
            : 0;

        $currentPhases = ConstructionPhase::query()
            ->when($primaryProject, function ($query) use ($primaryProject) {
                $query->where('project_id', $primaryProject->project_id);
            })
            ->where('status', 'in_progress')
            ->with('project')
            ->orderBy('created_at', 'desc')
            ->get();

        $delayedMilestones = Milestone::whereHas('phase', function ($q) use ($primaryProject) {
            if ($primaryProject) {
                $q->where('project_id', $primaryProject->project_id);
            }
        })->where('is_delayed', true)
            ->where('is_completed', false)
            ->with(['phase.project'])
            ->orderBy('planned_date')
            ->get();

        $upcomingMilestones = Milestone::whereHas('phase', function ($q) use ($primaryProject) {
            if ($primaryProject) {
                $q->where('project_id', $primaryProject->project_id);
            }
        })->where('is_completed', false)
            ->where('is_delayed', false)
            ->whereBetween('planned_date', [now(), now()->addDays(14)])
            ->with(['phase.project'])
            ->orderBy('planned_date')
            ->get();

        $recentReports = Report::query()
            ->when($primaryProject, function ($query) use ($primaryProject) {
                $query->where('project_id', $primaryProject->project_id);
            })
            ->with(['project', 'phase', 'submittedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

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
            'allProjects',
            'primaryProject',
            'primaryProjectName',
            'projectSummaries',
            'currentPhases',
            'delayedMilestones',
            'upcomingMilestones',
            'recentReports',
            'stats'
        ));
    }

    public function myProjects(Request $request)
    {
        $user = Auth::user();
        $client = $user?->client;

        if (!$client) {
            abort(403, 'User is not associated with a client account');
        }

        $query = Project::query()
            ->where('client_id', '=', $client->client_id)
            ->with(['phases', 'engineer', 'supervisors', 'reports'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($query) use ($search) {
                $query->where('project_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('phase')) {
            $query->whereHas('phases', function ($phaseQuery) use ($request) {
                $phaseQuery->where('phase_name', 'like', "%{$request->phase}%");
            });
        }

        if ($request->filled('completion')) {
            $completionFilter = $request->completion;

            if ($completionFilter === 'completed') {
                $query->whereHas('phases', function ($phaseQuery) {
                    $phaseQuery->where('completion_percentage', '>=', 95);
                });
            } elseif ($completionFilter === 'in_progress') {
                $query->whereHas('phases', function ($phaseQuery) {
                    $phaseQuery->where('completion_percentage', '<', 95)
                        ->where('status', 'in_progress');
                });
            } elseif ($completionFilter === 'at_risk') {
                $query->whereHas('phases', function ($phaseQuery) {
                    $phaseQuery->where('completion_percentage', '<', 70);
                });
            }
        }

        $projects = $query->paginate(6)->appends($request->only(['search', 'status', 'phase', 'completion']));

        $projectSummaries = $projects->getCollection()->map(function ($project) {
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

        $projects->setCollection($projectSummaries);

        $availablePhases = Project::query()
            ->where('client_id', '=', $client->client_id)
            ->with('phases')
            ->get()
            ->flatMap(fn($project) => $project->phases)
            ->pluck('phase_name')
            ->unique()
            ->sort()
            ->values();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('client.partials.project-gallery', compact('projects'))->render(),
                'total' => $projects->total(),
                'count' => $projects->count(),
            ]);
        }

        return view('client.myprojects', compact('projects', 'availablePhases'));
    }

    public function projectDetails(Project $project)
    {
        $user = Auth::user();
        $client = $user?->client;

        if (!$client || $project->client_id !== $client->client_id) {
            abort(403);
        }

        $project->load(['phases', 'engineer', 'activeSupervisor']);

        return view('client.project-details', compact('project'));
    }

    public function timeline(Request $request)
    {
        // Client timeline is now consolidated under TimelineController::clientTimeline.
        // Preserve this action as a redirect for any legacy references.
        return redirect()->route('client.timeline');
    }

    public function updates(Request $request)
    {
        $user = Auth::user();
        $projects = collect();
        $project = null;
        $updates = collect();

        if ($user && $user->client) {
            $projects = Project::query()
                ->where('client_id', '=', $user->client->client_id)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($request->filled('project_id')) {
                $project = $projects->firstWhere('project_id', $request->project_id);
            }

            if (!$project) {
                $project = $projects->first();
            }
        }

        if ($project) {
            $updates = Report::query()
                ->where('project_id', '=', $project->project_id)
                ->latest('report_date')
                ->get();
        }

        $reports = $updates;
        return view('client.report', compact('projects', 'project', 'reports'));
    }
}