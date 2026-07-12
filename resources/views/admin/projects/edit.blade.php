@extends('layouts.admin')

@section('title', 'Edit Project - D&G Construction Monitor')
@section('page_title', 'Edit Project')

@push('styles')
<style>
    :root {
        --primary-green: #22c55e;
        --primary-green-hover: #16a34a;
        --primary-green-light: rgba(34, 197, 94, 0.08);
    }

    .form-control:focus, 
    .form-select:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px var(--primary-green-light);
    }

    .form-control.is-valid,
    .form-select.is-valid {
        border-color: var(--primary-green);
    }

    .form-control.is-valid:focus,
    .form-select.is-valid:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px var(--primary-green-light);
    }

    .card {
        border-radius: 0.75rem;
        border: 1px solid #eceff4;
    }

    .card-header {
        background: #f8f9fb;
        border-bottom: 1px solid #eef2f7;
        border-radius: 0.75rem 0.75rem 0 0;
    }

    .project-form-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .project-form-actions .btn {
        flex: 0 0 auto;
    }

    @media (max-width: 767.98px) {
        .project-form-actions {
            flex-direction: column;
        }

        .project-form-actions .btn {
            width: 100%;
        }

        .page#pg-project-edit .card-body {
            padding: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const projectNameInput = document.getElementById('project_name');
    const submitBtn = form?.querySelector('button[type="submit"]');

    // Store original form values at page load
    const getFormValues = () => {
        const values = {};
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            values[input.name] = input.value.trim();
        });
        return values;
    };

    const originalValues = getFormValues();

    // Clear error on input
    projectNameInput?.addEventListener('input', function() {
        if (this.value.trim().length > 0) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
        }
    });

    // Validate all required fields on blur
    const requiredFields = document.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });

    // Prevent form submission if no changes were made
    if (submitBtn && form) {
        submitBtn.addEventListener('click', function(e) {
            const currentValues = getFormValues();
            let hasChanges = false;

            // Compare each field value
            for (let key in originalValues) {
                if (originalValues[key] !== currentValues[key]) {
                    hasChanges = true;
                    break;
                }
            }

            if (!hasChanges) {
                e.preventDefault();
                e.stopPropagation();
                alert('No changes detected. Please modify at least one field before updating.');
                return false;
            }
        });
    }
});
</script>
@endpush

@section('content')
<div class="page active" id="pg-project-edit">
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Project Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.projects.update', $project) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="project_name" class="form-label">Project Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('project_name') is-invalid @enderror" 
                                   id="project_name" 
                                   name="project_name" 
                                   value="{{ old('project_name', $project->project_name) }}" 
                                   required>
                            @error('project_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="project_location" class="form-label">Project Location <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('project_location') is-invalid @enderror" 
                                      id="project_location" 
                                      name="project_location" 
                                      rows="2" 
                                      required>{{ old('project_location', $project->project_location) }}</textarea>
                            @error('project_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                                <select class="form-select @error('client_id') is-invalid @enderror" 
                                        id="client_id" 
                                        name="client_id" 
                                        required>
                                    <option value="">-- Select Client --</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->client_id }}" 
                                                {{ old('client_id', $project->client_id) == $client->client_id ? 'selected' : '' }}>
                                            {{ $client->user->name }}
                                            @if($client->company_name)
                                                ({{ $client->company_name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('client_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="supervisor_id" class="form-label">Site Supervisor</label>
                                <select class="form-select @error('supervisor_id') is-invalid @enderror" 
                                        id="supervisor_id" 
                                        name="supervisor_id">
                                    <option value="">-- Select Supervisor --</option>
                                    @foreach($supervisors as $supervisor)
                                        <option value="{{ $supervisor->user_id }}" 
                                                {{ old('supervisor_id', $currentSupervisor?->user_id) == $supervisor->user_id ? 'selected' : '' }}>
                                            {{ $supervisor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supervisor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="start_date" class="form-label">Planned Start Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('start_date') is-invalid @enderror" 
                                       id="start_date" 
                                       name="start_date" 
                                       value="{{ old('start_date', $project->start_date->format('Y-m-d')) }}" 
                                       required>
                                <div class="form-text small text-muted mt-1">Planned kickoff date.</div>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="target_end_date" class="form-label">Planned End Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('target_end_date') is-invalid @enderror" 
                                       id="target_end_date" 
                                       name="target_end_date" 
                                       value="{{ old('target_end_date', $project->target_end_date->format('Y-m-d')) }}" 
                                       required>
                                <div class="form-text small text-muted mt-1">Target schedule for completion.</div>
                                @error('target_end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="actual_end_date" class="form-label">Actual End Date</label>
                                <input type="date" 
                                       class="form-control @error('actual_end_date') is-invalid @enderror" 
                                       id="actual_end_date" 
                                       name="actual_end_date" 
                                       value="{{ old('actual_end_date', $project->actual_end_date?->format('Y-m-d')) }}">
                                <div class="form-text small text-muted mt-1">Actual completion date, if finished.</div>
                                @error('actual_end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    {{ $project->status === 'completed' ? 'disabled' : '' }}
                                    required>
                                <option value="planning" {{ old('status', $project->status) == 'planning' ? 'selected' : '' }}>Planning</option>
                                <option value="ongoing" {{ old('status', $project->status) == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                <option value="completed" {{ old('status', $project->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="on_hold" {{ old('status', $project->status) == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            </select>
                            @if($project->status === 'completed')
                                <small class="text-muted">Completed projects cannot change status.</small>
                            @endif
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4">{{ old('description', $project->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="project-form-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save"></i> Update Project
                            </button>
                            <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-clock-history"></i> Project Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Created:</small><br>
                        <strong>{{ $project->created_at->format('M d, Y h:i A') }}</strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Last Updated:</small><br>
                        <strong>{{ $project->updated_at->format('M d, Y h:i A') }}</strong>
                    </div>
                    <div>
                        <small class="text-muted">Created By:</small><br>
                        <strong>{{ $project->engineer->name ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection