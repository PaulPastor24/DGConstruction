@extends('layouts.admin')

@section('title', 'Create User - D&G Construction Monitor')
@section('page_title', 'Create New User')

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
</style>
@endpush

@section('content')
<div class="page active" id="pg-user-create">
    
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
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <input type="text" 
                                   class="form-control @error('contact_number') is-invalid @enderror" 
                                   id="contact_number" 
                                   name="contact_number" 
                                   value="{{ old('contact_number') }}">
                            @error('contact_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select @error('role') is-invalid @enderror" 
                                        id="role" 
                                        name="role" 
                                        required>
                                    <option value="">-- Select Role --</option>
                                    <option value="engineer" {{ old('role') == 'engineer' ? 'selected' : '' }}>Engineer/Administrator</option>
                                    <option value="site_supervisor" {{ old('role') == 'site_supervisor' ? 'selected' : '' }}>Site Supervisor</option>
                                    <option value="client" {{ old('role') == 'client' ? 'selected' : '' }}>Client</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="is_active" class="form-label">Status</label>
                                <select class="form-select @error('is_active') is-invalid @enderror" 
                                        id="is_active" 
                                        name="is_active">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save"></i> Create User
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
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
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Role Descriptions</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Engineer/Administrator</strong>
                        <p class="small text-muted mb-0">Full system access, can manage projects, users, and all operations.</p>
                    </div>
                    <div class="mb-3">
                        <strong>Site Supervisor</strong>
                        <p class="small text-muted mb-0">Can manage assigned projects, submit reports, and track attendance.</p>
                    </div>
                    <div>
                        <strong>Client</strong>
                        <p class="small text-muted mb-0">Read-only access to view project progress and reports.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection