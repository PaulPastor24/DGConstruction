<div class="ps-section-card">
<div class="ps-section-head"><h6><i class="bi bi-people me-2"></i>Assigned workers</h6><span class="ps-readonly-pill">{{ $project->projectWorkers->count() }} assignments</span></div>
<div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Worker</th><th>Trade / Role</th><th>Contact</th><th>Assignment</th></tr></thead><tbody>
@forelse($project->projectWorkers as $assignment)
<tr><td>{{ $assignment->worker?->full_name ?? trim(($assignment->worker?->first_name ?? '').' '.($assignment->worker?->last_name ?? '')) ?: 'Worker unavailable' }}</td><td>{{ $assignment->assigned_role ?? $assignment->worker?->trade ?? 'General' }}</td><td>{{ $assignment->worker?->contact_number ?? 'Not provided' }}</td><td><span class="badge {{ $assignment->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $assignment->is_active ? 'Active' : 'Inactive' }}</span></td></tr>
@empty<tr><td colspan="4" class="text-muted py-3">No workers assigned to this project.</td></tr>@endforelse
</tbody></table></div></div>
<div class="ps-section-card">
<div class="ps-section-head"><h6><i class="bi bi-box-seam me-2"></i>Project materials</h6><a class="btn btn-sm btn-outline-success" href="{{ route('admin.inventory', ['project_id' => $project->project_id]) }}">Open inventory</a></div>
<p class="small text-muted">Project allocation and consumption. Select a material to see its usage records.</p>
<div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Material</th><th>Unit</th><th>Allocation</th><th>Used</th><th>Remaining</th></tr></thead><tbody>
@forelse($project->projectMaterials as $allocation)
@php
$usageRows = $project->materialUsages->where('material_id', $allocation->material_id);
$used = max((float) $allocation->used_quantity, (float) $usageRows->sum('quantity_used'));
@endphp
<tr><td><button type="button" class="btn btn-link text-success text-start p-0" data-project-material-detail="project-material-{{ $allocation->id }}">{{ $allocation->material?->name ?? 'Unavailable material' }}</button>
<template id="project-material-{{ $allocation->id }}">
<h5>{{ $allocation->material?->name }}</h5><p class="text-muted">{{ $project->project_name }} &middot; {{ $allocation->material?->category }}</p>
<div class="row g-3 mb-3"><div class="col-4"><small>Allocation</small><div class="fw-bold">{{ number_format($allocation->planned_quantity,2) }}</div></div><div class="col-4"><small>Used</small><div class="fw-bold">{{ number_format($used,2) }}</div></div><div class="col-4"><small>Remaining</small><div class="fw-bold">{{ number_format(max(0,$allocation->planned_quantity-$used),2) }} {{ $allocation->unit }}</div></div></div>
<h6>Usage history</h6><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Date</th><th>Phase</th><th>Quantity</th><th>Remarks</th></tr></thead><tbody>@forelse($usageRows->sortByDesc('usage_date') as $usage)<tr><td>{{ $usage->usage_date?->format('M d, Y') }}</td><td>{{ $usage->phase?->phase_name ?? 'Not specified' }}</td><td>{{ number_format($usage->quantity_used,2) }} {{ $usage->unit }}</td><td>{{ $usage->remarks ?? '?' }}</td></tr>@empty<tr><td colspan="4" class="text-muted">No usage recorded.</td></tr>@endforelse</tbody></table></div>
</template></td><td>{{ $allocation->unit }}</td><td>{{ number_format($allocation->planned_quantity,2) }}</td><td>{{ number_format($used,2) }}</td><td>{{ number_format(max(0,$allocation->planned_quantity-$used),2) }}</td></tr>
@empty<tr><td colspan="5" class="text-muted py-3">No materials allocated to this project.</td></tr>@endforelse
</tbody></table></div></div>
