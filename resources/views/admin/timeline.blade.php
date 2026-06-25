@extends('layouts.admin')

@section('title', 'Project Timeline - D&G Construction Monitor')
@section('page_title', 'Project Timeline')

@section('content')
<div class="page active" id="pg-timeline">
    
    <div class="card mb-4">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3" style="padding: 1rem 1.5rem;">
            <div class="d-flex align-items-center gap-3">
                <label for="timeline-project-select" class="fw-bold text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.5px; margin-bottom: 0;">Project</label>
                <select class="form-select" id="timeline-project-select" style="width:auto; min-width:250px;" onchange="window.location.href = '?project_id=' + this.value">
                    <option value="">-- Select a Project --</option>
                    @foreach($projects ?? [] as $proj)
                        <option value="{{ $proj->project_id }}" {{ (isset($selectedProject) && $selectedProject->project_id == $proj->project_id) ? 'selected' : '' }}>
                            {{ $proj->project_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            @if(isset($selectedProject))
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-soft text-success px-2 py-1 rounded" style="font-size: 12px; background-color: rgba(34,197,94,0.1);">{{ $selectedProject->status_badge ? ucfirst($selectedProject->status) : 'Planning' }}</span>
                    <span class="badge bg-secondary-soft text-dark px-2 py-1 rounded" style="font-size: 12px; background-color: rgba(0,0,0,0.05);">{{ $selectedProject->progress_percentage }}% Complete</span>
                    <span class="badge bg-primary-soft text-primary px-2 py-1 rounded" style="font-size: 12px; background-color: rgba(59,130,246,0.1);">{{ $selectedProject->current_phase ?? 'No Active Phase' }}</span>
                </div>
            @endif
        </div>
    </div>

    @if(isset($selectedProject))
        <div class="row row-deck match-height">
            
            <div class="col-lg-8 col-md-12 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="card-title h6 mb-0">Construction Phases — {{ $selectedProject->project_name }}</div>
                        @if($selectedProject->target_end_date)
                            <small class="text-muted">Target: {{ \Carbon\Carbon::parse($selectedProject->target_end_date)->format('M Y') }}</small>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush timeline-phases-list">
                            @forelse($phases ?? [] as $phase)
                                <div class="list-group-item p-4 d-flex align-items-start justify-content-between gap-3 border-bottom">
                                    <div class="d-flex align-items-start gap-3">
                                        <span class="dot mt-1" style="height: 12px; width: 12px; border-radius: 50%; display: inline-block; background-color: {{ $phase->color_code ?? 'var(--muted)' }}"></span>
                                        <div>
                                            <div class="fw-bold mb-1 text-dark" style="font-size: 14px;">
                                                {{ $phase->title }} {!! $phase->is_current ? '<span class="text-muted fw-normal"> &larr; Current</span>' : '' !!}
                                            </div>
                                            <div class="text-muted" style="font-size: 11px;">
                                                {{ \Carbon\Carbon::parse($phase->start_date)->format('M d') }} &ndash; {{ \Carbon\Carbon::parse($phase->end_date)->format('M d, Y') }} &middot; {{ $phase->status_note }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-dark" style="font-size: 14px;">{{ $phase->progress_percentage }}%</div>
                                        <small class="text-muted text-uppercase" style="font-size: 10px; letter-spacing: 0.3px;">{{ $phase->status_text }}</small>
                                    </div>
                                </div>
                            @empty
                                <div class="p-5 text-center text-muted">
                                    <p class="mb-0">No construction phases configuration mapped for this project yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12 d-flex flex-column gap-4">
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title h6 mb-0">Phase Progress</div>
                    </div>
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="progress-ring-container text-center position-relative" style="width: 120px; height: 120px;">
                            <svg viewBox="0 0 36 36" class="circular-chart" style="max-width: 100%; max-height: 100%;">
                                <path class="circle-bg" stroke="#e2e8f0" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                <path class="circle" stroke="#22c55e" stroke-dasharray="{{ $selectedProject->progress_percentage ?? 0 }}, 100" stroke-width="3" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            </svg>
                            <div class="position-absolute top-50 start-50 translate-middle text-center w-100">
                                <div class="fw-extrabold text-dark h4 mb-0">{{ $selectedProject->progress_percentage ?? 0 }}%</div>
                            </div>
                        </div>

                        <div class="flex-grow-1" style="font-size: 12px;">
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-1">
                                <li class="d-flex align-items-center gap-2">
                                    <span style="width:8px; height:8px; border-radius:50%; background-color:#22c55e; display:inline-block;"></span>
                                    <span>Done ({{ $stats['phases_done'] ?? 0 }})</span>
                                </li>
                                <li class="d-flex align-items-center gap-2">
                                    <span style="width:8px; height:8px; border-radius:50%; background-color:#eab308; display:inline-block;"></span>
                                    <span>In Progress ({{ $stats['phases_processing'] ?? 0 }})</span>
                                </li>
                                <li class="d-flex align-items-center gap-2">
                                    <span style="width:8px; height:8px; border-radius:50%; background-color:#cbd5e1; display:inline-block;"></span>
                                    <span>Upcoming ({{ $stats['phases_upcoming'] ?? 0 }})</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card flex-grow-1">
                    <div class="card-header">
                        <div class="card-title h6 mb-0">Milestone Flags</div>
                    </div>
                    <div class="card-body d-flex flex-column gap-3">
                        @forelse($milestones ?? [] as $flag)
                            <div class="alert mb-0 border-0 p-3 d-flex gap-3 align-items-start rounded" style="background-color: {{ $flag->type == 'warning' ? 'rgba(245,158,11,0.08)' : 'rgba(59,130,246,0.08)' }};">
                                <div class="fw-bold text-uppercase" style="font-size: 11px; color: {{ $flag->type == 'warning' ? 'var(--orange, #d97706)' : 'var(--blue, #2563eb)' }};">
                                    {{ $flag->type_label ?? strtoupper($flag->type) }}
                                </div>
                                <div style="font-size: 12px;">
                                    <div class="fw-bold text-dark mb-1">{{ $flag->title }}</div>
                                    <div class="text-muted mb-1">{{ $flag->description }}</div>
                                    <small class="text-muted d-block" style="font-size:10px;">Flagged {{ \Carbon\Carbon::parse($flag->logged_at)->format('M d') }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted my-auto">
                                <small>No milestone alerts or scheduling logs flagged.</small>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    @else
        <div class="card p-5 text-center text-muted">
            <div class="py-4">
                <h5 class="text-dark">No Project Selected</h5>
                <p class="mb-0">Please select an active construction management asset from the dropdown field menu above to generate schedules.</p>
            </div>
        </div>
    @endif
</div>
@endsection