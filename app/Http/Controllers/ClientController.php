<?php

namespace App\Http\Controllers;

use App\Models\ClientNotification;
use App\Models\ConstructionPhase;
use App\Models\MaterialDelivery;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $client = $user->client;

        if (! $client) {
            abort(403, 'User is not associated with a client account');
        }

        // Honour an explicit ?project_id first, then fall back to the project the client
        // last selected (stored in the session) so navigating away and back to the
        // dashboard keeps the previously chosen project instead of reverting to the latest.
        $selectedProjectId = $request->input('project_id') ?? session('client_selected_project_id');

        $allProjects = Project::query()
            ->where('client_id', '=', $client->client_id)
            ->with(['phases', 'engineer', 'supervisors'])
            ->orderBy('created_at', 'desc')
            ->get();

        $primaryProject = $selectedProjectId
            ? $allProjects->firstWhere('project_id', $selectedProjectId)
            : null;

        if (! $primaryProject) {
            $primaryProject = $allProjects->first();
        }

        // Remember the chosen project for future visits (the client JS also mirrors it to
        // localStorage for an instant, no-flash restore on the next dashboard load).
        if ($request->filled('project_id') && $selectedProjectId) {
            session(['client_selected_project_id' => $selectedProjectId]);
        }

        $projects = $allProjects;
        $primaryProjectName = optional($primaryProject)->project_name ?? 'No Project Assigned';

        $totalProjects = $projects->count();
        $completedProjects = $projects->filter(fn ($p) => $p->status === 'completed')->count();
        $ongoingProjects = $projects->filter(fn ($p) => $p->status === 'ongoing')->count();

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
            ->orderBy('start_date')
            ->get();

        $upcomingMilestones = Milestone::whereHas('phase', function ($q) use ($primaryProject) {
            if ($primaryProject) {
                $q->where('project_id', $primaryProject->project_id);
            }
        })->where('is_completed', false)
            ->where('is_delayed', false)
            ->whereDate('start_date', '>=', now())
            ->with(['phase.project'])
            ->orderBy('start_date')
            ->get();

        // If there is no milestone starting in the future, fall back to the most
        // recent non-completed / non-delayed milestone so the dashboard never
        // shows a bare "TBD" when real milestone data exists.
        if ($upcomingMilestones->isEmpty()) {
            $upcomingMilestones = Milestone::whereHas('phase', function ($q) use ($primaryProject) {
                if ($primaryProject) {
                    $q->where('project_id', $primaryProject->project_id);
                }
            })->where('is_completed', false)
                ->where('is_delayed', false)
                ->with(['phase.project'])
                ->orderByDesc('start_date')
                ->get();
        }

        // Recent reports: if the user explicitly selected a project, filter to it;
        // otherwise show recent reports across all projects for this client.
        // Only show reports that are approved and published to the client.
        $recentReports = Report::query()
            ->when($selectedProjectId, function ($query) use ($selectedProjectId) {
                $query->where('project_id', $selectedProjectId);
            })
            ->whereHas('project', function ($q) use ($client) {
                $q->where('client_id', $client->client_id);
            })
            ->where('approval_status', 'approved')
            ->where('is_published_to_client', true)
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
            $recentDeliveries = MaterialDelivery::query()
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
                'title' => optional($r->phase)->phase_name ? ('Report: '.optional($r->phase)->phase_name) : 'Project report submitted',
                'subtitle' => (strlen($r->report_text ?? '') > 0) ? Str::limit($r->report_text, 80) : 'Report available',
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
                'subtitle' => optional($d->material)->name ? (optional($d->material)->name." ({$d->quantity} {$d->unit})") : 'Delivery recorded',
                'author' => optional($d->project)->project_name ?? 'Project',
                'icon' => 'bi bi-truck',
                'variant' => 'bg-light-gray text-dark',
                'raw' => $d,
            ]);
        }

        $activityItems = $activityCollection->sortByDesc(function ($it) {
            return $it['time'] ? strtotime((string) $it['time']) : 0;
        })->values()->take(5);

        $projectSummaries = $projects->map(function ($project) {
            $phases = $project->phases;
            $completedPhases = $phases->filter(fn ($p) => $p->status === 'completed')->count();

            return [
                'project' => $project,
                'total_phases' => $phases->count(),
                'completed_phases' => $completedPhases,
                'current_phase' => $phases->firstWhere('status', 'in_progress'),
                'completion' => round($phases->avg('completion_percentage') ?? 0, 2),
            ];
        })->sortByDesc('completion');

        // Lightweight per-project payload for the Current Project card's title carousel.
        // Built once here so switching the carousel on the dashboard can update the card
        // instantly on the client side without a full page reload.
        $projectIdsForCarousel = $allProjects->pluck('project_id');

        $delayedCountsByProject = Milestone::whereHas('phase', function ($q) use ($projectIdsForCarousel) {
            $q->whereIn('project_id', $projectIdsForCarousel);
        })
            ->where('is_delayed', true)
            ->where('is_completed', false)
            ->with('phase')
            ->get()
            ->groupBy(fn ($m) => optional($m->phase)->project_id)
            ->map->count();

        $nextMilestoneByProject = Milestone::whereHas('phase', function ($q) use ($projectIdsForCarousel) {
            $q->whereIn('project_id', $projectIdsForCarousel);
        })
            ->where('is_completed', false)
            ->where('is_delayed', false)
            ->with('phase')
            ->orderBy('start_date')
            ->get()
            ->groupBy(fn ($m) => optional($m->phase)->project_id);

        $reportsByProject = Report::query()
            ->whereIn('project_id', $projectIdsForCarousel)
            ->where('approval_status', 'approved')
            ->where('is_published_to_client', true)
            ->with(['project', 'phase', 'submittedBy'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('project_id');

        $milestonesByProject = Milestone::query()
            ->whereHas('phase', function ($q) use ($projectIdsForCarousel) {
                $q->whereIn('project_id', $projectIdsForCarousel);
            })
            ->with('phase', 'project')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->groupBy(fn ($m) => optional($m->phase)->project_id);

        $carouselProjects = $allProjects->map(function ($project) use ($delayedCountsByProject, $nextMilestoneByProject, $reportsByProject, $milestonesByProject) {
            $phases = $project->phases;
            $location = trim((string) ($project->project_location ?? $project->location ?? $project->location_address ?? ''));
            $isDelayed = ($delayedCountsByProject->get($project->project_id, 0)) > 0;
            $nextMilestone = $nextMilestoneByProject->get($project->project_id, collect());
            // Prefer the soonest future milestone; otherwise fall back to the most
            // recent one so the embedded snapshot shows a real date, not "TBD".
            $nextMilestone = $nextMilestone->firstWhere(function ($m) {
                return $m->start_date && $m->start_date->gte(now()->startOfDay());
            }) ?? $nextMilestone->first();
            $activeSupervisor = $project->supervisors->first(function ($s) {
                return $s->pivot->is_active ?? false;
            });

            return [
                'id' => $project->project_id,
                'name' => $project->project_name,
                'image' => $project->image_url ?? 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=1600&q=80',
                'location' => $location !== '' ? $location : 'Location Pending',
                'start_date' => optional($project->start_date)->format('M d, Y') ?? 'TBD',
                'target_end_date' => optional($project->target_end_date)->format('M d, Y') ?? 'TBD',
                'manager' => optional($project->engineer)->name ?? 'Unassigned',
                'supervisor' => optional($activeSupervisor)->name ?? 'Not assigned',
                'progress' => round($phases->avg('completion_percentage') ?? 0, 2),
                'status_label' => $isDelayed ? 'Delayed' : 'On Track',
                'status_class' => $isDelayed ? 'status-delayed' : 'status-on-track',
                'phase' => optional($phases->firstWhere('status', 'in_progress'))->phase_name ?? 'Phase pending',
                'next_milestone_date' => optional($nextMilestone)->start_date?->format('M d, Y') ?? 'Pending',
                'snapshot' => $this->buildProjectSnapshot(
                    $project,
                    $reportsByProject->get($project->project_id, collect()),
                    $milestonesByProject->get($project->project_id, collect())
                ),
            ];
        })->values();

        $primaryProjectIndex = max($allProjects->search(function ($project) use ($primaryProject) {
            return $primaryProject && $project->project_id === $primaryProject->project_id;
        }), 0);

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
            'stats',
            'carouselProjects',
            'primaryProjectIndex'
        ));
    }

    /**
     * Build the full per-project payload consumed by the dashboard's Current Project
     * carousel: hero figures, metric stats, recent reports and activity feed. Kept as a
     * reusable helper so the initial dashboard load can pre-embed every project's snapshot
     * (making swipes instant) and the live AJAX endpoint can return the same shape on demand.
     *
     * @param  Collection|null  $reports  Pre-filtered reports for this project.
     * @param  Collection|null  $milestones  Pre-filtered milestones for this project.
     */
    private function buildProjectSnapshot(Project $project, $reports = null, $milestones = null)
    {
        $project->loadMissing(['phases', 'engineer', 'supervisors']);
        $phases = $project->phases;

        $location = trim((string) ($project->project_location ?? $project->location ?? $project->location_address ?? ''));

        // 'activeSupervisor' is an accessor (not an Eloquent relation), so it can't be
        // eager-loaded; derive it from the already-loaded supervisors collection instead.
        $activeSupervisor = $project->supervisors->first(function ($s) {
            return $s->pivot->is_active ?? false;
        });
        $supervisorName = optional($activeSupervisor)->name ?? 'Not assigned';

        if ($milestones === null) {
            $milestones = Milestone::whereHas('phase', function ($q) use ($project) {
                $q->where('project_id', $project->project_id);
            })
                ->with('phase')
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();
        }

        $isDelayed = $milestones
            ->where('is_delayed', true)
            ->where('is_completed', false)
            ->count() > 0;

        $nextMilestone = $milestones
            ->where('is_completed', false)
            ->where('is_delayed', false)
            ->whereBetween('start_date', [now(), now()->addDays(14)])
            ->sortBy('start_date')
            ->first();

        $currentPhase = $phases->firstWhere('status', 'in_progress');
        $progress = round($phases->avg('completion_percentage') ?? 0, 2);

        if ($reports === null) {
            $reports = Report::where('project_id', $project->project_id)
                ->where('approval_status', 'approved')
                ->where('is_published_to_client', true)
                ->with(['phase', 'submittedBy'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        $latestReport = $reports->first();

        $reportData = $reports->map(function ($report) {
            return [
                'title' => optional($report->phase)->phase_name ?? 'Project Update',
                'subtitle' => Str::limit($report->report_text ?? 'Report available', 48),
                'date' => optional($report->report_date)->format('M d, Y') ?? 'Unknown',
                'download_url' => route('client.reports.downloadPdf', $report->report_id),
            ];
        })->values();

        $activityCollection = collect();

        foreach ($reports as $r) {
            $activityCollection->push([
                'time_raw' => $r->report_date ?? $r->created_at,
                'time' => optional($r->report_date ?? $r->created_at)->format('M d, Y') ?? '',
                'title' => optional($r->phase)->phase_name ? ('Report: '.optional($r->phase)->phase_name) : 'Project report submitted',
                'subtitle' => strlen($r->report_text ?? '') > 0 ? Str::limit($r->report_text, 80) : 'Report available',
                'author' => optional($r->submittedBy)->name ?? 'Project team',
                'icon' => 'bi bi-file-earmark-text',
                'variant' => 'bg-light-green text-dark',
            ]);
        }

        foreach ($milestones as $m) {
            $status = $m->is_completed ? 'Completed' : ($m->is_delayed ? 'Delayed' : 'Planned');
            $activityCollection->push([
                'time_raw' => $m->updated_at ?? $m->created_at,
                'time' => optional($m->updated_at ?? $m->created_at)->format('M d, Y') ?? '',
                'title' => "Milestone: {$m->milestone_name}",
                'subtitle' => "Status: {$status}",
                'author' => $project->project_name,
                'icon' => 'bi bi-flag',
                'variant' => 'bg-light-yellow text-dark',
            ]);
        }

        $activity = $activityCollection
            ->sortByDesc(function ($it) {
                return $it['time_raw'] ? strtotime((string) $it['time_raw']) : 0;
            })
            ->values()
            ->take(5)
            ->map(function ($it) {
                unset($it['time_raw']);

                return $it;
            })
            ->values();

        return [
            'hero' => [
                'id' => $project->project_id,
                'name' => $project->project_name,
                'image' => $project->image_url ?? 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=1600&q=80',
                'location' => $location !== '' ? $location : 'Location Pending',
                'start_date' => optional($project->start_date)->format('M d, Y') ?? 'TBD',
                'target_end_date' => optional($project->target_end_date)->format('M d, Y') ?? 'TBD',
                'manager' => optional($project->engineer)->name ?? 'Unassigned',
                'supervisor' => $supervisorName,
                'progress' => $progress,
                'status_label' => $isDelayed ? 'Delayed' : 'On Track',
                'status_class' => $isDelayed ? 'status-delayed' : 'status-on-track',
                'phase' => optional($currentPhase)->phase_name ?? 'Phase pending',
                'next_milestone_date' => optional($nextMilestone)->start_date?->format('M d, Y') ?? 'Pending',
            ],
            'stats' => [
                'current_phase' => optional($currentPhase)->phase_name ?? 'Phase pending',
                'schedule_health_label' => $isDelayed ? 'At Risk' : 'On Track',
                'schedule_health_pill_class' => $isDelayed ? 'status-delayed' : 'status-on-track',
                'schedule_health_at_risk' => $isDelayed,
                'schedule_health_note' => $isDelayed ? 'Delayed milestones detected' : 'No major delays',
                'next_milestone_name' => optional($nextMilestone)->milestone_name ?? 'Next milestone pending',
                'next_milestone_date' => optional($nextMilestone)->start_date?->format('M d, Y') ?? 'Pending',
                'latest_report_status' => optional($latestReport)->approval_status ?? 'Pending',
                'latest_report_note' => $reports->count() > 0 ? 'Last uploaded report' : 'No report submitted',
            ],
            'reports' => $reportData,
            'activity' => $activity,
        ];
    }

    public function dashboardProjectSnapshot(Project $project)
    {
        $user = Auth::user();
        $client = $user?->client;

        if (! $client || $project->client_id !== $client->client_id) {
            abort(403);
        }

        return response()->json($this->buildProjectSnapshot($project));
    }

    public function notifications(Request $request)
    {
        $user = Auth::user();
        $client = $user?->client;

        if (! $client) {
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

        if (! $client) {
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

        if (! $client) {
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

        if (! $client) {
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

        if (! $client) {
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

        $projects = $query->paginate(6)->onEachSide(1)->appends($request->only(['search', 'status', 'phase', 'completion']));

        $projectSummaries = $projects->getCollection()->map(function ($project) {
            $phases = $project->phases;
            $completedPhases = $phases->filter(fn ($p) => $p->status === 'completed')->count();

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
            ->flatMap(fn ($project) => $project->phases)
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

        if (! $client || $project->client_id !== $client->client_id) {
            abort(403);
        }

        $project->load(['phases', 'engineer', 'supervisors']);

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
        $activeProjectId = null;

        if ($user && $user->client) {
            $projects = Project::query()
                ->where('client_id', '=', $user->client->client_id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Resolve the active project. An explicit filter choice always wins, but
            // when the Reports page is opened without a filter we fall back to the
            // project the client last selected so the choice stays synchronized with
            // the Dashboard (and the rest of the Client portal) within the session.
            if ($request->has('project_id')) {
                $activeProjectId = $request->input('project_id') !== '' ? (int) $request->input('project_id') : null;
            } else {
                $activeProjectId = session('client_selected_project_id');
            }

            if ($activeProjectId && $projects->contains('project_id', $activeProjectId)) {
                $selectedProject = $projects->firstWhere('project_id', $activeProjectId);
            }

            // Keep an explicit project choice sticky so navigating to other Client
            // pages and back preserves it. "All Projects" intentionally does not
            // overwrite the stored selection.
            if ($activeProjectId !== null) {
                session(['client_selected_project_id' => $activeProjectId]);
            }
        }

        $assignedProjectIds = $projects->pluck('project_id')->all();

        $reportsQuery = Report::query()
            ->with(['project', 'phase', 'submittedBy', 'reviewedBy', 'approvedBy'])
            ->whereIn('project_id', $assignedProjectIds)
            ->where('approval_status', 'approved')
            ->where('is_published_to_client', true)
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

        $paginationParams = $request->only(['phase_id', 'status', 'report_date']);
        if ($activeProjectId !== null) {
            $paginationParams['project_id'] = $activeProjectId;
        }

        $reports = $reportsQuery->latest('report_date')->paginate(10)->appends($paginationParams);

        if ($selectedProject) {
            $projectPhases = $selectedProject->phases()->orderBy('phase_order')->get();
        } else {
            $projectPhases = $projects->flatMap(fn ($project) => $project->phases)->sortBy('phase_order')->values();
        }

        $stats = [
            'total' => (clone $reportsQuery)->count(),
            'published' => (clone $reportsQuery)->count(),
        ];

        return view('client.report', compact('projects', 'selectedProject', 'activeProjectId', 'projectPhases', 'reports', 'stats'));
    }

    /**
     * Persist the client's currently selected project to the session so the choice
     * stays synchronized across every Client page (Dashboard, Reports, etc.) without
     * relying solely on the browser's localStorage.
     */
    public function selectProject(Request $request, Project $project)
    {
        $user = Auth::user();
        $client = $user?->client;

        if (! $client || $project->client_id !== $client->client_id) {
            abort(403, 'Project does not belong to this client');
        }

        session(['client_selected_project_id' => $project->project_id]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'project_id' => $project->project_id]);
        }

        return redirect()->back();
    }

    /**
     * Download report as PDF for client users
     */
    public function downloadReportPdf($reportId)
    {
        $user = auth('web')->user();
        $client = $user?->client;

        if (! $client) {
            abort(403, 'Client account not found');
        }

        $report = Report::with(['project', 'phase', 'submittedBy', 'reviewedBy', 'approvedBy'])
            ->where('report_id', $reportId)
            ->where('approval_status', 'approved')
            ->where('is_published_to_client', true)
            ->whereHas('project', function ($q) use ($client) {
                $q->where('client_id', $client->client_id);
            })->firstOrFail();

        // Reuse the supervisor PDF view if a client-specific PDF template is not present
        $viewName = view()->exists('client.reports.pdf') ? 'client.reports.pdf' : 'supervisor.reports.pdf';
        $html = view($viewName, compact('report'))->render();

        try {
            if (class_exists('\\Mpdf\\Mpdf')) {
                $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 10, 'margin_bottom' => 10]);
                $mpdf->WriteHTML($html);
                $fileName = 'report_'.$report->report_id.'_'.date('Y-m-d').'.pdf';

                return $mpdf->Output($fileName, 'D');
            }
        } catch (\Throwable $e) {
            Log::error('PDF generation failed (client): '.$e->getMessage());
        }

        $fileName = 'report_'.$report->report_id.'_'.date('Y-m-d').'.html';

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
