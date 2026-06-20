<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ConstructionPhase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PhaseController extends Controller
{
    /**
     * Display phases for a project
     */
    public function index(Request $request)
    {
        $query = Project::with(['phases' => function ($q) {
            $q->orderBy('phase_order');
        }])->where('engineer_id', auth('web')->user()->user_id);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('project_name', 'like', "%{$search}%");
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $projects = $query->paginate(10)->appends($request->only(['search', 'project_id']));

        return view('admin.phases.index', compact('projects'));
    }

    /**
     * Show phases for a specific project
     */
    public function show($projectId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProject($project);

        $phases = $project->phases()
            ->orderBy('phase_order')
            ->get();

        return view('admin.phases.show', compact('project', 'phases'));
    }

    /**
     * Show the form for creating a new phase
     */
    public function create($projectId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProject($project);

        $nextPhaseOrder = $project->phases()->max('phase_order') + 1 ?? 1;

        return view('admin.phases.create', compact('project', 'nextPhaseOrder'));
    }

    /**
     * Store a newly created phase
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'phase_name' => 'required|string|max:200',
            'phase_order' => 'required|integer|min:1',
            'planned_start_date' => 'required|date',
            'planned_end_date' => 'required|date|after:planned_start_date',
            'status' => 'required|in:not_started,in_progress,completed,delayed',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $this->authorizeProject($project);

        try {
            DB::beginTransaction();

            $phase = ConstructionPhase::create([
                'project_id' => $validated['project_id'],
                'phase_name' => $validated['phase_name'],
                'phase_order' => $validated['phase_order'],
                'planned_start_date' => $validated['planned_start_date'],
                'planned_end_date' => $validated['planned_end_date'],
                'status' => $validated['status'],
                'completion_percentage' => 0.00,
            ]);

            // Log the action
            $this->logAction('Phase Created', "Phase '{$phase->phase_name}' created for project '{$project->project_name}'");

            DB::commit();

            return redirect()
                ->route('admin.phases.show', $project->project_id)
                ->with('success', 'Phase created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Phase creation failed: ' . $e->getMessage());
            return back()->withErrors('Failed to create phase')->withInput();
        }
    }

    /**
     * Show the form for editing a phase
     */
    public function edit($projectId, $phaseId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProject($project);

        $phase = ConstructionPhase::query()
            ->where('phase_id', $phaseId)
            ->where('project_id', $projectId)
            ->firstOrFail();

        return view('admin.phases.edit', compact('project', 'phase'));
    }

    /**
     * Update a phase
     */
    public function update(Request $request, $projectId, $phaseId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProject($project);

        $phase = ConstructionPhase::query()
            ->where('phase_id', $phaseId)
            ->where('project_id', $projectId)
            ->firstOrFail();

        $validated = $request->validate([
            'phase_name' => 'required|string|max:200',
            'phase_order' => 'required|integer|min:1',
            'planned_start_date' => 'required|date',
            'planned_end_date' => 'required|date|after:planned_start_date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date|after:actual_start_date',
            'completion_percentage' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:not_started,in_progress,completed,delayed',
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $phase->status;
            $oldCompletion = $phase->completion_percentage;

            $phase->update($validated);

            // Log significant changes
            if ($oldStatus !== $phase->status) {
                $this->logAction('Phase Status Changed', "Phase '{$phase->phase_name}' status changed from {$oldStatus} to {$phase->status}");
            }

            if ($oldCompletion !== $phase->completion_percentage) {
                $this->logAction('Phase Completion Updated', "Phase '{$phase->phase_name}' completion changed from {$oldCompletion}% to {$phase->completion_percentage}%");
            }

            DB::commit();

            return redirect()
                ->route('admin.phases.show', $projectId)
                ->with('success', 'Phase updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Phase update failed: ' . $e->getMessage());
            return back()->withErrors('Failed to update phase')->withInput();
        }
    }

    /**
     * Delete a phase
     */
    public function destroy($projectId, $phaseId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProject($project);

        $phase = ConstructionPhase::query()
            ->where('phase_id', $phaseId)
            ->where('project_id', $projectId)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            $phaseName = $phase->phase_name;
            $phase->forceDelete();

            $this->logAction('Phase Deleted', "Phase '{$phaseName}' deleted from project '{$project->project_name}'");

            DB::commit();

            return redirect()
                ->route('admin.phases.show', $projectId)
                ->with('success', 'Phase deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Phase deletion failed: ' . $e->getMessage());
            return back()->withErrors('Failed to delete phase');
        }
    }

    /**
     * Authorize that the user owns this project
     */
    private function authorizeProject(Project $project)
    {
        if ($project->engineer_id !== auth('web')->user()->user_id) {
            abort(403, 'Unauthorized to manage phases for this project');
        }
    }

    /**
     * Log system action
     */
    private function logAction($action, $description)
    {
        \App\Models\SystemLog::create([
            'user_id' => auth('web')->user()->user_id,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
