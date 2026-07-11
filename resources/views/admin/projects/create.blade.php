@extends('layouts.admin')

@section('title', 'Create Project - D&G Construction Monitor')
@section('page_title', 'Create New Project')

@push('styles')
<style>
    :root {
        --mi-dark: var(--brand-dark);
        --mi-muted: #64748b;
        --mi-border: var(--border);
        --mi-background: var(--bg-page);
        --mi-white: var(--surface);
        --mi-accent: var(--brand-green);
        --mi-accent-soft: var(--brand-accent-soft);
        --mi-accent-hover: var(--brand-green);
    }

    .project-green-theme .card {
        border: 1px solid var(--mi-border) !important;
        box-shadow: 0 10px 28px rgba(22, 101, 52, 0.08);
    }

    .project-green-theme .form-control:focus, 
    .project-green-theme .form-select:focus {
        border-color: var(--mi-accent) !important;
        box-shadow: 0 0 0 0.2rem rgba(22, 101, 52, 0.16) !important;
    }

    .project-green-theme .form-control.is-valid,
    .project-green-theme .form-select.is-valid {
        border-color: var(--mi-accent);
    }

    .project-green-theme .form-control.is-valid:focus,
    .project-green-theme .form-select.is-valid:focus {
        border-color: var(--mi-accent);
        box-shadow: 0 0 0 0.2rem rgba(22, 101, 52, 0.16) !important;
    }

    .project-green-theme .btn-success {
        background-color: var(--mi-accent) !important;
        border-color: var(--mi-accent) !important;
        color: #ffffff !important;
        font-weight: 600;
    }

    .project-green-theme .btn-success:hover,
    .project-green-theme .btn-success:focus {
        background-color: var(--mi-accent-hover) !important;
        border-color: var(--mi-accent-hover) !important;
    }

    .project-green-theme .btn-outline-secondary:hover {
        border-color: var(--mi-accent) !important;
        color: var(--mi-accent) !important;
        background-color: var(--mi-accent-soft) !important;
    }

    .project-form-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .project-form-actions .btn {
        flex: 0 0 auto;
        padding: 0.6rem 1.5rem;
        border-radius: 6px;
        font-size: 0.875rem;
    }

    .guideline-item {
        font-size: 13px;
        color: #475569;
    }

    @media (max-width: 767.98px) {
        .project-form-actions {
            flex-direction: column;
        }

        .project-form-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .page#pg-project-create .card-body {
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
    const projectNameError = document.querySelector('[data-error="project_name"]');

    projectNameInput?.addEventListener('input', function() {
        if (this.value.trim().length > 0) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
            if (projectNameError) {
                projectNameError.style.display = 'none';
            }
        } else {
            this.classList.remove('is-valid');
        }
    });

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
});
</script>
@endpush

@section('content')
<div class="page active project-green-theme" id="pg-project-create">
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="mb-0 fw-bold text-dark" style="color: var(--mi-accent) !important;">Project Information</h5>
                    <p class="text-muted small mb-0 mt-1">Provide the foundational details needed to deploy a monitoring profile.</p>
                </div>
                <div class="card-body pt-3">
                    <form action="{{ route('admin.projects.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="project_name" class="form-label fw-bold text-dark small">Project Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control form-control-sm @error('project_name') is-invalid @enderror" 
                                   id="project_name" 
                                   name="project_name" 
                                   placeholder="e.g. D&G Residential Building"
                                   value="{{ old('project_name') }}" 
                                   required>
                            @error('project_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="project_location" class="form-label fw-bold text-dark small">Project Location <span class="text-danger">*</span></label>
                            <textarea class="form-control form-control-sm @error('project_location') is-invalid @enderror" 
                                      id="project_location" 
                                      name="project_location" 
                                      rows="2" 
                                      placeholder="Provide structural address deployment details..."
                                      required>{{ old('project_location') }}</textarea>
                            @error('project_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="client_id" class="form-label fw-bold text-dark small">Client Profile <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm @error('client_id') is-invalid @enderror" 
                                        id="client_id" 
                                        name="client_id" 
                                        required>
                                    <option value="" disabled selected hidden>-- Select Assigned Client --</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->client_id }}" {{ old('client_id') == $client->client_id ? 'selected' : '' }}>
                                            {{ $client->user->name ?? 'Unknown Client User' }}
                                            @if(!empty($client->company_name))
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
                                <label for="supervisor_id" class="form-label fw-bold text-dark small">Site Supervisor</label>
                                <select class="form-select form-select-sm @error('supervisor_id') is-invalid @enderror" 
                                        id="supervisor_id" 
                                        name="supervisor_id">
                                    <option value="" disabled selected hidden>-- Select Assigned Supervisor (Optional) --</option>
                                    @foreach($supervisors as $supervisor)
                                        <option value="{{ $supervisor->user_id }}" {{ old('supervisor_id') == $supervisor->user_id ? 'selected' : '' }}>
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
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label fw-bold text-dark small">Planned Start Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control form-control-sm @error('start_date') is-invalid @enderror" 
                                       id="start_date" 
                                       name="start_date" 
                                       value="{{ old('start_date') }}" 
                                       required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="target_end_date" class="form-label fw-bold text-dark small">Target Completion Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control form-control-sm @error('target_end_date') is-invalid @enderror" 
                                       id="target_end_date" 
                                       name="target_end_date" 
                                       value="{{ old('target_end_date') }}" 
                                       required>
                                @error('target_end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <input type="hidden" name="status" value="planning">
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold text-dark small">Structural Scope / Description</label>
                            <textarea class="form-control form-control-sm @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      placeholder="Specify scope guidelines, materials allocation limits, or phase bounds..."
                                      rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="project-form-actions border-top pt-3">
                            <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2">
                                <i class="bi bi-save"></i> Deploy Project
                            </button>
                            <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 bg-white text-dark">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle" style="color: var(--mi-accent);"></i> Deployment Guidelines
                    </h6>
                </div>
                <div class="card-body pt-2">
                    <ul class="list-unstyled d-flex flex-column gap-2 mb-0 layout-rules-list">
                        <li class="guideline-item d-flex align-items-start gap-2">
                            <span class="text-danger font-monospace">*</span>
                            <span>All mandatory fields must be finalized before system creation.</span>
                        </li>
                        <li class="guideline-item d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success small mt-1"></i>
                            <span>Project configuration titles evaluate via unique string validations.</span>
                        </li>
                        <li class="guideline-item d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success small mt-1"></i>
                            <span>Target completion milestones depend directly on valid date sequence limits.</span>
                        </li>
                        <li class="guideline-item d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success small mt-1"></i>
                            <span>Initial status allocations defaults immediately to the **Planning Phase**.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection