@extends('layouts.client')

@section('title', 'Project Status - Client Portal')

@section('content')
<div class="container-fluid p-0">
    
    <div class="mb-4">
        <h1 class="heading-syne fw-extrabold text-dark tracking-tight mb-1" style="font-size: 38px;">Project Timeline</h1>
        <p class="text-muted" style="font-size: 13px;">Real-time construction phase status and progress milestones.</p>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Project Selection Form Context Handler -->
            <form action="{{ route('client.dashboard') }}" method="GET" id="projectSelectorForm">
                <select name="project_id" onchange="document.getElementById('projectSelectorForm').submit();" class="form-select border-0 shadow-sm heading-syne fw-bold px-3 py-2 text-dark" style="width: auto; border-radius: 8px; min-width: 240px; font-size: 14px; background-color: #fff;">
                    @forelse($projects as $proj)
                        <option value="{{ $proj->project_id }}" {{ (isset($project) && $project->project_id == $proj->project_id) ? 'selected' : '' }}>
                            {{ $proj->project_name }}
                        </option>
                    @empty
                        <option value="" disabled selected>No Project Sites Allocated</option>
                    @endforelse
                </select>
            </form>
            
            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-medium" style="font-size: 12px;">
                {{ isset($project) ? ucfirst(str_replace('_', ' ', $project->status)) : 'No Active Status' }}
            </span>
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-medium" style="font-size: 12px;">
                {{ $project->progress_percentage ?? 0 }}% Complete
            </span>
            <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-medium" style="font-size: 12px;">
                {{ $project->current_phase_name ?? 'Initial Phase' }}
            </span>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            <h6 class="heading-syne fw-bold m-0 text-dark text-uppercase tracking-wider" style="font-size: 14px;">
                Construction Phases – {{ $project->project_name ?? 'Project Profile Overview' }}
            </h6>
            <small class="text-muted fw-medium">Target: {{ isset($project->target_end_date) ? date('M Y', strtotime($project->target_end_date)) : 'N/A' }}</small>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                
                <div class="col-12 col-lg-7">
                    <div class="p-4 border rounded-3 bg-white h-100" style="border-color: #f1f3f5 !important;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="heading-syne fw-bold m-0 text-dark" style="font-size: 13px;">Project Summary</h6>
                            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 10px;">Read Only</span>
                        </div>
                        <div class="row g-4">
                            <div class="col-6">
                                <small class="text-muted d-block" style="font-size: 11px;">Project Location</small>
                                <span class="fw-medium text-dark d-block mt-1" style="font-size: 13px;">{{ $project->project_location ?? 'Not Specified' }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block" style="font-size: 11px;">Assigned Engineer</small>
                                <span class="fw-medium text-dark d-block mt-1" style="font-size: 13px;">{{ $project->engineer->name ?? 'Not Assigned' }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block" style="font-size: 11px;">Project Timeline</small>
                                <span class="fw-medium text-dark d-block mt-1" style="font-size: 13px;">
                                    {{ isset($project->start_date) ? date('M d, Y', strtotime($project->start_date)) : 'N/A' }}
                                </span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block" style="font-size: 11px;">Site Supervisors</small>
                                <span class="fw-medium text-dark d-block mt-1" style="font-size: 13px;">
                                    @if(isset($project) && $project->supervisors->isNotEmpty())
                                        {{ $project->supervisors->pluck('name')->implode(', ') }}
                                    @else
                                        No Supervisor Active
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="d-flex flex-column gap-3">
                        
                        <div class="p-3 border rounded-3 bg-white" style="border-color: #f1f3f5 !important;">
                            <div class="d-flex justify-content-between mb-2">
                                <h6 class="heading-syne fw-bold m-0 text-dark" style="font-size: 13px;">Project Snapshot</h6>
                                <small class="text-muted" style="font-size: 11px;">Last updated: {{ isset($project->updated_at) ? $project->updated_at->format('M d, Y') : date('M d, Y') }}</small>
                            </div>
                            <div class="progress mb-2" style="height: 6px; background-color: #e9ecef; border-radius: 4px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $project->progress_percentage ?? 0 }}%;" aria-valuenow="{{ $project->progress_percentage ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted" style="font-size: 11px;">
                                <span class="fw-medium text-success">{{ $project->progress_percentage ?? 0 }}% complete</span>
                                <span>Target: {{ isset($project->target_end_date) ? date('M d, Y', strtotime($project->target_end_date)) : 'N/A' }}</span>
                            </div>
                            
                            <hr class="my-2" style="opacity: 0.08;">
                            
                            <div class="row g-2 text-dark" style="font-size: 12px;">
                                <div class="col-6 text-muted" style="font-size: 11px;">Phase owner</div>
                                <div class="col-6 text-end fw-medium" style="font-size: 11px;">{{ $project->phase_owner ?? 'Site Engineering Team' }}</div>
                                <div class="col-6 text-muted" style="font-size: 11px;">Latest review</div>
                                <div class="col-6 text-end fw-medium text-success" style="font-size: 11px;">{{ $project->review_status ?? 'Approved for next phase' }}</div>
                            </div>
                        </div>

                        <div class="p-3 border rounded-3 bg-white" style="border-color: #f1f3f5 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="heading-syne fw-bold m-0 text-dark" style="font-size: 13px;">Latest Site Update</h6>
                                @if(isset($latest_update))
                                    <span class="badge bg-primary-subtle text-primary rounded" style="font-size: 10px;">{{ date('M d, Y', strtotime($latest_update->log_date)) }}</span>
                                @endif
                            </div>
                            <p class="text-muted mb-0 lh-base" style="font-size: 12px;">
                                {{ $latest_update->content ?? 'No operational activity reports or log entries have been submitted to date for this site allocation.' }}
                            </p>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection