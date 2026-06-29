@extends('layouts.client')

@section('title', 'Timeline & Milestones - Client Portal')

@section('content')
<div class="container-fluid p-0">
    
    <div class="mb-4">
        <span class="text-uppercase tracking-wider text-success fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">PROJECT TRACKING</span>
        <h2 class="fw-extrabold text-dark m-0 mt-1" style="font-size: 1.75rem; font-weight: 800;">Timeline & Milestones</h2>
        <p class="text-muted mb-0 mt-1" style="font-size: 0.875rem;">Real-time progress overview of construction phases and target milestones.</p>
    </div>

    <div class="card timeline-ui-panel border-0">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
            <h6 class="text-uppercase font-bold tracking-wider m-0" style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); letter-spacing: 0.05em;">
                Phase Milestones
            </h6>
        </div>
        
        <div class="card-body px-4 pb-4">
            <div class="d-flex flex-column gap-3 mt-2">
                @if(isset($phases) && $phases->count() > 0)
                    @foreach($phases as $phase)
                        @php
                            $isCompleted = ($phase->progress_percentage == 100);
                            $accentColor = '#22c55e'; 
                            $bgColor = '#f0fdf4';
                            
                            if (($phase->color ?? '') === 'warning') {
                                $accentColor = '#f59e0b';
                                $bgColor = '#fffbeb';
                            } elseif (($phase->color ?? '') === 'danger') {
                                $accentColor = '#ef4444';
                                $bgColor = '#fef2f2';
                            } elseif ($phase->progress_percentage == 0) {
                                $accentColor = '#64748b';
                                $bgColor = '#f8fafc';
                            }
                        @endphp
                        
                        <div class="phase-row-item d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="status-indicator-dot-box" style="background-color: {{ $bgColor }}; color: {{ $accentColor }};">
                                    @if($isCompleted)
                                        <i class="bi bi-check-lg" style="font-size: 1.1rem; -webkit-text-stroke: 0.5px;"></i>
                                    @else
                                        <i class="bi bi-cone-striped" style="font-size: 1rem;"></i>
                                    @endif
                                </div>
                                
                                <div>
                                    <h6 class="phase-title-text m-0">{{ $phase->phase_name }}</h6>
                                    <p class="phase-date-subtext mb-0 mt-1">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        {{ date('M d', strtotime($phase->start_date)) }} – {{ date('M d, Y', strtotime($phase->end_date)) }} 
                                        
                                        @if($phase->status_description)
                                            <span class="status-description-pill ms-2">
                                                <span class="description-dot" style="background-color: {{ $accentColor }};"></span>
                                                {{ $phase->status_description }}
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center gap-4">
                                <div class="d-none d-sm-block" style="width: 120px;">
                                    <div class="progress" style="height: 6px; background-color: #e2e8f0; border-radius: 999px;">
                                        <div class="progress-bar" style="width: {{ $phase->progress_percentage }}%; background-color: {{ $accentColor }}; border-radius: 999px;"></div>
                                    </div>
                                </div>
                                <div class="text-end" style="min-width: 55px;">
                                    <span class="phase-percentage-badge {{ $isCompleted ? 'completed-text' : 'active-text' }}">
                                        {{ $isCompleted ? 'Done' : $phase->progress_percentage . '%' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-5 text-center text-muted border border-dashed rounded-xl bg-light-subtle">
                        <div class="mb-2 fs-3"><i class="bi bi-calendar-x text-muted"></i></div>
                        <p class="m-0 font-semibold text-sm">No structured phase track plans or milestone records exist for this project model context.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

<style>
    /* --- COMPONENT UI DECORATIONS --- */
    .timeline-ui-panel {
        background: #ffffff;
        border: 1px solid #f1f5f9 !important;
        border-radius: 20px;
    }

    .phase-row-item {
        background-color: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        padding: 1.15rem 1.5rem;
        transition: all 0.2s ease;
    }
    
    .phase-row-item:hover {
        background-color: #f8fafc;
        border-color: #e2e8f0;
    }

    .status-indicator-dot-box {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .phase-title-text {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .phase-date-subtext {
        font-size: 0.8rem;
        color: var(--text-muted);
        font-weight: 500;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
    }

    .status-description-pill {
        background-color: #f1f5f9;
        color: #334155;
        font-size: 0.74rem;
        font-weight: 600;
        padding: 0.15rem 0.5rem;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .description-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }

    .phase-percentage-badge {
        font-size: 0.88rem;
        font-weight: 800;
    }
    
    .phase-percentage-badge.completed-text {
        background-color: #e6f7ed;
        color: #16a34a !important;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    
    .phase-percentage-badge.active-text {
        color: var(--text-primary);
    }

    .font-semibold { font-weight: 600; }
    .text-sm { font-size: 0.85rem; }
    .rounded-xl { border-radius: 14px !important; }
</style>
@endsection