<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectArchive;
use App\Models\Client;
use App\Models\MaterialUsage;
use App\Models\User;
use App\Models\Report; // Added explicitly to prevent class structural errors
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(Request $request)
    {
        $query = Project::with(['client.user', 'engineer', 'supervisors']);

        if ($request->filled('search')) {
            $search = trim($request->search);

            $locationColumn = null;
            try {
                if (Schema::hasColumn('projects', 'project_location')) {
                    $locationColumn = 'project_location';
                } elseif (Schema::hasColumn('projects', 'location')) {
                    $locationColumn = 'location';
                }
            } catch (\Exception $e) {
                $locationColumn = null;
            }

            $query->where(function ($q) use ($search, $locationColumn) {
                $q->where('project_name', 'like', "%{$search}%");

                if ($locationColumn) {
                    $q->orWhere($locationColumn, 'like', "%{$search}%");
                }

                $usersHasName = false;
                $usersHasFirst = false;
                $usersHasLast = false;
                $usersHasFull = false;

                try {
                    $usersHasName = Schema::hasColumn('users', 'name');
                    $usersHasFirst = Schema::hasColumn('users', 'first_name');
                    $usersHasLast = Schema::hasColumn('users', 'last_name');
                    $usersHasFull = Schema::hasColumn('users', 'full_name');
                } catch (\Exception $e) {
                    // ignore schema failures and fallback to conservative options
                }

                $q->orWhereHas('client.user', function ($userQuery) use ($search, $usersHasName, $usersHasFirst, $usersHasLast, $usersHasFull) {
                    if ($usersHasName) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    } elseif ($usersHasFirst && $usersHasLast) {
                        $userQuery->whereRaw("CONCAT(IFNULL(first_name,''),' ',IFNULL(last_name,'')) LIKE ?", ["%{$search}%"]);
                    } elseif ($usersHasFirst) {
                        $userQuery->where('first_name', 'like', "%{$search}%");
                    } elseif ($usersHasFull) {
                        $userQuery->where('full_name', 'like', "%{$search}%");
                    } else {
                        $userQuery->where('email', 'like', "%{$search}%");
                    }
                });

                $q->orWhereHas('supervisors', function ($supervisorQuery) use ($search, $usersHasName, $usersHasFirst, $usersHasLast, $usersHasFull) {
                    if ($usersHasName) {
                        $supervisorQuery->where('name', 'like', "%{$search}%");
                    } elseif ($usersHasFirst && $usersHasLast) {
                        $supervisorQuery->whereRaw("CONCAT(IFNULL(first_name,''),' ',IFNULL(last_name,'')) LIKE ?", ["%{$search}%"]);
                    } elseif ($usersHasFirst) {
                        $supervisorQuery->where('first_name', 'like', "%{$search}%");
                    } elseif ($usersHasFull) {
                        $supervisorQuery->where('full_name', 'like', "%{$search}%");
                    } else {
                        $supervisorQuery->where('email', 'like', "%{$search}%");
                    }
                });
            });
        }

        $requestedStatus = $request->input('status');
        $hasArchiveFlag = Schema::hasColumn('projects', 'is_archived');

        if ($requestedStatus !== null && $requestedStatus !== '') {
            $normalizedStatus = $this->normalizeProjectStatus($requestedStatus);
            if ($normalizedStatus === 'archived') {
                $query->where(function ($q) use ($hasArchiveFlag) {
                    $q->where('status', 'archived');
                    if ($hasArchiveFlag) {
                        $q->orWhereRaw('COALESCE(is_archived, 0) = 1');
                    }
                });
            } elseif ($normalizedStatus !== 'all') {
                $query->whereIn('status', $this->getProjectStatusVariants($normalizedStatus))
                    ->where('status', '!=', 'archived');

                if ($hasArchiveFlag) {
                    $query->where(function ($q) {
                        $q->whereRaw('COALESCE(is_archived, 0) = 0');
                    });
                }
            }
        } else {
            $query->where(function ($q) use ($hasArchiveFlag) {
                $q->where('status', '!=', 'archived');

                if ($hasArchiveFlag) {
                    $q->where(function ($subQuery) {
                        $subQuery->whereRaw('COALESCE(is_archived, 0) = 0');
                    });
                }
            });
        }

        if ($request->filled('client')) {
            $query->where('client_id', $request->client);
        }

        if ($request->filled('supervisor')) {
            $query->whereHas('supervisors', function ($supervisorQuery) use ($request) {
                $supervisorQuery->where('users.user_id', $request->supervisor)
                    ->where('project_supervisors.is_active', true);
            });
        }

        $sortBy = $request->input('sort_by');
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('project_name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('project_name', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $projects = $query->paginate(15)->appends($request->only(['search', 'status', 'client', 'supervisor', 'sort_by']));

        $stats = [
            'total' => Project::query()->count(),
            'planning' => Project::query()->whereIn('status', $this->getProjectStatusVariants('pending'))->count(),
            'ongoing' => Project::query()->whereIn('status', $this->getProjectStatusVariants('in_progress'))->count(),
            'completed' => Project::query()->whereIn('status', $this->getProjectStatusVariants('completed'))->count(),
            'on_hold' => Project::query()->whereIn('status', $this->getProjectStatusVariants('pending'))->count(),
            'archived' => Project::query()->where(function ($q) {
                $q->where('status', 'archived');
                if (Schema::hasColumn('projects', 'is_archived')) {
                    $q->orWhereRaw('COALESCE(is_archived, 0) = 1');
                }
            })->count(),
        ];

        $archives = ProjectArchive::query()
            ->with(['project', 'client.user', 'engineer'])
            ->latest('archived_at')
            ->get();

        $clients = Client::with('user')->get();
        $supervisors = User::query()
            ->where('role', 'supervisor')
            ->where('is_active', true)
            ->orderBy('first_name', 'asc')
            ->get();

        $isAjax = $request->ajax() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        if ($isAjax) {
            return response()->view('admin.projects.partials.table', compact('projects'));
        }

        // If we just redirected here after creating a project, load that project
        // so the success modal can display its details on top of the table page.
        $newProject = null;
        if ((session('show_create_success_modal') || session('show_success_modal')) && session('new_project_id')) {
            $newProject = Project::with(['client.user', 'engineer', 'supervisors', 'phases'])
                ->find(session('new_project_id'));

        }

        return view('admin.projects.index', compact('projects', 'stats', 'clients', 'supervisors', 'newProject', 'archives'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        // Fetch all client profiles along with their user profile data cleanly
        $clients = \App\Models\Client::with('user')->get();
        
        // Fetch site supervisors
        $supervisors = \App\Models\User::query()
            ->where('role', '=', 'supervisor')
            ->where('is_active', '=', 1)
            ->get();

        return view('admin.projects.create', compact('clients', 'supervisors'));
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = auth('web')->user();
            if (!$user) {
                throw new \Exception('User not authenticated');
            }

            $projectData = [
                'project_name' => $request->project_name,
                'client_id' => $request->client_id,
                'engineer_id' => $user->user_id,
                'start_date' => $request->start_date,
                'target_end_date' => $request->target_end_date,
                'actual_end_date' => $request->filled('actual_end_date') ? $request->input('actual_end_date') : null,
                'status' => $request->filled('status') ? $request->input('status') : 'planning',
                'description' => $request->description,
                'project_image' => $this->storeProjectImage($request),
            ];

            $projectData = array_merge($projectData, $this->buildProjectLocationPayload($request->project_location));

            $project = Project::create($projectData);

            // Assign supervisor if provided
            if ($request->supervisor_id) {
                $project->supervisors()->attach($request->supervisor_id, [
                    'assigned_date' => now(),
                    'is_active' => true,
                ]);

                // Notify the assigned supervisor
                try {
                    \App\Services\NotificationService::notifySupervisor($request->supervisor_id, [
                        'type' => 'project',
                        'title' => 'Project Assignment',
                        'message' => "You have been assigned to Project '{$project->project_name}'",
                        'data' => ['module' => 'supervisor.projects', 'project_id' => $project->project_id],
                        'related_id' => $project->project_id,
                        'related_type' => 'project',
                    ]);
                } catch (\Throwable $e) {
                    // ignore notification failures
                }
            }

            // Notify client that a project was created for them
            try {
                if ($project->client_id) {
                    \App\Services\NotificationService::notifyClient($project->client_id, [
                        'type' => 'project',
                        'title' => 'Project Created',
                        'message' => "A new project '{$project->project_name}' has been created for you.",
                        'data' => ['module' => 'client.project.show', 'params' => ['project' => $project->project_id]],
                        'related_id' => $project->project_id,
                        'related_type' => 'project',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to notify client on project creation: ' . $e->getMessage());
            }

            DB::commit();

            // Notify all admins about new project creation
            try {
                \App\Services\NotificationService::notifyAdmins([
                    'type' => 'project',
                    'title' => 'New Project Created',
                    'message' => "Project \"{$project->project_name}\" has been created successfully.",
                    'data' => ['module' => 'admin.projects', 'project_id' => $project->project_id, 'project_name' => $project->project_name, 'recipient' => 'Admin'],
                    'related_id' => $project->project_id,
                    'related_type' => 'project',
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to notify admins on project creation: ' . $e->getMessage());
            }

            return redirect()
                ->route('admin.projects.index')
                ->with('success', 'Project created successfully. Construction phases and milestones can now be added manually by the admin.')
                ->with('success_title', 'Project Created Successfully')
                ->with('show_create_success_modal', true)
                ->with('new_project_id', $project->project_id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create project: ' . $e->getMessage(), [
                'request' => $request->all(),
                'user_id' => optional(auth('web')->user())->user_id,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'We could not create the project right now. Please review the form and try again.');
        }
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project, Request $request)
    {
        // Load all required relationships
        $project->load([
            'client.user',
            'engineer',
            'supervisors',
            'phases'
        ]);

        // Verify project was loaded
        if (!$project || !$project->project_id) {
            return redirect()
                ->route('admin.projects.index')
                ->with('error', 'Project not found.');
        }

        $isAjax = $request->ajax() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        if ($isAjax) {
            // Used by the Project Details modal (fetched from the projects table / sidebar).
            return response()->view('admin.projects.partials.details-modal', [
                'project' => $project,
                'isModal' => true,
            ]);
        }

        return view('admin.projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        $clients = Client::with('user')->get();
        $supervisors = User::query()
            ->where('role', 'supervisor')
            ->where('is_active', true)
            ->orderBy('first_name', 'asc')
            ->get();

        // Get current assigned supervisor
        $currentSupervisor = $project->supervisors()
            ->wherePivot('is_active', true)
            ->first();

        return view('admin.projects.edit', compact('project', 'clients', 'supervisors', 'currentSupervisor'));
    }

    /**
     * Update the specified project in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $redirectTarget = $this->buildAdminProjectsRedirectUrl($request);

        try {
            DB::beginTransaction();

            $project = Project::query()->findOrFail($request->input('project_id', $project->getKey()));

            // Prepare the update data with proper normalization
            $projectName = trim((string) $request->input('project_name', ''));
            $projectLocation = trim((string) $request->input('project_location', $request->input('location', '')));
            $clientId = (int) $request->input('client_id', 0);
            $startDate = $request->input('start_date');
            $targetEndDate = $request->input('target_end_date');
            $actualEndDate = $request->filled('actual_end_date') ? $request->input('actual_end_date') : null;
            $status = (string) $request->input('status', $project->status);
            $description = $request->filled('description') ? trim((string) $request->input('description')) : null;

            $currentStatus = strtolower((string) $project->status);
            $requestedStatus = strtolower((string) $status);
            $normalizedCurrentStatus = match ($currentStatus) {
                'in_progress', 'inprogress', 'ongoing', 'active' => 'ongoing',
                'completed', 'complete', 'finished' => 'completed',
                'on_hold', 'pending' => 'on_hold',
                'planning', 'not_started', 'paused', 'delayed' => 'planning',
                'archived' => 'archived',
                default => 'planning',
            };
            $normalizedRequestedStatus = match ($requestedStatus) {
                'in_progress', 'inprogress', 'ongoing', 'active' => 'ongoing',
                'completed', 'complete', 'finished' => 'completed',
                'on_hold', 'pending' => 'on_hold',
                'planning', 'not_started', 'paused', 'delayed' => 'planning',
                'archived' => 'archived',
                default => 'planning',
            };

            if ($normalizedCurrentStatus === 'archived') {
                return redirect()
                    ->route('admin.projects.index')
                    ->withInput()
                    ->with('edit_project_id', $project->project_id)
                    ->with('show_edit_project_modal', true)
                    ->with('error', 'An archived project cannot be modified.');
            }

            if ($normalizedCurrentStatus === 'planning' && $normalizedRequestedStatus === 'planning') {
                // Planning projects can stay planning or advance to in progress.
            } elseif ($normalizedCurrentStatus === 'planning' && $normalizedRequestedStatus === 'ongoing') {
                // Allowed progression from planning to in progress.
            } elseif ($normalizedCurrentStatus === 'planning' && $normalizedRequestedStatus === 'completed') {
                return redirect()
                    ->route('admin.projects.index')
                    ->withInput()
                    ->with('edit_project_id', $project->project_id)
                    ->with('show_edit_project_modal', true)
                    ->with('error', 'A planning project must move to in progress before it can be marked as completed.');
            } elseif ($normalizedCurrentStatus === 'ongoing' && $normalizedRequestedStatus === 'planning') {
                return redirect()
                    ->route('admin.projects.index')
                    ->withInput()
                    ->with('edit_project_id', $project->project_id)
                    ->with('show_edit_project_modal', true)
                    ->with('error', 'A project that is already in progress cannot be moved back to planning.');
            } elseif ($normalizedCurrentStatus === 'on_hold' && $normalizedRequestedStatus === 'planning') {
                // On-hold projects may return to planning when needed.
            } elseif ($normalizedCurrentStatus === 'on_hold' && $normalizedRequestedStatus === 'on_hold') {
                // On-hold projects can remain on hold.
            } elseif ($normalizedCurrentStatus === 'completed' && $normalizedRequestedStatus !== 'completed') {
                return redirect()
                    ->route('admin.projects.index')
                    ->withInput()
                    ->with('edit_project_id', $project->project_id)
                    ->with('show_edit_project_modal', true)
                    ->with('error', 'A completed project cannot be changed back to planning or in progress.');
            } elseif ($normalizedCurrentStatus === 'planning' && $normalizedRequestedStatus === 'archived') {
                return redirect()
                    ->route('admin.projects.index')
                    ->withInput()
                    ->with('edit_project_id', $project->project_id)
                    ->with('show_edit_project_modal', true)
                    ->with('error', 'A planning project cannot be archived directly.');
            }

            if ($normalizedRequestedStatus === 'completed' && empty($actualEndDate)) {
                $actualEndDate = now()->toDateString();
            }

            // Keep old values for notification decisions
            $oldStatus = $project->status;
            $oldClientId = $project->client_id;

            // Update project details directly once the request passes validation.
            $payload = array_merge([
                'project_name' => $projectName,
                'client_id' => $clientId,
                'start_date' => $startDate,
                'target_end_date' => $targetEndDate,
                'actual_end_date' => $actualEndDate,
                'status' => $status,
                'description' => $description,
            ], $this->buildProjectLocationPayload($projectLocation));

            $imageDecision = $this->resolveProjectImage($request, $project);
            if ($imageDecision['delete_old'] && !empty($project->project_image)) {
                $this->deleteProjectImageFile($project->project_image);
            }
            if (array_key_exists('project_image', $imageDecision)) {
                $payload['project_image'] = $imageDecision['project_image'];
            }

            $project->forceFill($payload);
            $project->save();

            // Preserve the explicit status chosen in the edit form.
            // The workflow status is derived from phases elsewhere, so avoid overwriting
            // the user-submitted status during a manual project update.

            // Update supervisor assignment
            if ($request->has('supervisor_id')) {
                // Deactivate all current supervisors
                $currentSupervisors = $project->supervisors()->pluck('user_id')->toArray();
                if (!empty($currentSupervisors)) {
                    foreach ($currentSupervisors as $supervisorId) {
                        $project->supervisors()->updateExistingPivot($supervisorId, [
                            'is_active' => false,
                        ]);
                    }
                }

                // Assign new supervisor or reactivate existing
                if ($request->supervisor_id) {
                    // Check if supervisor already exists
                    $existingSupervisor = $project->supervisors()
                        ->where('user_id', $request->supervisor_id)
                        ->first();

                    if ($existingSupervisor) {
                        // Update existing pivot
                        $project->supervisors()->updateExistingPivot($request->supervisor_id, [
                            'assigned_date' => now(),
                            'is_active' => true,
                        ]);
                    } else {
                        // Attach new supervisor
                        $project->supervisors()->attach($request->supervisor_id, [
                            'assigned_date' => now(),
                            'is_active' => true,
                        ]);
                    }
                }
            }

            DB::commit();

            session()->forget(['edit_project_id', 'show_edit_project_modal']);

            // Notify client on important changes
            try {
                // If project status changed
                if ($oldStatus !== $status && $project->client_id) {
                    \App\Services\NotificationService::notifyClient($project->client_id, [
                        'type' => 'project',
                        'title' => 'Project Status Updated',
                        'message' => "Project '{$project->project_name}' status changed to {$project->status}.",
                        'data' => ['module' => 'client.project.show', 'params' => ['project' => $project->project_id], 'status' => $project->status],
                        'related_id' => $project->project_id,
                        'related_type' => 'project',
                    ]);
                }

                // If client assignment changed notify the new client
                if ($oldClientId !== $project->client_id && $project->client_id) {
                    \App\Services\NotificationService::notifyClient($project->client_id, [
                        'type' => 'project',
                        'title' => 'Assigned To Project',
                        'message' => "You have been assigned to project '{$project->project_name}'.",
                        'data' => ['module' => 'client.project.show', 'params' => ['project' => $project->project_id]],
                        'related_id' => $project->project_id,
                        'related_type' => 'project',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to notify client on project update: ' . $e->getMessage());
            }

            return redirect($redirectTarget)
                ->with('success', 'Project updated successfully.')
                ->with('success_title', 'Project Updated')
                ->with('show_create_success_modal', false)
                ->with('show_edit_project_modal', false)
                ->with('new_project_id', $project->project_id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update project: ' . $e->getMessage(), [
                'project_id' => $project->project_id,
                'request' => $request->all(),
                'user_id' => optional(auth('web')->user())->user_id,
            ]);

            return redirect($redirectTarget)
                ->withInput()
                ->with('edit_project_id', $project->project_id)
                ->with('show_edit_project_modal', true)
                ->with('error', 'We could not update the project right now. Please review the form and try again.');
        }
    }

    /**
     * Archive the specified project (soft archive via status).
     */
    public function archive(Project $project)
    {
        $user = Auth::guard('web')->user();
        if (!$this->isAdminUser($user)) {
            return redirect()
                ->back()
                ->with('error', 'Only admins can archive projects.')
                ->with('error_title', 'Permission Denied');
        }

        try {
            if ($this->projectIsArchived($project)) {
                return redirect()
                    ->back()
                    ->with('error', 'This project is already archived.')
                    ->with('error_title', 'Cannot Archive Project');
            }

            $payload = [
                'status' => $this->getArchiveStatusValue(),
            ];
            if (Schema::hasColumn('projects', 'is_archived')) {
                $payload['is_archived'] = true;
            }

            $project->forceFill($payload);
            $project->save();
            $project->refresh();

            ProjectArchive::updateOrCreate(
                ['project_id' => $project->getKey()],
                [
                    'project_name' => $project->project_name,
                    'project_location' => \App\Models\ProjectArchive::resolveLocation($project),
                    'client_id' => $project->client_id,
                    'engineer_id' => $project->engineer_id,
                    'start_date' => $project->start_date,
                    'target_end_date' => $project->target_end_date,
                    'actual_end_date' => $project->actual_end_date,
                    'status' => 'archived',
                    'description' => $project->description,
                    'archived_at' => now(),
                ]
            );

            return redirect()->route('admin.projects.index')
                ->with('success', 'Project archived successfully.')
                ->with('success_title', 'Project Archived');

        } catch (\Exception $e) {
            Log::error('Failed to archive project.', [
                'project_id' => $project->project_id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to archive project: ' . $e->getMessage())
                ->with('error_title', 'Archive Failed');
        }
    }

    /**
     * Restore an archived project back to the active workflow.
     */
    public function restore(Project $project)
    {
        $user = Auth::guard('web')->user();
        if (!$this->isAdminUser($user)) {
            return redirect()
                ->back()
                ->with('error', 'Only admins can restore projects.')
                ->with('error_title', 'Permission Denied');
        }

        try {
            $project->refresh();
            $isArchived = $this->projectIsArchived($project);
            if (!$isArchived) {
                return redirect()
                    ->back()
                    ->with('info', 'Only archived projects can be restored.')
                    ->with('error_title', 'Restore Not Available');
            }

            $payload = ['status' => $this->getRestoreStatusValue()];
            if (Schema::hasColumn('projects', 'is_archived')) {
                $payload['is_archived'] = false;
            }

            $project->forceFill($payload);
            $project->save();
            $project->refresh();

            ProjectArchive::where('project_id', $project->project_id)->delete();

            return redirect()->route('admin.projects.index')
                ->with('success', 'Project restored successfully.')
                ->with('success_title', 'Project Restored');

        } catch (\Exception $e) {
            Log::error('Failed to restore project.', [
                'project_id' => $project->project_id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to restore project: ' . $e->getMessage())
                ->with('error_title', 'Restore Failed');
        }
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project)
    {
        $user = Auth::guard('web')->user();
        if (!$this->isAdminUser($user)) {
            return redirect()
                ->back()
                ->with('error', 'Only admins can delete projects.')
                ->with('error_title', 'Permission Denied');
        }

        try {
            if ($this->projectHasRelatedRecords($project)) {
                return redirect()
                    ->back()
                    ->with('error', 'This project already contains construction records. Projects with existing phases, milestones, reports, attendance, or material usage cannot be deleted. Please archive the project instead.')
                    ->with('error_title', 'Cannot Delete Project');
            }

            $project->delete();

            return redirect()->route('admin.projects.index')
                ->with('success', 'Project deleted successfully.')
                ->with('success_title', 'Project Deleted');

        } catch (\Exception $e) {
            Log::error('Failed to delete project.', [
                'project_id' => $project->project_id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to delete project: ' . $e->getMessage())
                ->with('error_title', 'Delete Failed');
        }
    }

    private function projectHasRelatedRecords(Project $project): bool
    {
        return (
            $project->phases()->exists() ||
            $project->phases()->whereHas('milestones')->exists() ||
            $project->reports()->exists() ||
            $project->materialUsages()->exists() ||
            $project->attendanceLogs()->exists()
        );
    }

    private function projectIsArchived(Project $project): bool
    {
        $status = strtolower((string) ($project->status ?? ''));
        if ($status === 'archived') {
            return true;
        }

        if ($status === 'completed' && (bool) $project->getAttribute('is_archived')) {
            return true;
        }

        if (!Schema::hasColumn('projects', 'is_archived')) {
            return false;
        }

        return (bool) $project->getAttribute('is_archived');
    }

    protected function getArchiveStatusValue(): string
    {
        return 'archived';
    }

    protected function getRestoreStatusValue(): string
    {
        return 'planning';
    }

    private function isAdminUser($user): bool
    {
        if (!$user) {
            return false;
        }

        return in_array(strtolower((string) $user->role), ['engineer', 'admin', 'administrator'], true);
    }

    /**
     * Render the custom Phase Management Workspace.
     * Aligned explicitly with the Project Engineer / Admin approval ecosystem.
     */
    private function buildAdminProjectsRedirectUrl(?Request $request = null): string
    {
        return route('admin.projects.index', [], false);
    }

    private function buildProjectLocationPayload(string $location): array
    {
        $payload = [];

        if (Schema::hasColumn('projects', 'project_location')) {
            $payload['project_location'] = $location;
        }

        if (Schema::hasColumn('projects', 'location')) {
            $payload['location'] = $location;
        }

        if (empty($payload)) {
            $payload['project_location'] = $location;
        }

        return $payload;
    }

    /**
     * Store an uploaded project cover image (create flow).
     */
    private function storeProjectImage(Request $request): ?string
    {
        if ($request->hasFile('project_image') && $request->file('project_image')->isValid()) {
            return $request->file('project_image')->store('project-images', 'public');
        }

        return null;
    }

    /**
     * Resolve the image action for the update flow.
     * Returns project_image key only when it should change.
     */
    private function resolveProjectImage(Request $request, Project $project): array
    {
        if ($request->hasFile('project_image') && $request->file('project_image')->isValid()) {
            return [
                'project_image' => $request->file('project_image')->store('project-images', 'public'),
                'delete_old' => true,
            ];
        }

        if ((int) $request->input('remove_image', 0) === 1) {
            return [
                'project_image' => null,
                'delete_old' => true,
            ];
        }

        return ['delete_old' => false];
    }

    private function deleteProjectImageFile(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        try {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        } catch (\Throwable $e) {
            Log::error('Failed to delete project image: ' . $e->getMessage());
        }
    }

    private function normalizeProjectStatus($status): string
    {
        return match (strtolower((string) $status)) {
            'in_progress', 'inprogress', 'ongoing', 'active' => 'in_progress',
            'completed', 'complete', 'finished' => 'completed',
            'pending', 'planning', 'not_started', 'on_hold', 'paused', 'delayed' => 'pending',
            'archived' => 'archived',
            default => 'pending',
        };
    }

    private function getProjectStatusVariants(string $status): array
    {
        return match ($status) {
            'in_progress' => ['in_progress', 'ongoing', 'inprogress', 'active'],
            'completed' => ['completed', 'complete', 'finished'],
            'pending' => ['planning', 'on_hold'],
            'archived' => ['archived'],
            default => ['pending'],
        };
    }

    public function phaseManagement(Request $request)
    {
        $pendingReports = collect();

        if (Schema::hasTable('accomplishment_reports')) {
            $reportColumns = Schema::getColumnListing('accomplishment_reports');
            $query = Report::query()->with(['project', 'submittedBy'])->orderBy('created_at', 'desc');

            if (in_array('approval_status', $reportColumns, true)) {
                $query->where('approval_status', 'pending');
            } elseif (in_array('status', $reportColumns, true)) {
                $query->where('status', 'pending');
            }

            $pendingReports = $query->get();
        }

        $auditLogs = [];
        if (class_exists('\App\Models\PhaseAuditLog')) {
            $auditLogs = \App\Models\PhaseAuditLog::with(['project', 'user'])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
        }

        $projects = Project::query()
            ->with(['phases' => function ($query) {
                $query->orderBy('phase_order')->with('milestones');
            }])
            ->orderBy('project_name')
            ->get();

        $selectedProject = null;
        $phases = collect();
        $selectedPhase = null;
        $stats = [
            'total' => 0,
            'completed' => 0,
            'in_progress' => 0,
            'pending' => 0,
            'delayed' => 0,
        ];

        if ($projects->isNotEmpty()) {
            $selectedProject = $projects->firstWhere('project_id', (int) $request->input('project_id'))
                ?: $projects->first();

            $selectedProject->loadMissing(['phases' => function ($query) {
                $query->orderBy('phase_order')->with('milestones');
            }]);

            $allPhases = $selectedProject->phases;
            $phases = $selectedProject->phases()
                ->with('milestones')
                ->orderBy('phase_order')
                ->paginate(10)
                ->appends($request->query());
            $selectedPhase = null;

            $stats = [
                'total' => $allPhases->count(),
                'completed' => $allPhases->where('status', 'completed')->count(),
                'in_progress' => $allPhases->where('status', 'in_progress')->count(),
                'pending' => $allPhases->where('status', 'not_started')->count(),
                'delayed' => $allPhases->where('status', 'delayed')->count(),
            ];
        }

        return view('admin.phases', compact(
            'pendingReports',
            'auditLogs',
            'projects',
            'selectedProject',
            'phases',
            'selectedPhase',
            'stats'
        ));
    }
}