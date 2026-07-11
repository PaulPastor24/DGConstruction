@extends('layouts.admin')

@section('title', (isset($project) && $project->project_name) ? $project->project_name . ' - D&G Construction Monitor' : 'Project Details - D&G Construction Monitor')
@section('page_title', (isset($project) && $project->project_name) ? $project->project_name : 'Project Details')

@push('styles')
<style>
    .project-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .detail-item {
        background: var(--card-bg, #f8f9fa);
        padding: 1rem;
        border-radius: 0.5rem;
        border-left: 3px solid var(--primary);
    }
    
    .detail-label {
        font-size: 0.85rem;
        color: var(--muted, #6c757d);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .detail-value {
        font-size: 1.1rem;
        color: var(--foreground, #212529);
        margin-top: 0.25rem;
        word-break: break-word;
    }

    .project-show-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
    }

    .project-show-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    @media (max-width: 991.98px) {
        .project-details-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .project-details-grid {
            grid-template-columns: 1fr;
        }

        .project-show-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .project-show-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .project-show-actions .btn {
            width: 100%;
        }

        .page#pg-project-show .card-body {
            padding: 1rem;
        }
    }
</style>
@endpush

@section('topbar_actions')
    @if($project)
        <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil"></i> Edit Project
        </a>
    @endif
@endsection

@section('content')
<div class="page active" id="pg-project-show">
    
    @if(!isset($project) || !$project)
        <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Error:</strong> Project data is not available. Please try again.
            <a href="{{ route('admin.projects.index') }}" class="btn btn-sm btn-primary ms-2">Back to Projects</a>
        </div>
    @else
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header project-show-header">
                    <div>
                        <h5 class="mb-0">{{ $project->project_name ?? 'N/A' }}</h5>
                        <small class="text-muted">ID: #{{ $project->project_id ?? 'N/A' }}</small>
                    </div>
                    @if($project->status)
                        <span class="badge bg-{{ $project->status_badge ?? 'secondary' }} p-2">
                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    @if($project->description)
                        <div class="mb-3 p-3 bg-light rounded">
                            <small class="text-muted">Description</small>
                            <p class="mb-0">{{ $project->description }}</p>
                        </div>
                    @endif

                    <div class="project-details-grid">
                        <div class="detail-item">
                            <div class="detail-label">Location</div>
                            <div class="detail-value">{{ $project->project_location ?? 'Not specified' }}</div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Status</div>
                            <div class="detail-value">
                                <span class="badge bg-{{ $project->status_badge ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $project->status ?? 'N/A')) }}
                                </span>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Start Date</div>
                            <div class="detail-value">
                                @if($project->start_date)
                                    <i class="bi bi-calendar-event text-primary"></i> {{ $project->start_date->format('M d, Y') }}
                                @else
                                    Not specified
                                @endif
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Target End Date</div>
                            <div class="detail-value">
                                @if($project->target_end_date)
                                    <i class="bi bi-calendar-check text-success"></i> {{ $project->target_end_date->format('M d, Y') }}
                                @else
                                    Not specified
                                @endif
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Actual End Date</div>
                            <div class="detail-value">
                                @if($project->actual_end_date)
                                    <i class="bi bi-calendar-x text-danger"></i> {{ $project->actual_end_date->format('M d, Y') }}
                                @else
                                    <span class="text-muted">Not completed</span>
                                @endif
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Duration</div>
                            <div class="detail-value">
                                @if($project->start_date && $project->target_end_date)
                                    {{ $project->start_date->diffInDays($project->target_end_date) }} days
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Created</div>
                            <div class="detail-value">{{ $project->created_at ? $project->created_at->format('M d, Y h:i A') : 'N/A' }}</div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Last Updated</div>
                            <div class="detail-value">{{ $project->updated_at ? $project->updated_at->format('M d, Y h:i A') : 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Project Team</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Client -->
                        <div class="col-md-4 mb-3">
                            <h6 class="text-muted mb-3"><i class="bi bi-person-check text-primary"></i> Client</h6>
                            @if($project->client && $project->client->user)
                                <div class="card border p-2">
                                    <div class="fw-bold">{{ $project->client->user->name ?? 'N/A' }}</div>
                                    @if($project->client->company_name)
                                        <small class="text-muted"><i class="bi bi-building"></i> {{ $project->client->company_name }}</small><br>
                                    @endif
                                    @if($project->client->user->email)
                                        <small><i class="bi bi-envelope"></i> {{ $project->client->user->email }}</small><br>
                                    @endif
                                    @if($project->client->user->contact_number)
                                        <small><i class="bi bi-telephone"></i> {{ $project->client->user->contact_number }}</small>
                                    @endif
                                </div>
                            @else
                                <p class="text-muted">Not assigned</p>
                            @endif
                        </div>

                        <!-- Engineer -->
                        <div class="col-md-4 mb-3">
                            <h6 class="text-muted mb-3"><i class="bi bi-hammer text-primary"></i> Project Engineer</h6>
                            @if($project->engineer)
                                <div class="card border p-2">
                                    <div class="fw-bold">{{ $project->engineer->name ?? 'N/A' }}</div>
                                    @if($project->engineer->email)
                                        <small><i class="bi bi-envelope"></i> {{ $project->engineer->email }}</small><br>
                                    @endif
                                    @if($project->engineer->contact_number)
                                        <small><i class="bi bi-telephone"></i> {{ $project->engineer->contact_number }}</small>
                                    @endif
                                </div>
                            @else
                                <p class="text-muted">Not assigned</p>
                            @endif
                        </div>

                        <!-- Supervisor -->
                        <div class="col-md-4 mb-3">
                            <h6 class="text-muted mb-3"><i class="bi bi-shield-check text-primary"></i> Site Supervisor</h6>
                            @php
                                $activeSupervisor = ($project->supervisors && $project->supervisors->count() > 0) 
                                    ? $project->supervisors->first() 
                                    : null;
                            @endphp
                            @if($activeSupervisor)
                                <div class="card border p-2">
                                    <div class="fw-bold">{{ $activeSupervisor->name ?? 'N/A' }}</div>
                                    @if($activeSupervisor->email)
                                        <small><i class="bi bi-envelope"></i> {{ $activeSupervisor->email }}</small><br>
                                    @endif
                                    @if($activeSupervisor->contact_number)
                                        <small><i class="bi bi-telephone"></i> {{ $activeSupervisor->contact_number }}</small>
                                    @endif
                                </div>
                            @else
                                <p class="text-muted">Not assigned</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Info Card -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Project Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Project ID</small><br>
                        <strong>#{{ $project->project_id ?? 'N/A' }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Progress</small><br>
                        <strong>{{ number_format($project->progress_percentage ?? 0, 2) }}%</strong>
                        <div class="progress mt-2" style="height: 8px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $project->progress_percentage ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Created</small><br>
                        <strong>{{ $project->created_at ? $project->created_at->format('M d, Y h:i A') : 'N/A' }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Last Updated</small><br>
                        <strong>{{ $project->updated_at ? $project->updated_at->format('M d, Y h:i A') : 'N/A' }}</strong>
                    </div>
                    <div>
                        <small class="text-muted">Duration</small><br>
                        <strong>
                            @if($project->start_date && $project->target_end_date)
                                {{ $project->start_date->diffInDays($project->target_end_date) }} days
                            @else
                                N/A
                            @endif
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="project-show-actions mt-3">
        <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    @endif
</div>
@endsection
