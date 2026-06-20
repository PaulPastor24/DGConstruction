@extends('layouts.supervisor')

@section('title', 'Project Timeline - D&G Construction Monitor')
@section('page_title', 'Project Timeline')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <select class="form-select border-0 shadow-sm heading-syne fw-bold px-3 py-2 text-dark" style="width: auto; border-radius: 8px; min-width: 240px;">
                <option value="">Select Project Site...</option>
                {{-- Dynamic loop for projects --}}
                @isset($projects)
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $project->name }}</option>
                    @endforeach
                @else
                    <option selected disabled>No active projects found</option>
                @endisset
            </select>
            
            @if(isset($currentProject))
                <span class="badge bg-{{ $currentProject->status_color ?? 'success' }}-subtle text-{{ $currentProject->status_color ?? 'success' }} px-3 py-2 rounded-pill fw-medium" style="font-size: 13px;">
                    {{ $currentProject->status_text ?? 'On Track' }}
                </span>
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-medium" style="font-size: 13px;">
                    {{ $currentProject->progress_percentage ?? 0 }}% Complete
                </span>
                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-medium" style="font-size: 13px;">
                    {{ $currentProject->current_phase_name ?? 'Initial Phase' }}
                </span>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            <h6 class="heading-syne fw-bold m-0 text-dark text-uppercase tracking-wider" style="font-size: 14px;">
                Construction Phases & Milestones
            </h6>
            <small class="text-muted fw-medium">
                Target Completion: {{ isset($currentProject->target_end_date) ? date('M Y', strtotime($currentProject->target_end_date)) : 'N/A' }}
            </small>
        </div>
        
        <div class="card-body px-4 pb-4">
            <div class="d-flex flex-column gap-3 mt-2">
                @if(isset($phases) && $phases->count() > 0)
                    @foreach($phases as $item)
                        <div class="p-3 border rounded-3 bg-white d-flex align-items-center justify-content-between flex-wrap gap-3 transition-hover" style="border-color: #f1f3f5 !important;">
                            <div class="d-flex align-items-center gap-3">
                                <span class="rounded-circle d-inline-block" style="width: 12px; height: 12px; background-color: var(--bs-{{ $item->color ?? 'secondary' }});"></span>
                                <div>
                                    <h6 class="mb-1 text-dark fw-bold" style="font-size: 14px;">{{ $item->name }}</h6>
                                    <p class="mb-0 text-muted" style="font-size: 11px;">
                                        {{ date('M d', strtotime($item->start_date)) }} – {{ date('M d, Y', strtotime($item->end_date)) }} · 
                                        <span class="fst-italic">{{ $item->status_description }}</span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <div class="heading-syne fw-extrabold text-{{ ($item->color ?? 'secondary') === 'secondary' ? 'muted' : (($item->color ?? 'secondary') === 'warning' ? 'dark' : $item->color) }}" style="font-size: 16px;">
                                    {{ $item->progress }}%
                                </div>
                                <small class="text-muted fw-bold tracking-wider" style="font-size: 9px;">{{ strtoupper($item->badge_text) }}</small>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-4 text-center text-muted fst-italic border rounded-3 bg-light-subtle" style="font-size: 13px;">
                        No construction planning milestones or phase tracks have been added for this site selection.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection