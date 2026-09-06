@extends('layouts.admin')

@section('title', 'Edit User')
@section('page_title', 'Edit User')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <h4 class="fw-bold mb-3">Edit User</h4>
            <p class="text-muted">Update the selected user account.</p>
            <form action="{{ route('admin.users.update', $user->user_id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="engineer" {{ old('role', $user->role) === 'engineer' ? 'selected' : '' }}>Engineer</option>
                            <option value="supervisor" {{ old('role', $user->role) === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                            <option value="client" {{ old('role', $user->role) === 'client' ? 'selected' : '' }}>Client</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select" required>
                            <option value="1" {{ old('is_active', $user->is_active) ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !old('is_active', $user->is_active) ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">New Password (optional)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
