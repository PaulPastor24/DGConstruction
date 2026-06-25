@extends('layouts.admin')

@section('title', 'Create Project - D&G Construction Monitor')
@section('page_title', 'Create New Project')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const projectNameInput = document.getElementById('project_name');
    const projectNameError = document.querySelector('[data-error="project_name"]');

    // Clear error on input
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

    // Validate all required fields on input
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
<div class="page active" id="pg-project-create">
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Project Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.projects.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="project_name" class="form-label">Project Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('project_name') is-invalid @enderror" 
                                   id="project_name" 
                                   name="project_name" 
                                   value="{{ old('project_name') }}" 
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
                                      required>{{ old('project_location') }}</textarea>
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
                                <label for="supervisor_id" class="form-label">Site Supervisor</label>
                                <select class="form-select @error('supervisor_id') is-invalid @enderror" 
                                        id="supervisor_id" 
                                        name="supervisor_id">
                                    <option value="">-- Select Supervisor --</option>
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
                            <div class="col-md-4 mb-3">
                                <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('start_date') is-invalid @enderror" 
                                       id="start_date" 
                                       name="start_date" 
                                       value="{{ old('start_date') }}" 
                                       required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="target_end_date" class="form-label">Target End Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('target_end_date') is-invalid @enderror" 
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

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Create Project
                            </button>
                            <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
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
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>All fields marked with <span class="text-danger">*</span> are required</li>
                        <li>Project name must be unique (case-insensitive)</li>
                        <li>Project location is required</li>
                        <li>Target end date must be after start date</li>
                        <li>You can assign a supervisor now or later</li>
                        <li><strong>Status automatically set to "Planning"</strong></li>
                        <li>All input fields are automatically trimmed</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection