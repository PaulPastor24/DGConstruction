<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client;
use App\Models\User;
use App\Models\Report; // Added explicitly to prevent class structural errors
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Http\Request;
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
        $query = Project::with(['client.user', 'engineer', 'supervisors'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                  ->orWhere('project_location', 'like', "%{$search}%")
                  ->orWhereHas('client.user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status') && in_array($request->status, ['planning', 'ongoing', 'completed', 'on_hold', 'archived'])) {
            $query->where('status', $request->status);
        }

        $projects = $query->paginate(15)->appends($request->only(['search', 'status']));

        $stats = [
            'total' => Project::query()->count('*'),
            'planning' => Project::query()->where('status', 'planning')->count('*'),
            'ongoing' => Project::query()->where('status', 'ongoing')->count('*'),
            'completed' => Project::query()->where('status', 'completed')->count('*'),
            'on_hold' => Project::query()->where('status', 'on_hold')->count('*'),
            'archived' => Project::query()->where('status', 'archived')->count('*'),
        ];

        return view('admin.projects.index', compact('projects', 'stats'));
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
            ->where('role', '=', 'site_supervisor')
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

            // Create the project with default status "planning"
            $project = Project::create([
                'project_name' => $request->project_name,
                'project_location' => $request->project_location,
                'client_id' => $request->client_id,
                'engineer_id' => $user->user_id,
                'start_date' => $request->start_date,
                'target_end_date' => $request->target_end_date,
                'status' => 'planning', // Always default to planning on create
                'description' => $request->description,
            ]);

            // Assign supervisor if provided
            if ($request->supervisor_id) {
                $project->supervisors()->attach($request->supervisor_id, [
                    'assigned_date' => now(),
                    'is_active' => true,
                ]);
            }

            // AUTOMATION LINK: Seed default physical construction timelines into 'phases' table
            $defaultPhases = [
                ['phase_name' => 'Phase 1: Mobilization & Site Clearance', 'weight' => 10],
                ['phase_name' => 'Phase 2: Substructure & Foundation Works', 'weight' => 30],
                ['phase_name' => 'Phase 3: Structural Frame & Superstructure', 'weight' => 40],
                ['phase_name' => 'Phase 4: Architectural Works & Finishing', 'weight' => 20],
            ];

            foreach ($defaultPhases as $index => $phase) {
                $project->phases()->create([
                    'phase_name'            => $phase['phase_name'],
                    // Standardized statuses matching the enum choices in your migration exactly[cite: 6]
                    'status'                => $index === 0 ? 'in_progress' : 'not_started', 
                    'completion_percentage' => 0,
                    'planned_start_date'    => $project->start_date,      
                    'planned_end_date'      => $project->target_end_date, 
                    'phase_order'           => $index + 1,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.projects.show', $project)
                ->with('success', 'Project created and architectural milestones initialized successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create project: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
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

        // Debug: Log the project data being passed to view
        Log::info('Project show data:', [
            'project_id' => $project->project_id,
            'project_name' => $project->project_name,
            'status' => $project->status,
            'location' => $project->project_location
        ]);

        return view('admin.projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        $clients = Client::with('user')->get();
        $supervisors = User::query()
            ->where('role', 'site_supervisor')
            ->where('is_active', true)
            ->orderBy('name')
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
        try {
            DB::beginTransaction();

            // Prepare the update data with proper normalization
            $projectName = trim($request->input('project_name', ''));
            $projectLocation = trim($request->input('project_location', ''));
            $clientId = (int) $request->input('client_id', 0);
            $startDate = $request->input('start_date');
            $targetEndDate = $request->input('target_end_date');
            $actualEndDate = $request->input('actual_end_date');
            $status = $request->input('status');
            $description = $request->input('description') ? trim($request->input('description')) : null;

            // Prevent invalid status transitions
            $currentStatus = $project->status;
            if ($currentStatus === 'archived') {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'An archived project cannot be modified.');
            }

            if ($currentStatus === 'ongoing' && $status === 'planning') {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'An ongoing project cannot be changed back to planning.');
            }

            if ($currentStatus === 'completed' && in_array($status, ['planning', 'ongoing'])) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'A completed project cannot be changed to planning or ongoing.');
            }

            // If a project is being completed, make sure actual end date is set
            if ($status === 'completed' && empty($actualEndDate)) {
                $actualEndDate = now()->toDateString();
            }

            // Compare each field to detect actual changes
            $hasChanges = false;

            // Compare text fields
            if ($project->project_name !== $projectName) {
                $hasChanges = true;
            }
            if ($project->project_location !== $projectLocation) {
                $hasChanges = true;
            }
            if ((int) $project->client_id !== $clientId) {
                $hasChanges = true;
            }
            if ($project->status !== $status) {
                $hasChanges = true;
            }
            if ($project->description !== $description) {
                $hasChanges = true;
            }

            // Compare dates as strings
            if ($startDate && $project->start_date) {
                $newStartDate = \Carbon\Carbon::parse($startDate)->toDateString();
                $oldStartDate = $project->start_date->toDateString();
                if ($newStartDate !== $oldStartDate) {
                    $hasChanges = true;
                }
            }

            if ($targetEndDate && $project->target_end_date) {
                $newTargetDate = \Carbon\Carbon::parse($targetEndDate)->toDateString();
                $oldTargetDate = $project->target_end_date->toDateString();
                if ($newTargetDate !== $oldTargetDate) {
                    $hasChanges = true;
                }
            }

            // Compare actual end date (can be null)
            if ($actualEndDate && $project->actual_end_date) {
                $newActualDate = \Carbon\Carbon::parse($actualEndDate)->toDateString();
                $oldActualDate = $project->actual_end_date->toDateString();
                if ($newActualDate !== $oldActualDate) {
                    $hasChanges = true;
                }
            } elseif (($actualEndDate === null && $project->actual_end_date !== null) || 
                      ($actualEndDate !== null && $project->actual_end_date === null)) {
                $hasChanges = true;
            }

            // Check for supervisor changes
            $newSupervisorId = $request->input('supervisor_id') ? (int) $request->input('supervisor_id') : null;
            $currentSupervisor = $project->supervisors()
                ->wherePivot('is_active', true)
                ->first();
            $currentSupervisorId = $currentSupervisor ? (int) $currentSupervisor->user_id : null;

            if ($newSupervisorId !== $currentSupervisorId) {
                $hasChanges = true;
            }

            // If no actual changes detected, return early
            if (!$hasChanges) {
                DB::rollBack();
                return redirect()
                    ->route('admin.projects.edit', $project)
                    ->withInput()
                    ->with('info', 'No changes were detected.');
            }

            // Update project details
            $project->update([
                'project_name' => $projectName,
                'project_location' => $projectLocation,
                'client_id' => $clientId,
                'start_date' => $startDate,
                'target_end_date' => $targetEndDate,
                'actual_end_date' => $actualEndDate,
                'status' => $status,
                'description' => $description,
            ]);

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

            return redirect()
                ->route('admin.projects.index')
                ->with('success', 'Project updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update project: ' . $e->getMessage());
        }
    }

    /**
     * Archive the specified project (soft archive via status).
     */
    public function archive(Project $project)
    {
        try {
            if (in_array($project->status, ['completed', 'archived'])) {
                return redirect()
                    ->back()
                    ->with('error', 'Completed or archived projects cannot be archived again.');
            }

            $project->update(['status' => 'archived']);

            return redirect()
                ->route('admin.projects.index')
                ->with('success', 'Project archived successfully!');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to archive project: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project)
    {
        try {
            $hasRelatedRecords = (
                $project->phases()->exists() ||
                $project->supervisors()->exists() ||
                $project->reports()->exists() ||
                $project->attendanceLogs()->exists() ||
                $project->workers()->exists()
            );

            if ($hasRelatedRecords) {
                $project->phases()->delete();
                $project->supervisors()->detach();
                $project->attendanceLogs()->delete();
                $project->workers()->detach();
                $project->reports()->delete();
            }

            DB::table('projects')
                ->where('project_id', $project->project_id)
                ->delete();

            return redirect()
                ->route('admin.projects.index')
                ->with('success', 'Project deleted successfully!');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete project: ' . $e->getMessage());
        }
    }

    /**
     * Render the custom Phase Management Workspace.
     * Aligned explicitly with the Project Engineer / Admin approval ecosystem.
     */
    public function phaseManagement()
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

        return view('admin.phases', compact('pendingReports', 'auditLogs'));
    }
}