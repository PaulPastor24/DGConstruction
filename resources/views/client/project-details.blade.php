@extends('layouts.client')

@section('title', $project->project_name . ' - Client Portal')

@section('content')
@php
    $detailLocation = trim((string) ($project->project_location ?? $project->location ?? $project->location_address ?? ''));
@endphp
<div class="container-fluid p-0">
    @include('client.partials.page-header', [
        'eyebrow' => 'Project Overview',
        'title' => $project->project_name,
        'description' => 'Detailed overview of this active construction project.',
    ])

    <div class="project-detail-card">
        <div class="row g-4">
            <div class="col-12 col-lg-7">
                <div class="project-detail-banner">
                    <img src="https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=1200&q=80" alt="Project overview image">
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="project-detail-info">
                    <div class="detail-pill">{{ $project->status === 'ongoing' ? 'In Progress' : ucfirst($project->status) }}</div>
                    <div class="detail-location"><i class="bi bi-geo-alt me-2"></i>{{ $detailLocation !== '' ? $detailLocation : 'Location Pending' }}</div>
                    <div class="detail-metric"><span>Start</span><strong>{{ $project->start_date?->format('M d, Y') ?? 'TBD' }}</strong></div>
                    <div class="detail-metric"><span>Target End</span><strong>{{ $project->target_end_date?->format('M d, Y') ?? 'TBD' }}</strong></div>
                    <div class="detail-metric"><span>Manager</span><strong>{{ optional($project->engineer)->name ?? 'Unassigned' }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12 col-lg-8">
            <div class="detail-panel">
                <div class="detail-panel-title">Project Phases</div>
                <div class="phase-list">
                    @foreach($project->phases as $phase)
                        <div class="phase-item">
                            <div class="phase-item-top">
                                <span>{{ $phase->phase_name }}</span>
                                <span class="phase-percent">{{ (int) ($phase->completion_percentage ?? 0) }}%</span>
                            </div>
                            <div class="progress" style="height:8px; background:#e2e8f0; border-radius:999px;">
                                <div class="progress-bar bg-success" style="width: {{ $phase->completion_percentage ?? 0 }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="detail-panel">
                <div class="detail-panel-title">Project Summary</div>
                <div class="summary-stat"><span>Current Phase</span><strong>{{ optional($project->phases->firstWhere('status','in_progress'))->phase_name ?? 'N/A' }}</strong></div>
                <div class="summary-stat"><span>Overall Completion</span><strong>{{ round($project->phases->avg('completion_percentage') ?? 0) }}%</strong></div>
                <div class="summary-stat"><span>Supervisor</span><strong>{{ optional($project->activeSupervisor)->name ?? 'Not assigned' }}</strong></div>
            </div>
        </div>
    </div>
</div>

<style>
    .project-detail-card { background:#fff; border:1px solid #e2e8f0; border-radius:24px; padding:1.1rem; box-shadow:0 8px 30px rgba(15,23,42,0.04); }
    .project-detail-banner { border-radius:18px; overflow:hidden; }
    .project-detail-banner img { width:100%; height:320px; object-fit:cover; }
    .project-detail-info { background:#f8fafc; border:1px solid #e2e8f0; border-radius:18px; padding:1.4rem; height:100%; }
    .detail-pill { display:inline-flex; padding:0.35rem 0.7rem; background:#f0fdf4; color:#2E7D32; font-weight:700; border-radius:999px; font-size:0.7rem; text-transform:uppercase; letter-spacing:0.08em; }
    .detail-location { margin-top:1rem; font-weight:700; font-size:1.02rem; }
    .detail-metric { display:flex; justify-content:space-between; padding:0.85rem 0; border-bottom:1px solid #e2e8f0; }
    .detail-metric span { color:#64748b; font-weight:600; }
    .detail-metric strong { font-size:0.93rem; }
    .detail-panel { background:#fff; border:1px solid #e2e8f0; border-radius:20px; padding:1.3rem; }
    .detail-panel-title { font-weight:800; font-size:1.02rem; margin-bottom:1rem; }
    .phase-list { display:flex; flex-direction:column; gap:1rem; }
    .phase-item { background:#f8fafc; padding:0.9rem; border-radius:14px; }
    .phase-item-top { display:flex; justify-content:space-between; margin-bottom:0.65rem; font-weight:700; }
    .phase-percent { color:#2E7D32; }
    .summary-stat { display:flex; justify-content:space-between; padding:0.7rem 0; border-bottom:1px solid #f1f5f9; }
    .summary-stat span { color:#64748b; font-weight:600; }
</style>
@endsection