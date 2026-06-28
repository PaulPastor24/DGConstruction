@extends('layouts.supervisor')

@section('title', 'Construction Phases - Supervisor View')
@section('page_title', 'Construction Phases')

@section('content')
<div class="page-card">
    <div class="page-hero">
        <div class="eyebrow">Site Delivery</div>
        <div class="page-title">Phase Management</div>
        <div class="page-subtitle">Track current construction phase milestones across the assigned project portfolio.</div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-4">
        <div class="section-card h-100">
            <div class="section-card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="eyebrow">Active Focus</div>
                        <div class="fw-bold fs-5">{{ $primaryProject->project_name ?? 'No project assigned' }}</div>
                    </div>
                    <span class="badge bg-success-subtle text-success-emphasis">{{ $primaryPhase->status ?? 'pending' }}</span>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small text-muted">Current phase</span>
                        <span class="small fw-bold">{{ $primaryPhase->phase_name ?? 'Pending setup' }}</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: {{ ($primaryPhase->completion_percentage ?? 0) }}%"></div>
                    </div>
                </div>
                <div class="small text-muted">{{ $primaryProject->project_location ?? 'Location pending' }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="section-card h-100">
            <div class="section-card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="eyebrow">Portfolio</div>
                        <div class="fw-bold fs-5">Assigned project phases</div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-3">
                    @forelse ($assignedProjects as $project)
                        <div class="border rounded-3 p-3">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $project->project_name }}</div>
                                    <div class="small text-muted">{{ $project->project_location ?? 'Location pending' }}</div>
                                </div>
                                <span class="badge bg-light text-muted">{{ $project->phases->count() }} phases</span>
                            </div>
                            <div class="mt-3 d-flex flex-column gap-2">
                                @foreach ($project->phases->take(3) as $phase)
                                    <div class="d-flex justify-content-between align-items-center bg-light rounded-2 p-2">
                                        <div>
                                            <div class="small fw-semibold">{{ $phase->phase_name }}</div>
                                            <div class="small text-muted">{{ $phase->status ?? 'pending' }}</div>
                                        </div>
                                        <div class="small text-muted">{{ (float) ($phase->completion_percentage ?? 0) }}%</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">No phase data is available for your assignment yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
