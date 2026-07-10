<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\NotificationService;
use App\Models\ConstructionPhase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
        try {
            $project = Project::findOrFail($request->input('project_id'));
            $this->authorizeProject($project);

            $validated = $request->validate([
                'project_id' => 'required|exists:projects,project_id',
                'phase_name' => 'required|string|max:200',
                'phase_order' => ['required', 'integer', 'min:1', function ($attribute, $value, $fail) use ($project) {
                    $exists = ConstructionPhase::query()
                        ->where('project_id', $project->project_id)
                        ->where('phase_order', $value)
                        ->exists();

                    if ($exists) {
                        $fail('A phase with this order already exists for this project.');
                    }
                }],
                'planned_start_date' => 'required|date',
                'planned_end_date' => 'required|date|after_or_equal:planned_start_date',
                'actual_start_date' => 'nullable|date',
                'actual_end_date' => 'nullable|date|after_or_equal:actual_start_date',
            ], [
                'phase_name.required' => 'Please enter a phase name.',
                'phase_order.required' => 'Please enter a phase order.',
                'planned_start_date.required' => 'Please select a planned start date.',
                'planned_end_date.required' => 'Please select a planned end date.',
                'planned_end_date.after_or_equal' => 'The planned end date must be on or after the planned start date.',
                'actual_end_date.after_or_equal' => 'The actual end date must be on or after the actual start date.',
                'completion_percentage.max' => 'Progress cannot exceed 100%.',
                'status.in' => 'Please choose a valid phase status.',
            ]);

            // Determine status for new phase: default to pending (not_started),
            // but auto-complete if completion is 100%.
            DB::beginTransaction();

            $phase = ConstructionPhase::create([
                'project_id' => $validated['project_id'],
                'phase_name' => $validated['phase_name'],
                'phase_order' => $validated['phase_order'],
                'planned_start_date' => $validated['planned_start_date'],
                'planned_end_date' => $validated['planned_end_date'],
                'actual_start_date' => $validated['actual_start_date'] ?? null,
                'actual_end_date' => $validated['actual_end_date'] ?? null,
                'completion_percentage' => 0.00,
                'status' => 'not_started',
            ]);

            $this->logAction('Phase Created', "Phase '{$phase->phase_name}' created for project '{$project->project_name}'");

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                $phasePayload = [
                    'phase_id' => $phase->phase_id,
                    'project_id' => $phase->project_id,
                    'phase_name' => $phase->phase_name,
                    'phase_order' => (int) $phase->phase_order,
                    'planned_start_date' => $phase->planned_start_date ? \Illuminate\Support\Carbon::parse($phase->planned_start_date)->format('M d, Y') : null,
                    'planned_start_date_raw' => $phase->planned_start_date ? \Illuminate\Support\Carbon::parse($phase->planned_start_date)->toDateString() : null,
                    'planned_end_date' => $phase->planned_end_date ? \Illuminate\Support\Carbon::parse($phase->planned_end_date)->format('M d, Y') : null,
                    'planned_end_date_raw' => $phase->planned_end_date ? \Illuminate\Support\Carbon::parse($phase->planned_end_date)->toDateString() : null,
                    'actual_start_date' => $phase->actual_start_date ? \Illuminate\Support\Carbon::parse($phase->actual_start_date)->format('M d, Y') : null,
                    'actual_start_date_raw' => $phase->actual_start_date ? \Illuminate\Support\Carbon::parse($phase->actual_start_date)->toDateString() : null,
                    'actual_end_date' => $phase->actual_end_date ? \Illuminate\Support\Carbon::parse($phase->actual_end_date)->format('M d, Y') : null,
                    'actual_end_date_raw' => $phase->actual_end_date ? \Illuminate\Support\Carbon::parse($phase->actual_end_date)->toDateString() : null,
                    'completion_percentage' => (float) $phase->completion_percentage,
                    'status' => $phase->status,
                    'project_name' => optional($project)->project_name ?? null,
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'The construction phase has been created successfully and is currently marked as Pending.',
                    'phase' => $phasePayload,
                    'redirect' => route('admin.phases', ['project_id' => $project->project_id]),
                ], 200);
            }

            return redirect()
                ->route('admin.phases', ['project_id' => $project->project_id])
                ->with('success', 'The construction phase has been created successfully and is currently marked as Pending.')
                ->with('success_title', 'Phase Created Successfully');
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please correct the highlighted fields.',
                    'errors' => $e->errors(),
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Phase creation failed: ' . $e->getMessage());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create phase. Please try again.',
                ], 500);
            }

            return back()->withErrors(['message' => 'Failed to create phase'])->withInput();
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

        try {
            $validated = $request->validate([
                'phase_name' => 'required|string|max:200',
                'phase_order' => ['required', 'integer', 'min:1', function ($attribute, $value, $fail) use ($project, $phase) {
                    $exists = ConstructionPhase::query()
                        ->where('project_id', $project->project_id)
                        ->where('phase_order', $value)
                        ->where('phase_id', '!=', $phase->phase_id)
                        ->exists();

                    if ($exists) {
                        $fail('A phase with this order already exists for this project.');
                    }
                }],
                'planned_start_date' => 'required|date',
                'planned_end_date' => 'required|date|after_or_equal:planned_start_date',
                'actual_start_date' => 'nullable|date',
                'actual_end_date' => 'nullable|date|after_or_equal:actual_start_date',
                'completion_percentage' => 'nullable|numeric|min:0|max:100',
                'status' => 'required|in:not_started,in_progress,completed,delayed',
            ], [
                'phase_name.required' => 'Please enter a phase name.',
                'phase_order.required' => 'Please enter a phase order.',
                'planned_start_date.required' => 'Please select a planned start date.',
                'planned_end_date.required' => 'Please select a planned end date.',
                'planned_end_date.after_or_equal' => 'The planned end date must be on or after the planned start date.',
                'actual_end_date.after_or_equal' => 'The actual end date must be on or after the actual start date.',
                'completion_percentage.max' => 'Progress cannot exceed 100%.',
                'status.in' => 'Please choose a valid phase status.',
            ]);

            $normalizedSubmittedValues = [
                'phase_name' => trim((string) ($validated['phase_name'] ?? '')),
                'phase_order' => (int) ($validated['phase_order'] ?? 0),
                'planned_start_date' => $validated['planned_start_date'] ? \Illuminate\Support\Carbon::parse($validated['planned_start_date'])->toDateString() : null,
                'planned_end_date' => $validated['planned_end_date'] ? \Illuminate\Support\Carbon::parse($validated['planned_end_date'])->toDateString() : null,
                'actual_start_date' => !empty($validated['actual_start_date']) ? \Illuminate\Support\Carbon::parse($validated['actual_start_date'])->toDateString() : null,
                'actual_end_date' => !empty($validated['actual_end_date']) ? \Illuminate\Support\Carbon::parse($validated['actual_end_date'])->toDateString() : null,
                'completion_percentage' => (float) ($phase->completion_percentage ?? 0),
                'status' => (string) ($validated['status'] ?? ''),
            ];

            $normalizedCurrentValues = [
                'phase_name' => trim((string) $phase->phase_name),
                'phase_order' => (int) $phase->phase_order,
                'planned_start_date' => $phase->planned_start_date ? \Illuminate\Support\Carbon::parse($phase->planned_start_date)->toDateString() : null,
                'planned_end_date' => $phase->planned_end_date ? \Illuminate\Support\Carbon::parse($phase->planned_end_date)->toDateString() : null,
                'actual_start_date' => $phase->actual_start_date ? \Illuminate\Support\Carbon::parse($phase->actual_start_date)->toDateString() : null,
                'actual_end_date' => $phase->actual_end_date ? \Illuminate\Support\Carbon::parse($phase->actual_end_date)->toDateString() : null,
                'completion_percentage' => (float) $phase->completion_percentage,
                'status' => (string) $phase->status,
            ];

            if ($normalizedSubmittedValues === $normalizedCurrentValues) {
                throw ValidationException::withMessages([
                    'phase_name' => ['No changes were made. Update at least one field before saving.'],
                ]);
            }

            $submittedStatus = $normalizedSubmittedValues['status'] ?? '';
            $phaseProgress = round((float) ($phase->completion_percentage ?? 0), 2);
            $finalStatus = $submittedStatus;

            if ($phaseProgress >= 100) {
                $finalStatus = 'completed';
            }

            if ($phase->status === 'completed' && $finalStatus !== 'completed') {
                throw ValidationException::withMessages([
                    'status' => ['Completed phases cannot be reverted to another status.'],
                ]);
            }

            if ($submittedStatus === 'completed' && $phaseProgress < 100) {
                throw ValidationException::withMessages([
                    'status' => ['Cannot mark phase as completed unless progress is 100%.'],
                ]);
            }

            $allowedTransitions = [
                'not_started' => ['not_started', 'in_progress'],
                'in_progress' => ['in_progress', 'delayed', 'completed'],
                'delayed' => ['delayed', 'in_progress', 'completed'],
                'completed' => ['completed'],
            ];

            $effectiveRequestedStatus = $phaseProgress >= 100 ? 'completed' : $submittedStatus;
            if ($phase->status !== 'completed') {
                $allowed = $allowedTransitions[$phase->status] ?? [$phase->status];
                if ($phase->status === 'not_started' && !in_array($effectiveRequestedStatus, ['not_started', 'in_progress'], true)) {
                    throw ValidationException::withMessages([
                        'status' => ['Pending phases can only transition to In Progress.'],
                    ]);
                }

                if ($phase->status !== 'not_started' && !in_array($effectiveRequestedStatus, $allowed, true) && $effectiveRequestedStatus !== 'completed') {
                    throw ValidationException::withMessages([
                        'status' => ['The selected status transition is not allowed for this phase.'],
                    ]);
                }
            }

            $validated['status'] = $finalStatus;

            DB::beginTransaction();

            $oldStatus = $phase->status;
            $oldCompletion = $phase->completion_percentage;

            $phase->fill($validated);

            if ($phase->status !== 'completed' && $finalStatus === 'in_progress' && empty($phase->actual_start_date)) {
                $phase->actual_start_date = now()->toDateString();
            }

            if ($finalStatus === 'completed' && empty($phase->actual_end_date) && $phaseProgress >= 100) {
                $phase->actual_end_date = now()->toDateString();
            }

            $phase->save();

            if ($oldStatus !== $phase->status) {
                $this->logAction('Phase Status Changed', "Phase '{$phase->phase_name}' status changed from {$oldStatus} to {$phase->status}");
            }

            if ($oldCompletion !== $phase->completion_percentage) {
                $this->logAction('Phase Completion Updated', "Phase '{$phase->phase_name}' completion changed from {$oldCompletion}% to {$phase->completion_percentage}%");

                try {
                    if ($project && $project->client_id) {
                        NotificationService::notifyClient($project->client_id, [
                            'type' => 'phase',
                            'title' => 'Project Progress Updated',
                            'message' => "{$phase->phase_name} progress changed to {$phase->completion_percentage}%.",
                            'data' => ['module' => 'client.reports', 'phase_id' => $phase->phase_id, 'project_id' => $project->project_id],
                            'related_id' => $phase->phase_id,
                            'related_type' => 'phase',
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Failed to notify client on phase completion update: ' . $e->getMessage());
                }
            }

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                $phasePayload = [
                    'phase_id' => $phase->phase_id,
                    'project_id' => $phase->project_id,
                    'phase_name' => $phase->phase_name,
                    'phase_order' => (int) $phase->phase_order,
                    'planned_start_date' => $phase->planned_start_date ? \Illuminate\Support\Carbon::parse($phase->planned_start_date)->format('M d, Y') : null,
                    'planned_start_date_raw' => $phase->planned_start_date ? \Illuminate\Support\Carbon::parse($phase->planned_start_date)->toDateString() : null,
                    'planned_end_date' => $phase->planned_end_date ? \Illuminate\Support\Carbon::parse($phase->planned_end_date)->format('M d, Y') : null,
                    'planned_end_date_raw' => $phase->planned_end_date ? \Illuminate\Support\Carbon::parse($phase->planned_end_date)->toDateString() : null,
                    'actual_start_date' => $phase->actual_start_date ? \Illuminate\Support\Carbon::parse($phase->actual_start_date)->format('M d, Y') : null,
                    'actual_start_date_raw' => $phase->actual_start_date ? \Illuminate\Support\Carbon::parse($phase->actual_start_date)->toDateString() : null,
                    'actual_end_date' => $phase->actual_end_date ? \Illuminate\Support\Carbon::parse($phase->actual_end_date)->format('M d, Y') : null,
                    'actual_end_date_raw' => $phase->actual_end_date ? \Illuminate\Support\Carbon::parse($phase->actual_end_date)->toDateString() : null,
                    'completion_percentage' => (float) $phase->completion_percentage,
                    'status' => $phase->status,
                    'project_name' => optional($project)->project_name ?? null,
                ];

                $autoCompleted = ($oldCompletion < 100 && $phase->completion_percentage >= 100);

                return response()->json([
                    'success' => true,
                    'message' => 'Phase updated successfully.',
                    'phase' => $phasePayload,
                    'auto_completed' => $autoCompleted,
                    'redirect' => route('admin.phases', ['project_id' => $project->project_id]),
                ], 200);
            }

            return redirect()
                ->route('admin.phases', ['project_id' => $project->project_id])
                ->with('success', 'Phase updated successfully');
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please correct the highlighted fields.',
                    'errors' => $e->errors(),
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Phase update failed: ' . $e->getMessage());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update phase. Please try again.',
                ], 500);
            }

            return back()->withErrors(['message' => 'Failed to update phase'])->withInput();
        }
    }

    /**
     * Delete a phase
     */
    public function destroy(Request $request, $projectId, $phaseId)
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

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Phase deleted successfully.',
                    'redirect' => route('admin.phases', ['project_id' => $project->project_id]),
                ], 200);
            }

            return redirect()
                ->route('admin.phases', ['project_id' => $project->project_id])
                ->with('success', 'Phase deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Phase deletion failed: ' . $e->getMessage());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete phase. Please try again.',
                ], 500);
            }

            return back()->withErrors(['message' => 'Failed to delete phase']);
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
