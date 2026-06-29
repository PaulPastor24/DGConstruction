@extends('layouts.client')

@section('title', 'Project Timeline - Client View')

@section('content')
<div id="pg-timeline" class="container-fluid p-0">
    
    <div class="mb-4">
        <span class="text-uppercase tracking-wider text-success fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">MASTER SCHEDULE</span>
        <h2 class="fw-extrabold text-dark m-0 mt-1" style="font-size: 1.75rem; font-weight: 800;">My Project Timeline</h2>
        <p class="text-muted mb-0 mt-1" style="font-size: 0.875rem;">Comprehensive delivery timeline and structural benchmarks across your development portfolio.</p>
    </div>

    @if(isset($projectsWithStats) && count($projectsWithStats) > 0)
        <div class="timeline-summary mb-4">
            <div class="summary-card">
                <div class="summary-icon-box bg-emerald-light">
                    <i class="bi bi-folder-fill text-emerald"></i>
                </div>
                <div>
                    <span class="summary-card-label">Monitored Contracts</span>
                    <h4 class="summary-card-value">{{ count($projectsWithStats) }}</h4>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon-box bg-amber-light">
                    <i class="bi bi-clock-history text-amber"></i>
                </div>
                <div>
                    <span class="summary-card-label">Active Deployments</span>
                    <h4 class="summary-card-value">{{ count($projectsWithStats) }}</h4>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column gap-4">
            @foreach($projectsWithStats as $project)
                <div class="timeline-main-card">
                    <div class="card-header-workspace d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                        <div>
                            <h3 class="project-headline-title m-0">{{ $project['name'] }}</h3>
                            <p class="text-muted text-xs m-0 mt-1"><i class="bi bi-geo-alt me-1"></i> {{ $project['location'] ?? 'Designated Construction Zone Development' }}</p>
                        </div>
                        <span class="badge-status-glow">ACTIVE DEV</span>
                    </div>

                    <div class="row align-items-center mb-4 g-3">
                        <div class="col-12 col-md-5">
                            <div class="project-progress-section">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="progress-label-text">Overall Project Completion</span>
                                    <span class="progress-pct-bold">{{ round($project['progress']) }}%</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill" style="width: {{ $project['progress'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="phases-section-wrapper">
                        <div class="phases-title mb-3">Construction Phases</div>
                        <div class="phases-list-grid">
                            @foreach($project['phases'] as $phase)
                                @php
                                    $displayStatus = strtolower($phase->display_status ?? 'planning');
                                    $isFinished = ($displayStatus === 'completed' || $phase->progress_percentage == 100);
                                @endphp
                                <div class="phase-item-pill">
                                    <span class="phase-dot-indicator {{ $isFinished ? 'completed-dot' : 'pending-dot' }}"></span>
                                    <span class="phase-item-name-text">{{ $phase->phase_name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="no-projects-fallback card border-0 p-5 text-center">
            <div class="fallback-icon-frame mb-3 mx-auto">
                <i class="bi bi-inbox text-muted"></i>
            </div>
            <strong class="text-dark d-block fs-5 mb-1">No Projects Assigned Yet</strong>
            <p class="text-muted mx-auto mb-0 style-paragraph" style="max-width: 420px; font-size: 0.88rem;">
                You don't have any active projects assigned to your user context profile. Contact your regional construction administration desk to initiate tracking links.
            </p>
        </div>
    @endif
</div>

@push('styles')
<style>
    /* --- METRIC SUMMARY CARDS --- */
    .timeline-summary {
        display: flex;
        gap: 1.25rem;
        flex-wrap: wrap;
    }
    .summary-card {
        flex: 1;
        min-width: 240px;
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .summary-icon-box {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .bg-emerald-light { background-color: #e6f7ed; }
    .text-emerald { color: #16a34a; }
    .bg-amber-light { background-color: #fff7ed; }
    .text-amber { color: #d97706; }
    
    .summary-card-label { font-size: 0.78rem; color: var(--text-muted); font-weight: 500; display: block; }
    .summary-card-value { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin: 0; }

    /* --- CONTAINER WORKSPACE BLOCK --- */
    .timeline-main-card {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.003);
    }
    .card-header-workspace { border-color: #f1f5f9 !important; }
    .project-headline-title { font-size: 1.25rem; font-weight: 800; color: var(--text-primary); }
    
    .badge-status-glow {
        background-color: #e6f7ed;
        color: #16a34a;
        font-size: 0.68rem;
        font-weight: 800;
        padding: 0.25rem 0.65rem;
        border-radius: 6px;
        letter-spacing: 0.03em;
    }

    /* --- PROGRESS ARCHITECTURE --- */
    .progress-label-text { font-size: 0.78rem; color: var(--text-muted); font-weight: 500; }
    .progress-pct-bold { font-size: 0.9rem; font-weight: 800; color: var(--text-primary); }
    .progress-bar-container {
        height: 6px;
        background-color: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }
    .progress-bar-fill {
        height: 100%;
        background-color: #16a34a;
        border-radius: 999px;
    }

    /* --- MINI CONTEXTUAL PHASES TRAIL --- */
    .phases-title {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        font-weight: 700;
    }
    .phases-list-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .phase-item-pill {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .phase-dot-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
    .completed-dot { background-color: #16a34a; box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15); }
    .pending-dot { background-color: #94a3b8; }
    .phase-item-name-text { font-size: 0.82rem; font-weight: 600; color: #334155; }

    /* --- FALLBACK NULL STATES --- */
    .no-projects-fallback {
        border: 1px dashed #cbd5e1 !important;
        border-radius: 20px;
        background: transparent;
    }
    .fallback-icon-frame {
        width: 56px;
        height: 56px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .text-xs { font-size: 0.78rem; }
</style>
@endsection