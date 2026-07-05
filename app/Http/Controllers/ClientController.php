<?php

namespace App\Http\Controllers;

use App\Models\ClientNotification;
use App\Models\ConstructionPhase;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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

        // Recent reports: if the user explicitly selected a project, filter to it;
        // otherwise show recent reports across all projects for this client.
        $recentReports = Report::query()
            ->when($selectedProjectId, function ($query) use ($selectedProjectId) {
                $query->where('project_id', $selectedProjectId);
            })
            ->whereHas('project', function ($q) use ($client) {
                $q->where('client_id', $client->client_id);
            })
            ->with(['project', 'phase', 'submittedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Other recent activity sources
        $recentNotifications = ClientNotification::query()
            ->where('client_id', $client->client_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentMilestones = Milestone::query()
            ->whereHas('project', function ($q) use ($client) {
                $q->where('client_id', $client->client_id);
            })
            ->with(['phase', 'project'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        $recentDeliveries = [];
        if (Schema::hasTable('material_deliveries')) {
            $recentDeliveries = \App\Models\MaterialDelivery::query()
                ->whereHas('project', function ($q) use ($client) {
                    $q->where('client_id', $client->client_id);
                })
                ->with('project')
                ->orderBy('delivered_at', 'desc')
                ->limit(5)
                ->get();
        }

        // Merge into unified activity feed
        $activityCollection = collect();

        foreach ($recentReports as $r) {
            $activityCollection->push([
                'type' => 'report',
                'time' => $r->report_date ?? $r->created_at,
                'title' => optional($r->phase)->phase_name ? ('Report: ' . optional($r->phase)->phase_name) : 'Project report submitted',
                'subtitle' => 
                    (strlen($r->report_text ?? '') > 0) ? Str::limit($r->report_text, 80) : 'Report available',
                'author' => optional($r->submittedBy)->name ?? 'Project team',
                'icon' => 'bi bi-file-earmark-text',
                'variant' => 'bg-light-green text-dark',
                'raw' => $r,
            ]);
        }

        foreach ($recentNotifications as $n) {
            $activityCollection->push([
                'type' => 'notification',
                'time' => $n->created_at,
                'title' => $n->title ?? 'Notification',
                'subtitle' => Str::limit($n->message ?? '', 80),
                'author' => 'System',
                'icon' => 'bi bi-bell',
                'variant' => 'bg-light-blue text-dark',
                'raw' => $n,
            ]);
        }

        foreach ($recentMilestones as $m) {
            $status = $m->is_completed ? 'Completed' : ($m->is_delayed ? 'Delayed' : 'Planned');
            $activityCollection->push([
                'type' => 'milestone',
                'time' => $m->updated_at ?? $m->created_at,
                'title' => "Milestone: {$m->milestone_name}",
                'subtitle' => "Status: {$status}",
                'author' => optional($m->project)->project_name ?? 'Project',
                'icon' => 'bi bi-flag',
                'variant' => 'bg-light-yellow text-dark',
                'raw' => $m,
            ]);
        }

        foreach ($recentDeliveries as $d) {
            $activityCollection->push([
                'type' => 'delivery',
                'time' => $d->delivered_at ?? $d->created_at ?? now(),
                'title' => 'Material Delivery',
                'subtitle' => optional($d->material)->name ? (optional($d->material)->name . " ({$d->quantity} {$d->unit})") : 'Delivery recorded',
                'author' => optional($d->project)->project_name ?? 'Project',
                'icon' => 'bi bi-truck',
                'variant' => 'bg-light-gray text-dark',
                'raw' => $d,
            ]);
        }

        $activityItems = $activityCollection->sortByDesc(function ($it) {
            return $it['time'] ? strtotime((string)$it['time']) : 0;
        })->values()->take(5);

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
            'activityItems',
            'stats'
        ));
    }

    public function notifications(Request $request)
    {
        $user = Auth::user();
        $client = $user?->client;

        if (!$client) {
            abort(403, 'User is not associated with a client account');
        }

        $totalNotifs = ClientNotification::query()
            ->where('client_id', $client->client_id)
            ->count('*');

        $unreadCount = ClientNotification::query()
            ->where('client_id', $client->client_id)
            ->where('is_read', false)
            ->count('*');

        $readCount = ClientNotification::query()
            ->where('client_id', $client->client_id)
            ->where('is_read', true)
            ->count('*');

        $query = ClientNotification::query()
            ->where('client_id', $client->client_id)
            ->orderBy('created_at', 'desc');
        $type = strtolower($request->query('type', 'all'));

        if ($type !== 'all') {
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
                case 'timeline':
                case 'announcement':
                case 'system':
                    $query->where('type', $type);
                    break;
                default:
                    break;
            }
        }

        $notifications = $query->paginate(10)->withQueryString();

        return view('client.notifications', compact('notifications', 'totalNotifs', 'unreadCount', 'readCount'));
    }

    public function markNotificationRead($id)
    {
        $user = Auth::user();
        $client = $user?->client;

        if (!$client) {
            return response()->json(['success' => false, 'message' => 'Client account not found'], 404);
        }

        $notification = ClientNotification::query()
            ->where('id', $id)
            ->where('client_id', $client->client_id)
            ->firstOrFail();

        $notification->is_read = true;
        $notification->read_at = now();
        $notification->save();

        return response()->json(['success' => true]);
    }

    public function markNotificationReadAndRedirect(Request $request, $id)
    {
        $user = Auth::user();
        $client = $user?->client;

        if (!$client) {
            return redirect()->route('client.notifications');
        }

        $notification = ClientNotification::query()
            ->where('id', $id)
            ->where('client_id', $client->client_id)
            ->first();

        if ($notification) {
            $notification->is_read = true;
            $notification->read_at = now();
            $notification->save();
        }

        $redirect = $request->query('redirect');
        if ($redirect && URL::isValidUrl($redirect) && Str::startsWith($redirect, url('/'))) {
            return redirect()->to($redirect);
        }

        return redirect()->route('client.notifications');
    }

    public function markAllNotificationsRead()
    {
        $user = Auth::user();
        $client = $user?->client;

        if (!$client) {
            return response()->json(['success' => false, 'message' => 'Client account not found'], 404);
        }

        $notifications = ClientNotification::query()
            ->where('client_id', $client->client_id)
            ->where('is_read', false)
            ->get();

        foreach ($notifications as $notification) {
            $notification->is_read = true;
            $notification->read_at = now();
            $notification->save();
        }

        return response()->json(['success' => true]);
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
                'pagination' => view('vendor.pagination.bootstrap-5', ['paginator' => $projects])->render(),
                'total' => $projects->total(),
                'count' => $projects->count(),
                'from' => $projects->firstItem(),
                'to' => $projects->lastItem(),
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
        $selectedProject = null;
        $projectPhases = collect();

        if ($user && $user->client) {
            $projects = Project::query()
                ->where('client_id', '=', $user->client->client_id)
                ->orderBy('created_at', 'desc')
                ->get();

            $requestedProjectId = $request->filled('project_id') ? (int) $request->project_id : null;
            if ($requestedProjectId && $projects->contains('project_id', $requestedProjectId)) {
                $selectedProject = $projects->firstWhere('project_id', $requestedProjectId);
            }
        }

        $assignedProjectIds = $projects->pluck('project_id')->all();

        $reportsQuery = Report::query()
            ->with(['project', 'phase', 'submittedBy'])
            ->whereIn('project_id', $assignedProjectIds)
            ->when($selectedProject, function ($query) use ($selectedProject) {
                $query->where('project_id', $selectedProject->project_id);
            })
            ->when($request->filled('phase_id'), function ($query) use ($request) {
                $query->where('phase_id', $request->phase_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('approval_status', $request->status);
            })
            ->when($request->filled('report_date'), function ($query) use ($request) {
                $query->whereDate('report_date', $request->report_date);
            });

        $reports = $reportsQuery->latest('report_date')->paginate(10)->appends($request->only(['project_id', 'phase_id', 'status', 'report_date']));

        if ($selectedProject) {
            $projectPhases = $selectedProject->phases()->orderBy('phase_order')->get();
        } else {
            $projectPhases = $projects->flatMap(fn ($project) => $project->phases)->sortBy('phase_order')->values();
        }

        $stats = [
            'total' => (clone $reportsQuery)->count(),
            'pending' => (clone $reportsQuery)->where('approval_status', 'pending')->count(),
            'approved' => (clone $reportsQuery)->where('approval_status', 'approved')->count(),
            'rejected' => (clone $reportsQuery)->where('approval_status', 'rejected')->count(),
        ];

        return view('client.report', compact('projects', 'selectedProject', 'projectPhases', 'reports', 'stats'));
    }

    /**
     * Download report as PDF for client users
     */
    public function downloadReportPdf($reportId)
    {
        $user = auth('web')->user();
        $client = $user?->client;

        if (!$client) {
            abort(403, 'Client account not found');
        }

        $report = Report::with(['project', 'phase', 'submittedBy', 'reviewedBy', 'approvedBy'])
            ->where('report_id', $reportId)
            ->whereHas('project', function ($q) use ($client) {
                $q->where('client_id', $client->client_id);
            })->firstOrFail();

        // Reuse the supervisor PDF view if a client-specific PDF template is not present
        $viewName = view()->exists('client.reports.pdf') ? 'client.reports.pdf' : 'supervisor.reports.pdf';
        $html = view($viewName, compact('report'))->render();

        try {
            if (class_exists('\\Mpdf\\Mpdf')) {
                $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 10, 'margin_bottom' => 10]);
                $mpdf->WriteHTML($html);
                $fileName = 'report_' . $report->report_id . '_' . date('Y-m-d') . '.pdf';
                return $mpdf->Output($fileName, 'D');
            }
        } catch (\Throwable $e) {
            Log::error('PDF generation failed (client): ' . $e->getMessage());
        }

        $fileName = 'report_' . $report->report_id . '_' . date('Y-m-d') . '.html';
        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}