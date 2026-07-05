<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ConstructionPhase;
use App\Models\Milestone;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MilestoneController extends Controller
{
    /**
     * Display milestones for a phase
     */
    public function index(Request $request, $projectId, $phaseId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProject($project);

        $phase = ConstructionPhase::query()
            ->where('phase_id', $phaseId)
            ->where('project_id', $projectId)
            ->firstOrFail();

        $milestones = $phase->milestones()
            ->orderBy('planned_date')
            ->get();

        $stats = [
            'total' => $milestones->count(),
            'completed' => $milestones->where('is_completed', true)->count(),
            'delayed' => $milestones->where('is_delayed', true)->count(),
            'upcoming' => $milestones->where('is_completed', false)->where('is_delayed', false)->count(),
        ];

        return view('admin.milestones.index', compact('project', 'phase', 'milestones', 'stats'));
    }

    /**
     * Show the form for creating a new milestone
     */
    public function create($projectId, $phaseId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProject($project);

        $phase = ConstructionPhase::query()
            ->where('phase_id', $phaseId)
            ->where('project_id', $projectId)
            ->firstOrFail();

        return view('admin.milestones.create', compact('project', 'phase'));
    }

    /**
     * Store a newly created milestone
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'phase_id' => 'required|exists:construction_phases,phase_id',
            'milestone_name' => 'required|string|max:200',
            'planned_date' => 'required|date|after:today',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $this->authorizeProject($project);

        $phase = ConstructionPhase::query()
            ->where('phase_id', $validated['phase_id'])
            ->where('project_id', $validated['project_id'])
            ->firstOrFail();

        try {
            DB::beginTransaction();

            $milestone = Milestone::create([
                'phase_id' => $validated['phase_id'],
                'milestone_name' => $validated['milestone_name'],
                'planned_date' => $validated['planned_date'],
                'is_completed' => false,
                'is_delayed' => false,
            ]);

            // Notify client about new milestone
            try {
                if ($project && $project->client_id) {
                    \App\Services\NotificationService::notifyClient($project->client_id, [
                        'type' => 'milestone',
                        'title' => 'New Milestone Added',
                        'message' => "A new milestone '{$milestone->milestone_name}' was added to project '{$project->project_name}'.",
                        'data' => ['module' => 'client.milestones', 'milestone_id' => $milestone->milestone_id, 'project_id' => $project->project_id],
                        'related_id' => $milestone->milestone_id,
                        'related_type' => 'milestone',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to notify client on milestone creation: ' . $e->getMessage());
            }
            $this->logAction(
                'Milestone Created',
                "Milestone '{$milestone->milestone_name}' created in phase '{$phase->phase_name}' of project '{$project->project_name}'"
            );

            DB::commit();

            return redirect()
                ->route('admin.milestones.index', [$project->project_id, $phase->phase_id])
                ->with('success', 'Milestone created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Milestone creation failed: ' . $e->getMessage());
            return back()->withErrors('Failed to create milestone')->withInput();
        }
    }

    /**
     * Show the form for editing a milestone
     */
    public function edit($projectId, $phaseId, $milestoneId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProject($project);

        $phase = ConstructionPhase::query()
            ->where('phase_id', $phaseId)
            ->where('project_id', $projectId)
            ->firstOrFail();

        $milestone = Milestone::query()
            ->where('milestone_id', $milestoneId)
            ->where('phase_id', $phaseId)
            ->firstOrFail();

        return view('admin.milestones.edit', compact('project', 'phase', 'milestone'));
    }

    /**
     * Update a milestone
     */
    public function update(Request $request, $projectId, $phaseId, $milestoneId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProject($project);

        $phase = ConstructionPhase::query()
            ->where('phase_id', $phaseId)
            ->where('project_id', $projectId)
            ->firstOrFail();

        $milestone = Milestone::query()
            ->where('milestone_id', $milestoneId)
            ->where('phase_id', $phaseId)
            ->firstOrFail();

        $validated = $request->validate([
            'milestone_name' => 'required|string|max:200',
            'planned_date' => 'required|date',
            'actual_date' => 'nullable|date',
            'is_completed' => 'boolean',
            'is_delayed' => 'boolean',
        ]);

        try {
            DB::beginTransaction();


            $oldStatus = $milestone->is_completed;
            $oldDelayed = $milestone->is_delayed;

            $milestone->update($validated);

            // Log status changes
            $changes = [];
            if ($oldStatus !== $milestone->is_completed) {
                $changes[] = $milestone->is_completed ? 'marked as completed' : 'marked as incomplete';
            }
            if ($oldDelayed !== $milestone->is_delayed) {
                $changes[] = $milestone->is_delayed ? 'marked as delayed' : 'delayed status removed';
            }

            if (!empty($changes)) {
                $this->logAction(
                    'Milestone Updated',
                    "Milestone '{$milestone->milestone_name}' " . implode(', ', $changes)
                );
                
                try {
                    $project = $milestone->project ?? Project::query()->find($milestone->project_id);
                    if ($project && $project->client_id) {
                        \App\Services\NotificationService::notifyClient($project->client_id, [
                            'type' => 'milestone',
                            'title' => 'Milestone Updated',
                            'message' => "Milestone '{$milestone->milestone_name}' was updated: " . implode(', ', $changes),
                            'data' => ['module' => 'client.milestones', 'milestone_id' => $milestone->milestone_id, 'project_id' => $project->project_id],
                            'related_id' => $milestone->milestone_id,
                            'related_type' => 'milestone',
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Failed to notify client on milestone update: ' . $e->getMessage());
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.milestones.index', [$projectId, $phaseId])
                ->with('success', 'Milestone updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Milestone update failed: ' . $e->getMessage());
            return back()->withErrors('Failed to update milestone')->withInput();
        }
    }

    /**
     * Mark a milestone as completed
     */
    public function complete($projectId, $phaseId, $milestoneId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProject($project);

        $milestone = Milestone::whereHas('phase', function ($q) use ($phaseId, $projectId) {
            $q->where('phase_id', $phaseId)->where('project_id', $projectId);
        })->findOrFail($milestoneId);

        try {
            DB::beginTransaction();

            $milestone->update([
                'is_completed' => true,
                'is_delayed' => false,
                'actual_date' => now()->toDateString(),
            ]);

            $this->logAction(
                'Milestone Completed',
                "Milestone '{$milestone->milestone_name}' marked as completed"
            );

            // Notify client about milestone completion
            try {
                if ($project && $project->client_id) {
                    \App\Services\NotificationService::notifyClient($project->client_id, [
                        'type' => 'milestone',
                        'title' => 'Milestone Completed',
                        'message' => "Milestone '{$milestone->milestone_name}' has been completed for project '{$project->project_name}'.",
                        'data' => ['module' => 'client.milestones', 'milestone_id' => $milestone->milestone_id, 'project_id' => $project->project_id],
                        'related_id' => $milestone->milestone_id,
                        'related_type' => 'milestone',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to notify client on milestone completion: ' . $e->getMessage());
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Milestone marked as completed']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Milestone completion failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to complete milestone'], 500);
        }
    }

    /**
     * Mark a milestone as delayed
     */
    public function markDelayed($projectId, $phaseId, $milestoneId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProject($project);

        $milestone = Milestone::whereHas('phase', function ($q) use ($phaseId, $projectId) {
            $q->where('phase_id', $phaseId)->where('project_id', $projectId);
        })->findOrFail($milestoneId);

        try {
            DB::beginTransaction();

            $milestone->update(['is_delayed' => true]);

            $this->logAction(
                'Milestone Delayed',
                "Milestone '{$milestone->milestone_name}' marked as delayed"
            );

            // Notify client about milestone delay
            try {
                if ($project && $project->client_id) {
                    \App\Services\NotificationService::notifyClient($project->client_id, [
                        'type' => 'milestone',
                        'title' => 'Milestone Delayed',
                        'message' => "Milestone '{$milestone->milestone_name}' has been marked delayed for project '{$project->project_name}'.",
                        'data' => ['module' => 'client.milestones', 'milestone_id' => $milestone->milestone_id, 'project_id' => $project->project_id],
                        'related_id' => $milestone->milestone_id,
                        'related_type' => 'milestone',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to notify client on milestone delay: ' . $e->getMessage());
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Milestone marked as delayed']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mark milestone delayed failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to mark milestone as delayed'], 500);
        }
    }

    /**
     * Delete a milestone
     */
    public function destroy($projectId, $phaseId, $milestoneId)
    {
        $project = Project::findOrFail($projectId);
        $this->authorizeProject($project);

        $milestone = Milestone::whereHas('phase', function ($q) use ($phaseId, $projectId) {
            $q->where('phase_id', $phaseId)->where('project_id', $projectId);
        })->findOrFail($milestoneId);

        try {
            DB::beginTransaction();

            $milestoneName = $milestone->milestone_name;
            $milestone->delete();

            $this->logAction(
                'Milestone Deleted',
                "Milestone '{$milestoneName}' deleted from phase '{$phaseId}'"
            );

            DB::commit();

            return redirect()
                ->route('admin.milestones.index', [$projectId, $phaseId])
                ->with('success', 'Milestone deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Milestone deletion failed: ' . $e->getMessage());
            return back()->withErrors('Failed to delete milestone');
        }
    }

    /**
     * Authorize that the user owns this project
     */
    private function authorizeProject(Project $project)
    {
        if ($project->engineer_id !== auth('web')->user()->user_id) {
            abort(403, 'Unauthorized to manage milestones for this project');
        }
    }

    /**
     * Log system action
     */
    private function logAction($action, $description)
    {
        SystemLog::create([
            'user_id' => auth('web')->user()->user_id,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
