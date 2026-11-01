# Appendix A - Software Source Code

This appendix presents selected source code excerpts from the Construction Project Service and Operation Management System for D&G Dev’t Corp. The listings illustrate the core logic for role-based access control, accomplishment report submission and approval, construction progress calculation, camera-assisted attendance and timekeeping, material inventory and usage, project worker allocation, management dashboard data aggregation, and client access restrictions. Each excerpt identifies its source file and line range to support traceability between the documented functionality and its implementation. The selection focuses on the application’s principal business rules and data operations rather than reproducing the entire codebase.

Selected source excerpts, prepared October 31, 2026. Line estimates are guides, not quotas; these listings retain only central business rules and data operations.

Each listing reproduces the cited source lines with common indentation removed. Separate listings are separate excerpts. Imports, setup variables, surrounding conditions, transaction handling, method endings, and responses may be omitted. Listings are documentary excerpts, not standalone executable programs. Line references describe this source snapshot.

## A.1 Role-Based Access Control

The following source code implements role-based access control by checking whether the signed-in account is active and whether its assigned role is permitted to access the requested route. Accounts that do not satisfy these conditions are redirected to the login page. This mechanism supports the separation of management, supervisor, and client functions within the system.

Source: `app/Http/Middleware/RoleMiddleware.php`, lines 19-37 (excerpt).

```php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    // If the user isn't logged in, or doesn't match the required role, boot them out
    $user = $request->user();
    if (!$user || !$user->is_active) {
        return redirect('/login')->with('error', 'Unauthorized access.');
    }

    $allowedRoles = array_values(array_filter(array_map(
        fn ($value) => trim(strtolower($value)),
        $roles
    )));

    if (! in_array(strtolower((string) $user->role), $allowedRoles, true)) {
        return redirect('/login')->with('error', 'Unauthorized access.');
    }

    return $next($request);
}
```

## A.2 Accomplishment Report Submission and Approval

The following excerpts illustrate the submission and approval workflow for accomplishment reports. The system validates report details, checks the supervisor's project access, and stores submitted reports with a pending approval status. Authorized management users can approve reports, control their publication to clients, and update stored phase completion information when an accomplishment percentage is provided. Image preparation and other surrounding processing are omitted from these listings.

Source: `app/Http/Controllers/ReportController.php`, lines 399-421 (excerpt).

```php
$validated = $request->validate([
    'project_id' => 'required|exists:projects,project_id',
    'phase_id' => 'required|exists:construction_phases,phase_id',
    'report_date' => 'required|date',
    'report_text' => 'required|string|max:5000',
    'accomplishment_percentage' => 'nullable|numeric|min:0|max:100',
    'site_images' => 'nullable|array|max:20',
    'site_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
    'attachments' => 'nullable|array|max:10',
    'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
]);

$project = Project::query()->notArchived()->findOrFail($validated['project_id']);
$phase = ConstructionPhase::findOrFail($validated['phase_id']);

if ($phase->status === 'completed' || (float) ($phase->completion_percentage ?? 0) >= 100) {
    if ($request->wantsJson() || $request->ajax()) {
        return response()->json(['success' => false, 'message' => 'This construction phase is already completed and cannot accept new reports.'], 422);
    }
    return back()->withErrors(['phase_id' => 'This construction phase is already completed and cannot accept new reports.'])->withInput();
}

$this->authorizeSupervisor($project);
```

Source: `app/Http/Controllers/ReportController.php`, lines 426-451 (excerpt).

```php
$images = [];
if ($request->hasFile('site_images')) {
    foreach ($request->file('site_images') as $image) {
        $path = $image->store('reports', 'public');
        $images[] = $path;
    }
}

$report = Report::create([
    'project_id' => $validated['project_id'],
    'phase_id' => $validated['phase_id'],
    'submitted_by' => auth('web')->user()->user_id,
    'report_date' => $validated['report_date'],
    'report_text' => $validated['report_text'],
    'site_images' => $images,
    'attachments' => $this->storeAttachments($request),
    'approval_status' => 'pending',
    'accomplishment_percentage' => $request->filled('accomplishment_percentage')
        ? round(min(100, max(0, (float) $request->input('accomplishment_percentage'))), 2)
        : null,
    'is_published_to_client' => false,
    'admin_report_text' => null,
    'admin_site_images' => null,
    'admin_explanation' => null,
    'published_at' => null,
]);
```

Source: `app/Http/Controllers/ReportController.php`, lines 102-107 (excerpt).

```php
$report = Report::findOrFail($reportId);

// Only administrators can approve
if (! in_array(auth('web')->user()->role, ['engineer', 'admin', 'administrator'], true)) {
    abort(403, 'Only administrators can approve reports');
}
```

Source: `app/Http/Controllers/ReportController.php`, lines 193-226 (excerpt).

```php
$updateData = [
    'approval_status' => 'approved',
    'reviewed_by' => auth('web')->user()->user_id,
    'reviewed_at' => now(),
    'approved_by' => auth('web')->user()->user_id,
    'approved_at' => now(),
    'approval_remarks' => $validated['approval_remarks'] ?? null,
    'accomplishment_percentage' => $approvedProgress,
    'is_published_to_client' => $publishToClient,
    'admin_report_text' => $validated['admin_report_text'] ?? $report->admin_report_text,
    'admin_site_images' => !empty($finalAdminImages) ? $finalAdminImages : null,
    'site_images' => !empty($remainingSiteImages) ? $remainingSiteImages : null,
    'admin_explanation' => $validated['admin_explanation'] ?? $report->admin_explanation,
    'published_at' => $publishToClient ? now() : null,
];

// If re-approving, don't change rejected_at or reviewed_at if they exist
if ($isReApproval) {
    unset($updateData['reviewed_at'], $updateData['approved_at']);
    $updateData['reviewed_by'] = auth('web')->user()->user_id;
    $updateData['approved_by'] = auth('web')->user()->user_id;
}

$removedImageFiles = $this->applyImageRemovals($report, $request, $updateData);
$report->update($updateData);

if ($report->phase && $request->filled('accomplishment_percentage')) {
    $newPhasePercentage = round(min(100, max(0, (float) $request->input('accomplishment_percentage'))), 2);
    $report->phase->update(['completion_percentage' => $newPhasePercentage]);

    if ($newPhasePercentage >= 100 && $report->phase->status !== 'completed') {
        $report->phase->update(['status' => 'completed']);
    }
}
```

## A.3 Construction Phase and Progress Calculation

The following source code determines the progress percentage of a construction phase. An administrative progress override takes priority when present; otherwise, progress is calculated from the proportion of completed milestones. When milestone information is unavailable or the phase has no milestones, the system uses its stored completion percentage. This calculation supplies progress information for project monitoring and timeline displays.

Source: `app/Models/ConstructionPhase.php`, lines 156-177 (excerpt).

```php
public function getProgressPercentageAttribute(): float
{
    if ($this->admin_progress_override !== null && $this->admin_progress_override !== '') {
        return (float) $this->admin_progress_override;
    }

    if (!$this->relationLoaded('milestones') && !Schema::hasTable('timeline_milestones')) {
        return (float) $this->getRawOriginal('completion_percentage', 0);
    }

    $milestones = $this->relationLoaded('milestones')
        ? $this->milestones
        : $this->milestones()->get(['milestone_id', 'is_completed']);

    $total = $milestones->count();

    if ($total === 0) {
        return (float) $this->getRawOriginal('completion_percentage', 0);
    }

    return round(($milestones->where('is_completed', true)->count() / $total) * 100, 2);
}
```

## A.4 Camera-Assisted Attendance and Timekeeping

The following excerpts present the principal controls for camera-assisted attendance and timekeeping. Face matches must meet the configured threshold and ambiguity requirements, and the supervisor must confirm the worker's identity before attendance is saved. A short-lived, single-use verification proof links the match to the worker and supervisor, while attendance rules prevent duplicate Time In records and restrict invalid Time Out transactions. The camera process assists supervisor verification and does not provide certified liveness detection.

Source: `app/Http/Controllers/FaceAttendanceController.php`, lines 185-204 (excerpt).

```php
$top = $scores[0] ?? null;
if (! $top || $top['score'] < $threshold) {
    throw new RuntimeException('Face not recognized. Retry in good lighting or use Manual Log.');
}
if (isset($scores[1]) && config('face_attendance.ambiguity_margin') > $top['score'] - $scores[1]['score']) {
    throw new RuntimeException('Match is uncertain between workers. Rescan or use Manual Log.');
}
$worker = $this->assignedWorker($top['worker_id']);
$this->markAttempt($attempt->id, 'matched', $top['score'], $worker->worker_id);
$request->session()->put('worker_attendance_face_proof', [
    'worker_id' => (int) $worker->worker_id, 'supervisor_id' => (int) Auth::id(),
    'verified_at' => now()->timestamp, 'attempt_id' => (int) $attempt->id,
    'provider' => 'local_supervised',
]);

return response()->json([
    'success' => true, 'purpose' => 'attendance', 'worker' => $this->profile($worker),
    'match_score' => $top['score'], 'needs_supervisor_confirmation' => true,
    'log_date' => now('Asia/Manila')->toDateString(),
])->header('Cache-Control', 'no-store, private');
```

Source: `app/Http/Controllers/SupervisorController.php`, lines 517-522 (excerpt).

```php
$action = $validated['action'] ?? 'time_in';
$manualMode = $request->boolean('manual');
$verificationMethod = $manualMode ? 'manual' : ($validated['verification_method'] ?? 'passkey');
if ($verificationMethod === 'face' && (!$request->boolean('supervisor_confirmed_identity') || !config('face_attendance.enabled'))) {
    return response()->json(['message' => 'Confirm the worker in person before saving camera attendance.'], 422);
}
```

Source: `app/Http/Controllers/SupervisorController.php`, lines 580-596 (excerpt).

```php
// pull() consumes the authorization; it cannot be replayed for other workers.
$proofKey = $verificationMethod === 'face'
    ? 'worker_attendance_face_proof'
    : 'worker_attendance_passkey_proof';
$proof = $request->session()->pull($proofKey);
$proofTime = is_array($proof) ? (int) ($proof['verified_at'] ?? 0) : 0;
if (! is_array($proof)
    || (int) ($proof['worker_id'] ?? 0) !== (int) $worker->worker_id
    || (int) ($proof['supervisor_id'] ?? 0) !== (int) Auth::id()
    || ($verificationMethod === 'face' && ($proof['provider'] ?? null) !== 'local_supervised')
    || $proofTime <= 0
    || $proofTime > $now->timestamp + 5
    || ($now->timestamp - $proofTime) > 120) {
    return response()->json([
        'message' => 'No recent verified '.($verificationMethod === 'face' ? 'facial identity' : 'passkey').' for this worker account. Scan again, or use Manual Log.',
    ], 403);
}
```

Source: `app/Http/Controllers/SupervisorController.php`, lines 658-694 (excerpt).

```php
if ($existingLog && $action === 'time_in' && ! $manualMode) {
    return response()->json([
        'message' => 'This worker already has a Time In record for the selected day.',
    ], 409);
}

if (! $existingLog) {
    $timeInValue = $requestedTimeIn ?? $now->format('H:i:s');
    $scheduleStart = data_get($worker, 'schedule_start') ?: '07:00:00';
    $status = $this->computeTimeInStatus($timeInValue, $scheduleStart);

    $attendanceData = [
        'project_id' => $deployment->project_id ?? null,
        'worker_id' => $validated['worker_id'],
        'deployment_id' => $deployment->deployment_id ?? null,
        'recorded_by' => Auth::id(),
        'log_date' => $date,
        'time_in' => $timeInValue,
        'break_out' => $manualMode && $requestedBreakOut ? $requestedBreakOut : null,
        'break_in' => $manualMode && $requestedBreakIn ? $requestedBreakIn : null,
        'time_out' => $manualMode && $requestedTimeOut ? $requestedTimeOut : null,
        'status' => $status,
        'remarks' => trim(
            ($manualMode
                ? 'Manual Time In by supervisor. Reason: '.$manualReason.'.'
                : ($verificationMethod === 'face'
                    ? 'Local camera face similarity matched; supervisor confirmed person. No certified liveness verification.'
                    : 'Worker ACCOUNT passkey verified; supervisor confirmation required. Device authentication is not worker face/fingerprint matching.'))
            .' '.($status === 'late'
                ? 'Late time-in by '.$this->lateMinutes($timeInValue, $scheduleStart).' minute(s).'
                : 'Present. Time-in was on or before the scheduled start.')
        ),
        // A platform passkey proves access to an account credential,
        // not a worker face/fingerprint match on a shared phone.
        'biometric_matched' => 0, // Camera-assisted supervisor verification is not independently trusted biometrics.
        'created_at' => $now,
    ];
```

Source: `app/Http/Controllers/SupervisorController.php`, lines 702-702 (excerpt).

```php
DB::table('attendance_logs')->insert($attendanceData);
```

Source: `app/Http/Controllers/SupervisorController.php`, lines 777-803 (excerpt).

```php
if ($action === 'time_out') {
    $scheduledEnd = data_get($worker, 'schedule_end') ?: '17:00:00';
    $timeOutAllowed = Carbon::parse($date.' '.$scheduledEnd);
    $timeOutAt = Carbon::parse($date.' '.($requestedTimeOut ?? $now->format('H:i:s')));

    if ((! $manualMode && $now->lt($timeOutAllowed)) || ($manualMode && $timeOutAt->lt($timeOutAllowed))) {
        return response()->json([
            'message' => 'Time out is only allowed from 5:00 PM onwards.',
        ], 422);
    }

    if (! $existingLog->time_in) {
        return response()->json([
            'message' => 'Cannot time out without time in.',
        ], 422);
    }

    if ($existingLog->time_out) {
        return response()->json([
            'message' => 'Time out is already recorded.',
        ], 422);
    }

    $updates['time_out'] = $requestedTimeOut ?? $now->format('H:i:s');
    $updates['overtime_minutes'] = $timeOutAt->gt($timeOutAllowed)
        ? $timeOutAllowed->diffInMinutes($timeOutAt)
        : 0;
```

## A.5 Material Inventory, Allocation, and Usage

The following excerpts demonstrate how the system approves material requests, updates project allocations, and records material consumption. Request approval checks available warehouse stock before deducting the approved quantity and updating the project's allocation. Usage recording checks the remaining project allocation and updates consumption records without deducting warehouse stock a second time. These operations support the tracking of materials allocated to and used by individual construction projects. Surrounding transaction setup and commit for request approval are omitted from these listings.

Source: `app/Http/Controllers/AdminDashboardController.php`, lines 2240-2261 (excerpt).

```php
if ($materialRequest->status !== 'pending') {
    return back()->with('error', 'This request has already been processed.');
}

$approvedQuantity = (float) ($request->input('approved_quantity', $materialRequest->requested_quantity));
$material = $materialRequest->material;

if ($approvedQuantity > (float) ($material->current_stock ?? 0)) {
    return back()->with('error', 'Insufficient stock. Available: '.(float) ($material->current_stock ?? 0).' '.($material->unit ?? 'unit').'.');
}

try {
    DB::beginTransaction();

    $material->current_stock = max(0, (float) $material->current_stock - $approvedQuantity);
    $material->save();

    $materialRequest->status = 'approved';
    $materialRequest->approved_quantity = $approvedQuantity;
    $materialRequest->reviewed_by = Auth::user()->user_id;
    $materialRequest->reviewed_at = now();
    $materialRequest->save();
```

Source: `app/Http/Controllers/AdminDashboardController.php`, lines 2276-2295 (excerpt).

```php
if (Schema::hasTable('project_materials')) {
    $projectMaterial = ProjectMaterial::query()
        ->where('project_id', $materialRequest->project_id)
        ->where('material_id', $material->id)
        ->first();

    if ($projectMaterial) {
        $projectMaterial->planned_quantity = (float) $projectMaterial->planned_quantity + $approvedQuantity;
        $projectMaterial->unit = $projectMaterial->unit ?? $materialRequest->unit ?? $material->unit ?? 'unit';
        $projectMaterial->save();
    } else {
        ProjectMaterial::create([
            'project_id' => $materialRequest->project_id,
            'material_id' => $material->id,
            'planned_quantity' => $approvedQuantity,
            'used_quantity' => 0,
            'unit' => $materialRequest->unit ?? $material->unit ?? 'unit',
        ]);
    }
}
```

Source: `app/Http/Controllers/SupervisorController.php`, lines 1973-2017 (excerpt).

```php
DB::transaction(function () use ($validated, $project, $material, $phase, $user, $request) {
    $inventoryRow = ProjectMaterial::query()
        ->where('project_id', $project->project_id)
        ->where('material_id', $material->id)
        ->lockForUpdate()
        ->first();

    if (! $inventoryRow) {
        throw ValidationException::withMessages(['quantity_used' => 'This material has no project allocation. Request an allocation before recording usage.']);
    }

    $totalLogged = (float) MaterialUsage::query()
        ->where('project_id', $project->project_id)
        ->where('material_id', $material->id)
        ->sum('quantity_used');
    $used = max((float) $inventoryRow->used_quantity, $totalLogged);
    $remainingAllocation = $this->normalizeRemaining((float) $inventoryRow->planned_quantity, $used);

    if ((float) $validated['quantity_used'] > $remainingAllocation) {
        throw ValidationException::withMessages(['quantity_used' => 'Insufficient project allocation. Remaining: '.number_format($remainingAllocation, 2).' '.($inventoryRow->unit ?? $material->unit).'.']);
    }

    // Warehouse stock was deducted when the materials were allocated.
    $inventoryRow->used_quantity = $used + (float) $validated['quantity_used'];
    $inventoryRow->unit = $inventoryRow->unit ?? $material->unit;
    $inventoryRow->save();

    $photoPath = null;
    if ($request->hasFile('site_photo')) {
        $photoPath = $request->file('site_photo')->store('material-usage-photos', 'public');
    }

    MaterialUsage::create([
        'project_id' => $project->project_id,
        'phase_id' => $phase->phase_id,
        'material_id' => $material->id,
        'quantity_used' => (float) $validated['quantity_used'],
        'unit' => $inventoryRow->unit,
        'usage_date' => $validated['usage_date'],
        'remarks' => $validated['remarks'] ?? null,
        'recorded_by' => $user->user_id,
        'site_photo_path' => $photoPath,
    ]);

});
```

## A.6 Project Worker Allocation

The following source code illustrates the allocation and transfer of workers to construction projects. Within a database transaction, the system locks the relevant records, validates worker activity and project availability, and checks for an existing active assignment. Transfers deactivate assignments to other projects, while new deployment records preserve the association of historical attendance with earlier assignments. The remaining audit log and closing transaction code are omitted from this excerpt.

Source: `app/Http/Controllers/WorkerAllocationController.php`, lines 81-116 (excerpt).

```php
private function saveAllocation(Request $request, array $workerIds, array $data): void
{
    DB::transaction(function () use ($request, $workerIds, $data) {
        // Lock in a stable order and validate the entire group before changing any deployments.
        $workers = Worker::query()->whereIn('worker_id', $workerIds)->orderBy('worker_id')->lockForUpdate()->get();
        abort_unless($workers->count() === count($workerIds), 404);
        if ($workers->contains(fn ($worker) => ! $worker->is_active)) {
            throw ValidationException::withMessages(['worker_ids' => 'Inactive workers cannot be allocated. Refresh the roster and select active workers.']);
        }
        $project = Project::query()->notArchived()->lockForUpdate()->find($data['project_id']);
        if (! $project) {
            throw ValidationException::withMessages(['project_id' => 'Choose a project that is not archived.']);
        }

        foreach ($workers as $worker) {
            $workerId = $worker->worker_id;
            if ($data['mode'] === 'transfer') {
                DB::table('project_workers')->where('worker_id', $workerId)
                    ->where('project_id', '!=', $project->project_id)->where('is_active', true)
                    ->update(['is_active' => false]);
            }

            $alreadyAssigned = DB::table('project_workers')->where('worker_id', $workerId)
                ->where('project_id', $project->project_id)->where('is_active', true)->exists();
            if (! $alreadyAssigned) {
                // Create a new deployment instead of moving historical attendance to a different project.
                $deployment = [
                    'worker_id' => $workerId, 'project_id' => $project->project_id,
                    'is_active' => true, 'created_at' => now(),
                ];
                foreach (['deployed_date', 'start_date'] as $column) {
                    if (Schema::hasColumn('project_workers', $column)) {
                        $deployment[$column] = now()->toDateString();
                    }
                }
                DB::table('project_workers')->insert($deployment);
```

## A.7 Management Dashboard Data Aggregation

The following excerpts show how the Management Dashboard obtains summary information from operational records. The queries count active projects, active workers, and material catalog records, while the progress calculation summarizes ongoing projects and separates ongoing and delayed project counts. These values support centralized project monitoring; the active workforce count represents registered active workers rather than daily attendance. The summary excerpt contains selected entries from a larger data array.

Source: `app/Http/Controllers/AdminDashboardController.php`, lines 52-60 (excerpt).

```php
$activeProjectsCount = $hasProjects
    ? DB::table('projects')
        ->whereIn('status', ['planning', 'ongoing'])
        ->count()
    : 0;

$totalProjectsCount = $hasProjects
    ? DB::table('projects')->count()
    : 0;
```

Source: `app/Http/Controllers/AdminDashboardController.php`, lines 79-92 (excerpt).

```php
'total_workforce' => Schema::hasTable('workers')
    ? DB::table('workers')
        ->where('is_active', true)
        ->count()
    : 0,

'pending_reports' => $hasReportsTable
    ? DB::table('accomplishment_reports')
        ->count()
    : 0,

'inventory_count' => Schema::hasTable('materials')
    ? DB::table('materials')->count()
    : 0,
```

Source: `app/Http/Controllers/AdminDashboardController.php`, lines 168-192 (excerpt).

```php
$allActiveProjects = Project::with($phaseRelations)
    ->whereIn('status', Project::statusVariants(Project::STATUS_ONGOING))
    ->get();

$overallProgress['total'] = $allActiveProjects->count();

if ($overallProgress['total'] > 0) {
    $totalProgress = $allActiveProjects->reduce(function ($carry, $project) {
        $phases = $project->phases;

        return $carry + ($phases->isNotEmpty()
            ? $phases->avg('progress_percentage')
            : 0);
    }, 0);

    $overallProgress['percentage'] = round($totalProgress / $overallProgress['total'], 2);

    $delayedProjects = $allActiveProjects->filter(fn ($project) => $delayedProjectIds->contains($project->project_id));

    $overallProgress['ongoing'] = $overallProgress['total'] - $delayedProjects->count();
    $overallProgress['delayed'] = $delayedProjects->count();
    $overallProgress['ongoing_progress'] = round($allActiveProjects
        ->reject(fn ($project) => $delayedProjectIds->contains($project->project_id))
        ->sum(fn ($project) => (float) $project->progress_percentage) / $overallProgress['total'], 2);
}
```

## A.8 Client Project Access Restrictions

The following excerpts demonstrate the access restrictions applied to client project information and report downloads. Before returning a project snapshot, the system verifies that the project belongs to the signed-in client's account. Report downloads are further restricted to records that belong to the client's projects and have been both approved and published for client viewing. These checks limit access to authorized project information. PDF rendering and response handling are omitted from these listings.

Source: `app/Http/Controllers/ClientController.php`, lines 561-571 (excerpt).

```php
public function dashboardProjectSnapshot(Project $project)
{
    $user = Auth::user();
    $client = $user?->client;

    if (! $client || $project->client_id !== $client->client_id) {
        abort(403);
    }

    return response()->json($this->buildProjectSnapshot($project));
}
```

Source: `app/Http/Controllers/ClientController.php`, lines 934-949 (excerpt).

```php
public function downloadReportPdf($reportId)
{
    $user = auth('web')->user();
    $client = $user?->client;

    if (! $client) {
        abort(403, 'Client account not found');
    }

    $report = Report::with(['project', 'phase', 'submittedBy', 'reviewedBy', 'approvedBy', 'phase.milestones'])
        ->where('report_id', $reportId)
        ->where('approval_status', 'approved')
        ->where('is_published_to_client', true)
        ->whereHas('project', function ($q) use ($client) {
            $q->where('client_id', $client->client_id);
        })->firstOrFail();
```

## Selected code length

Counts include blank lines and comments inside code listings, excluding headings and explanations.

| Appendix section | Code lines |
| --- | ---: |
| A.1 Role-Based Access Control | 19 |
| A.2 Accomplishment Report Submission and Approval | 89 |
| A.3 Construction Phase and Progress Calculation | 22 |
| A.4 Camera-Assisted Attendance and Timekeeping | 108 |
| A.5 Material Inventory, Allocation, and Usage | 87 |
| A.6 Project Worker Allocation | 36 |
| A.7 Management Dashboard Data Aggregation | 48 |
| A.8 Client Project Access Restrictions | 27 |
| **Total** | **436** |
