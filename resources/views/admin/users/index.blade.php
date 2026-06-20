@extends('layouts.admin')

@section('title', 'User Management - D&G Construction Monitor')
@section('page_title', 'User Management')

@section('topbar_actions')
    <a href="{{ route('admin.users.create') }}" class="btn btn-success btn-sm">
        <i class="bi bi-person-plus"></i> New User
    </a>
@endsection

@push('styles')
<style>
    :root {
        --primary-green: #22c55e;
        --primary-green-hover: #16a34a;
        --primary-green-light: rgba(34, 197, 94, 0.08);
    }

    #pg-users {
        padding-bottom: 1.5rem;
    }

    /* Stat Cards using Dashboard Design */
    .user-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .user-stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        position: relative;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .user-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--stat-color, var(--primary-green));
    }

    .user-stat-card .stat-label {
        font-size: 12px;
        color: #666666;
        font-weight: 500;
    }

    .user-stat-card .stat-value {
        font-family: 'Syne', sans-serif;
        font-size: 24px;
        font-weight: 800;
        margin: 4px 0;
        color: #1a1a1a;
    }

    /* Section Header using Dashboard Design */
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        margin-top: 4px;
    }

    .section-title {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        color: #666666;
    }

    .user-card-shell {
        background: #fff;
        border-radius: 0.75rem;
        border: 1px solid #eceff4;
        overflow: hidden;
        box-shadow: 0 0.2rem 0.8rem rgba(15, 23, 42, 0.06);
    }

    .user-card-shell .card-header {
        background: #f8f9fb;
        border-bottom: 1px solid #eef2f7;
        padding: 1rem;
    }

    .table-responsive {
        border-radius: 0;
    }

    .table thead th {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6c757d;
        background: #f8f9fb;
        border-bottom: 2px solid #e5e7eb;
    }

    .table tbody td {
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }

    .table tbody tr {
        transition: background-color 0.15s ease;
    }

    .table tbody tr:hover {
        background-color: #f9fafb;
    }

    /* Consistent action button styling */
    .table-actions {
        display: flex;
        gap: 0.35rem;
        justify-content: center;
    }

    .table-actions .btn {
        width: 2.2rem;
        height: 2.2rem;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        border: 1.5px solid;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .table-actions .btn-outline-primary {
        border-color: var(--primary-green);
        color: var(--primary-green);
    }

    .table-actions .btn-outline-primary:hover {
        background-color: var(--primary-green);
        color: #fff;
    }

    .table-actions .btn-outline-danger {
        border-color: #ef4444;
        color: #ef4444;
    }

    .table-actions .btn-outline-danger:hover {
        background-color: #ef4444;
        color: #fff;
    }

    @media (max-width: 767.98px) {
        .user-stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }

        .user-stat-card {
            padding: 12px;
        }

        .user-stat-card .stat-label {
            font-size: 10px;
        }

        .user-stat-card .stat-value {
            font-size: 18px;
        }
    }
</style>
@endpush

@section('content')
<div class="page active" id="pg-users">
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- User Stats Row -->
    <div class="user-stats-grid">
        <div class="user-stat-card" style="--stat-color: #16a34a;">
            <div class="stat-label">Total Users</div>
            <div class="stat-value">{{ $total_users ?? 0 }}</div>
        </div>
        <div class="user-stat-card" style="--stat-color: #3b82f6;">
            <div class="stat-label">Engineers</div>
            <div class="stat-value">{{ $engineers_count ?? 0 }}</div>
        </div>
        <div class="user-stat-card" style="--stat-color: #f59e0b;">
            <div class="stat-label">Supervisors</div>
            <div class="stat-value">{{ $supervisors_count ?? 0 }}</div>
        </div>
        <div class="user-stat-card" style="--stat-color: #06b6d4;">
            <div class="stat-label">Clients</div>
            <div class="stat-value">{{ $clients_count ?? 0 }}</div>
        </div>
    </div>

    <div class="card user-card-shell">
        <div class="card-header">
            <div class="section-header">
                <div class="section-title">All Users</div>
                <span class="badge bg-success" style="border-radius: 0.5rem;">{{ $users->total() }} Total</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Contact Number</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong></td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge bg-{{ $user->role_badge }}" style="border-radius: 0.4rem;">
                                        {{ $user->role_name }}
                                    </span>
                                </td>
                                <td>{{ $user->contact_number ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $user->status_badge }}" style="border-radius: 0.4rem;">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions" role="group">
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="btn btn-outline-primary" 
                                           title="Edit User">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.users.destroy', $user) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Delete this user permanently?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-outline-danger" 
                                                    title="Delete User">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                                    No users found. <a href="{{ route('admin.users.create') }}">Create one now</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection