<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\NotificationService;
use App\Models\ConstructionPhase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PhaseController extends Controller
{
    private function validatePlannedSchedule(Project $project, ?int $phaseId, int $phaseOrder, string $plannedStartDate, string $plannedEndDate, ?int $dependsOnPhaseId, ?string $phaseName = null): void
    {
        $candidateStart = \Illuminate\Support\Carbon::parse($plannedStartDate);
        $candidateEnd = \Illuminate\Support\Carbon::parse($plannedEndDate);

        if ($dependsOnPhaseId) {
            $dependencyPhase = ConstructionPhase::query()
                ->where('project_id', $project->project_id)
                ->where('phase_id', $dependsOnPhaseId)
                ->first();

            if ($dependencyPhase && $dependencyPhase->planned_end_date) {
                $dependencyEnd = \Illuminate\Support\Carbon::parse($dependencyPhase->planned_end_date);
                if ($candidateStart->lt($dependencyEnd)) {
                    throw ValidationException::withMessages([
                        'planned_start_date' => ['This phase cannot begin before the selected dependency phase ends on ' . $dependencyEnd->format('M d, Y') . '.'],
                    ]);
                }
            }
        }

        $relativePhases = ConstructionPhase::query()
            ->where('project_id', $project->project_id)
            ->when($phaseId !== null, fn ($query) => $query->where('phase_id', '!=', $phaseId))
            ->orderBy('phase_order')
            ->get();

        foreach ($relativePhases as $relativePhase) {
            if ((int) $relativePhase->phase_order >= $phaseOrder) {
                continue;
            }

            if (!$relativePhase->planned_start_date || !$relativePhase->planned_end_date) {
                continue;
            }

            $existingStart = \Illuminate\Support\Carbon::parse($relativePhase->planned_start_date);
            $existingEnd = \Illuminate\Support\Carbon::parse($relativePhase->planned_end_date);

            if ($candidateStart->lt($existingEnd) && $candidateEnd->gt($existingStart)) {
                $phaseLabel = $phaseName ?: 'This phase';
                throw ValidationException::withMessages([
                    'planned_start_date' => [
                        $phaseLabel . ' overlaps with ' . $relativePhase->phase_name . ' scheduled from ' . $existingStart->format('M d, Y') . ' to ' . $existingEnd->format('M d, Y') . '.',
                    ],
                ]);
            }
        }
    }

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
                'depends_on_phase_id' => ['nullable', 'integer', 'exists:construction_phases,phase_id', function ($attribute, $value, $fail) use ($project, $request) {
                    if (!$value) {
                        return;
                    }

                    $dependencyPhase = ConstructionPhase::where('project_id', $project->project_id)
                        ->where('phase_id', $value)
                        ->first();

                    if (!$dependencyPhase) {
                        $fail('The dependency must belong to the selected project.');
                        return;
                    }

                    $selectedPhaseOrder = (int) $request->input('phase_order', 0);
                    if ($selectedPhaseOrder > 0 && (int) $dependencyPhase->phase_order >= $selectedPhaseOrder) {
                        $fail('A phase can only depend on an earlier phase in the project sequence.');
                    }
                }],
                'notes' => 'nullable|string|max:5000',
                'admin_progress_override' => 'nullable|numeric|min:0|max:100',
                'override_reason' => [
                    'nullable',
                    'string',
                    'max:2000',
                    function ($attribute, $value, $fail) use ($request): void {
                        if ($request->filled('admin_progress_override') && trim((string) $value) === '') {
                            $fail('Please provide a reason when setting a progress override.');
                        }
                    },
                ],
            ], [
                'phase_name.required' => 'Please enter a phase name.',
                'phase_order.required' => 'Please enter a phase order.',
                'planned_start_date.required' => 'Please select a planned start date.',
                'planned_end_date.required' => 'Please select a planned end date.',
                'planned_end_date.after_or_equal' => 'The planned end date must be on or after the planned start date.',
                'status.in' => 'Please choose a valid phase status.',
            ]);

            $this->validatePlannedSchedule(
                $project,
                null,
                (int) $validated['phase_order'],
                (string) $validated['planned_start_date'],
                (string) $validated['planned_end_date'],
                isset($validated['depends_on_phase_id']) && $validated['depends_on_phase_id'] !== null ? (int) $validated['depends_on_phase_id'] : null,
                (string) ($validated['phase_name'] ?? '')
            );

            // Determine status for new phase: default to pending (not_started),
            // but auto-complete if completion is 100%.
            DB::beginTransaction();

            $phaseData = [
                'project_id' => $validated['project_id'],
                'phase_name' => $validated['phase_name'],
                'phase_order' => $validated['phase_order'],
                'planned_start_date' => $validated['planned_start_date'],
                'planned_end_date' => $validated['planned_end_date'],
                'completion_percentage' => 0.00,
                'status' => 'not_started',
            ];
            if (Schema::hasColumn('construction_phases', 'admin_progress_override')) {
                $phaseData['admin_progress_override'] = $validated['admin_progress_override'] ?? null;
            }
            if (Schema::hasColumn('construction_phases', 'override_reason')) {
                $phaseData['override_reason'] = ($validated['admin_progress_override'] ?? null) !== null
                    ? trim((string) ($validated['override_reason'] ?? ''))
                    : null;
            }
            if (Schema::hasColumn('construction_phases', 'override_applied_at')) {
                $phaseData['override_applied_at'] = ($validated['admin_progress_override'] ?? null) !== null ? now() : null;
            }
            if (Schema::hasColumn('construction_phases', 'override_applied_by')) {
                $phaseData['override_applied_by'] = ($validated['admin_progress_override'] ?? null) !== null
                    ? auth('web')->user()->user_id
                    : null;
            }
            if (Schema::hasColumn('construction_phases', 'depends_on_phase_id')) {
                $phaseData['depends_on_phase_id'] = $validated['depends_on_phase_id'] ?? null;
            }
            if (Schema::hasColumn('construction_phases', 'notes')) {
                $phaseData['notes'] = $validated['notes'] ?? null;
            }
            $phase = ConstructionPhase::create($phaseData);
            $phase->syncStatusFromMilestones();
            $project->syncStatusFromPhases();

            $this->logAction('Phase Created', "Phase '{$phase->phase_name}' created for project '{$project->project_name}'");

            DB::commit();

            // Notify admins about new construction phase
            try {
                NotificationService::notifyAdmins([
                    'type' => 'phase',
                    'title' => 'New Construction Phase',
                    'message' => "Phase \"{$phase->phase_name}\" has been added to \"{$project->project_name}\".",
                    'data' => ['module' => 'admin.phases', 'phase_id' => $phase->phase_id, 'project_id' => $phase->project_id, 'project_name' => $project->project_name, 'recipient' => 'Admin'],
                    'related_id' => $phase->phase_id,
                    'related_type' => 'phase',
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to notify admins on phase creation: ' . $e->getMessage());
            }

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
                    'completion_percentage' => (float) $phase->progress_percentage,
                    'milestone_progress_summary' => $phase->milestone_progress_summary,
                    'status' => $phase->status,
                    'project_name' => optional($project)->project_name ?? null,
                    'admin_progress_override' => $phase->admin_progress_override !== null ? (float) $phase->admin_progress_override : null,
                    'override_reason' => $phase->override_reason ?? null,
                    'override_applied_at' => $phase->override_applied_at?->format('M d, Y h:i A'),
                    'override_applied_by' => $phase->override_applied_by,
                    'override_applied_by_name' => Schema::hasColumn('construction_phases', 'override_applied_by')
                        ? $phase->overrideAppliedBy?->name
                        : null,
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
                'status' => 'required|in:not_started,in_progress,completed,delayed',
                'actual_start_date' => 'nullable|date|after_or_equal:planned_start_date',
                'actual_end_date' => 'nullable|date|after_or_equal:actual_start_date',
                'depends_on_phase_id' => ['nullable', 'integer', 'exists:construction_phases,phase_id', function ($attribute, $value, $fail) use ($project, $phase) {
                    if ($value && (int) $value === (int) $phase->phase_id) {
                        $fail('A phase cannot depend on itself.');
                        return;
                    }

                    $dependencyPhase = $value
                        ? ConstructionPhase::where('project_id', $project->project_id)->where('phase_id', $value)->first()
                        : null;

                    if ($value && !$dependencyPhase) {
                        $fail('The dependency must belong to the selected project.');
                        return;
                    }

                    if ($dependencyPhase && (int) $dependencyPhase->phase_order >= (int) $phase->phase_order) {
                        $fail('A phase can only depend on an earlier phase in the project sequence.');
                        return;
                    }

                    if ($value && $phase->hasDependencyCycle((int) $value)) {
                        $fail('The selected dependency would create a circular phase workflow.');
                    }
                }],
                'delay_reason' => 'nullable|in:weather,materials,permits,design_change,labor,equipment,other',
                'delay_notes' => 'nullable|string|max:5000',
                'notes' => 'nullable|string|max:5000',
                'admin_progress_override' => 'nullable|numeric|min:0|max:100',
                'override_reason' => [
                    'nullable',
                    'string',
                    'max:2000',
                    function ($attribute, $value, $fail) use ($request): void {
                        if ($request->filled('admin_progress_override') && trim((string) $value) === '') {
                            $fail('Please provide a reason when setting a progress override.');
                        }
                    },
                ],
            ], [
                'phase_name.required' => 'Please enter a phase name.',
                'phase_order.required' => 'Please enter a phase order.',
                'planned_start_date.required' => 'Please select a planned start date.',
                'planned_end_date.required' => 'Please select a planned end date.',
                'planned_end_date.after_or_equal' => 'The planned end date must be on or after the planned start date.',
                'status.in' => 'Please choose a valid phase status.',
            ]);

            $this->validatePlannedSchedule(
                $project,
                (int) $phase->phase_id,
                (int) $validated['phase_order'],
                (string) $validated['planned_start_date'],
                (string) $validated['planned_end_date'],
                isset($validated['depends_on_phase_id']) && $validated['depends_on_phase_id'] !== null ? (int) $validated['depends_on_phase_id'] : null,
                (string) ($validated['phase_name'] ?? '')
            );

            $normalizedSubmittedValues = [
                'phase_name' => trim((string) ($validated['phase_name'] ?? '')),
                'phase_order' => (int) ($validated['phase_order'] ?? 0),
                'planned_start_date' => $validated['planned_start_date'] ? \Illuminate\Support\Carbon::parse($validated['planned_start_date'])->toDateString() : null,
                'planned_end_date' => $validated['planned_end_date'] ? \Illuminate\Support\Carbon::parse($validated['planned_end_date'])->toDateString() : null,
                'status' => (string) ($validated['status'] ?? ''),
                'admin_progress_override' => array_key_exists('admin_progress_override', $validated)
                    ? ($validated['admin_progress_override'] === null || $validated['admin_progress_override'] === '' ? null : (float) $validated['admin_progress_override'])
                    : ($phase->admin_progress_override !== null ? (float) $phase->admin_progress_override : null),
                'override_reason' => array_key_exists('override_reason', $validated)
                    ? trim((string) ($validated['override_reason'] ?? ''))
                    : trim((string) ($phase->override_reason ?? '')),
            ];

            $normalizedCurrentValues = [
                'phase_name' => trim((string) $phase->phase_name),
                'phase_order' => (int) $phase->phase_order,
                'planned_start_date' => $phase->planned_start_date ? \Illuminate\Support\Carbon::parse($phase->planned_start_date)->toDateString() : null,
                'planned_end_date' => $phase->planned_end_date ? \Illuminate\Support\Carbon::parse($phase->planned_end_date)->toDateString() : null,
                'status' => (string) $phase->status,
                'admin_progress_override' => $phase->admin_progress_override !== null ? (float) $phase->admin_progress_override : null,
                'override_reason' => trim((string) ($phase->override_reason ?? '')),
            ];

            if ($normalizedSubmittedValues === $normalizedCurrentValues) {
                throw ValidationException::withMessages([
                    'phase_name' => ['No changes were made. Update at least one field before saving.'],
                ]);
            }

            $submittedStatus = $normalizedSubmittedValues['status'] ?? '';
            $finalStatus = $submittedStatus;

            if ($submittedStatus === ConstructionPhase::STATUS_IN_PROGRESS && !$phase->isReadyToStart()) {
                throw ValidationException::withMessages([
                    'status' => ['This phase cannot start until its dependency is completed.'],
                ]);
            }

            if ($phase->status === 'completed' && $submittedStatus !== 'completed') {
                throw ValidationException::withMessages([
                    'status' => ['Completed phases cannot be reverted to another status.'],
                ]);
            }

            if ($phase->progress_percentage >= 100 && $submittedStatus !== 'completed') {
                $finalStatus = 'completed';
            }

            $milestonesComplete = !Schema::hasTable('timeline_milestones')
                || !$phase->milestones()->where('is_completed', false)->exists();
            if ($submittedStatus === ConstructionPhase::STATUS_COMPLETED && !$milestonesComplete) {
                throw ValidationException::withMessages([
                    'status' => ['Cannot complete this phase until all milestones are completed.'],
                ]);
            }

            if ($submittedStatus === ConstructionPhase::STATUS_DELAYED && empty($validated['delay_reason'])) {
                throw ValidationException::withMessages([
                    'delay_reason' => ['Please select a reason for delaying this phase.'],
                ]);
            }

            $allowedTransitions = [
                'not_started' => ['not_started', 'in_progress'],
                'in_progress' => ['in_progress', 'delayed', 'completed'],
                'delayed' => ['delayed', 'in_progress', 'completed'],
                'completed' => ['completed'],
            ];

            $effectiveRequestedStatus = $finalStatus;
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
            $validated['depends_on_phase_id'] = $validated['depends_on_phase_id'] ?? null;
            if (array_key_exists('admin_progress_override', $validated)) {
                $overrideValue = $validated['admin_progress_override'];
                if ($overrideValue !== null && $overrideValue !== '') {
                    $validated['override_reason'] = trim((string) ($validated['override_reason'] ?? ''));
                    $validated['override_applied_at'] = now();
                    $validated['override_applied_by'] = auth('web')->user()->user_id;
                } else {
                    $validated['admin_progress_override'] = null;
                    $validated['override_reason'] = null;
                    $validated['override_applied_at'] = null;
                    $validated['override_applied_by'] = null;
                }
            }
            if ($finalStatus === ConstructionPhase::STATUS_IN_PROGRESS && empty($validated['actual_start_date'])) {
                $validated['actual_start_date'] = now()->toDateString();
            }
            if ($finalStatus === ConstructionPhase::STATUS_COMPLETED && empty($validated['actual_end_date'])) {
                $validated['actual_end_date'] = now()->toDateString();
            }
            if ($finalStatus !== ConstructionPhase::STATUS_DELAYED) {
                $validated['delay_reason'] = null;
                $validated['delay_notes'] = null;
            }

            $optionalPhaseColumns = ['actual_start_date', 'actual_end_date', 'delay_reason', 'delay_notes', 'depends_on_phase_id', 'notes', 'admin_progress_override', 'override_reason', 'override_applied_at', 'override_applied_by'];
            foreach ($optionalPhaseColumns as $optionalColumn) {
                if (!Schema::hasColumn('construction_phases', $optionalColumn)) {
                    unset($validated[$optionalColumn]);
                }
            }

            DB::beginTransaction();

            $oldStatus = $phase->status;

            $phase->fill($validated);

            $phase->save();
            $phase->syncStatusFromMilestones();
            $project->syncStatusFromPhases();

            if ($oldStatus !== $phase->status) {
                $this->logAction('Phase Status Changed', "Phase '{$phase->phase_name}' status changed from {$oldStatus} to {$phase->status}");

                if ($phase->status === 'delayed' && $oldStatus !== 'delayed') {
                    try {
                        NotificationService::notifyAdmins([
                            'type' => 'phase',
                            'title' => 'Phase Delayed',
                            'message' => "Phase '{$phase->phase_name}' in project '{$project->project_name}' has been marked delayed.",
                            'data' => [
                                'module' => 'admin.timeline',
                                'phase_id' => $phase->phase_id,
                                'project_id' => $project->project_id,
                                'project_name' => $project->project_name,
                                'recipient' => 'Admin',
                            ],
                            'related_id' => $phase->phase_id,
                            'related_type' => 'phase',
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('Failed to notify admin on phase delay: ' . $e->getMessage());
                    }
                }

                if ($phase->status === 'completed' && $oldStatus !== 'completed') {
                    try {
                        if ($project && $project->client_id) {
                            NotificationService::notifyClient($project->client_id, [
                                'type' => 'phase',
                                'title' => 'Construction Phase Completed',
                                'message' => "The '{$phase->phase_name}' phase has been completed for project '{$project->project_name}'.",
                                'data' => ['module' => 'client.timeline', 'phase_id' => $phase->phase_id, 'project_id' => $project->project_id],
                                'related_id' => $phase->phase_id,
                                'related_type' => 'phase',
                            ]);
                        }
                    } catch (\Throwable $e) {
                        Log::error('Failed to notify client on phase completion: ' . $e->getMessage());
                    }
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
                    'completion_percentage' => (float) $phase->progress_percentage,
                    'status' => $phase->status,
                    'project_name' => optional($project)->project_name ?? null,
                    'admin_progress_override' => $phase->admin_progress_override ?? null,
                    'override_reason' => $phase->override_reason ?? null,
                    'override_applied_at' => $phase->override_applied_at?->format('M d, Y h:i A'),
                    'override_applied_by' => $phase->override_applied_by,
                    'override_applied_by_name' => Schema::hasColumn('construction_phases', 'override_applied_by')
                        ? $phase->overrideAppliedBy?->name
                        : null,
                ];

                $autoCompleted = ($oldStatus !== 'completed' && $phase->status === 'completed' && $finalStatus !== $submittedStatus);

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