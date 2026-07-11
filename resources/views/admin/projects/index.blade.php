@extends('layouts.admin')

@section('title', 'Projects Management - D&G Construction Monitor')
@section('page_title', 'Projects Management')

@section('topbar_actions')
    <a href="{{ route('admin.projects.create') }}" class="btn btn-success btn-sm">
        <i class="bi bi-plus-circle"></i> New Project
    </a>
@endsection

@push('styles')
<style>
    :root {
        --primary-green: #22c55e;
        --primary-green-hover: #16a34a;
        --primary-green-light: rgba(34, 197, 94, 0.08);
    }

    #pg-projects {
        padding-bottom: 1.5rem;
    }

    /* Stat Cards using Dashboard Design */
    .project-stats-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .project-stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        position: relative;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .project-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--stat-color, var(--primary-green));
    }

    .project-stat-card .stat-label {
        font-size: 12px;
        color: #666666;
        font-weight: 500;
    }

    .project-stat-card .stat-value {
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
        flex-wrap: wrap;
        gap: 16px;
    }

    .section-title {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        color: #666666;
    }

    .project-filters-wrapper {
        flex: 1;
        min-width: 300px;
    }

    .project-card-shell {
        background: #fff;
        border-radius: 0.75rem;
        border: 1px solid #eceff4;
        overflow: hidden;
        box-shadow: 0 0.2rem 0.8rem rgba(15, 23, 42, 0.06);
    }

    .project-card-shell .card-header {
        background: #f8f9fb;
        border-bottom: 1px solid #eef2f7;
        padding: 1rem;
    }

    .project-filters-form {
        width: 100%;
        display: flex;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .project-filters-form input,
    .project-filters-form select {
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        min-height: 36px;
    }

    .project-filters-form input {
        flex: 1;
        min-width: 200px;
    }

    .project-filters-form select {
        width: 180px;
    }

    .project-filters-form .btn {
        border-radius: 0.5rem;
        height: 36px;
        padding: 0 12px;
        font-size: 14px;
    }

    .project-filters-form input:focus,
    .project-filters-form select:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px var(--primary-green-light);
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

    /* Consistent action button styling - square and rounded */
    .table-actions {
        display: flex;
        gap: 0.35rem;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
    }

    .table-actions .d-inline {
        display: inline-flex !important;
    }

    .table-actions .btn {
        width: 2.2rem;
        height: 2.2rem;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        border: 1.5px solid;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        line-height: 1;
        flex-shrink: 0;
    }

    .table-actions .btn-outline-info {
        border-color: #06b6d4;
        color: #06b6d4;
    }

    .table-actions .btn-outline-info:hover {
        background-color: #06b6d4;
        color: #fff;
    }

    .table-actions .btn-outline-primary {
        border-color: var(--primary-green);
        color: var(--primary-green);
    }

    .table-actions .btn-outline-primary:hover {
        background-color: var(--primary-green);
        color: #fff;
    }

    .table-actions .btn-outline-warning {
        border-color: #f59e0b;
        color: #f59e0b;
    }

    .table-actions .btn-outline-warning:hover {
        background-color: #f59e0b;
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

    @media (max-width: 1199.98px) {
        .project-stats-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .project-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .project-stat-card {
            padding: 12px;
        }

        .project-stat-card .stat-label {
            font-size: 10px;
        }

        .project-stat-card .stat-value {
            font-size: 18px;
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .project-filters-wrapper {
            width: 100%;
            min-width: unset;
        }

        .project-filters-form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.6rem;
        }

        .project-filters-form input,
        .project-filters-form select,
        .project-filters-form .btn {
            width: 100%;
            min-width: 0;
        }

        .project-card-shell .card-header {
            padding: 1rem;
        }

        .project-mobile-card {
            display: block !important;
            background: #fff;
            border: 1px solid #eef1f4;
            border-radius: 0.75rem;
            box-shadow: 0 0.125rem 0.5rem rgba(15, 23, 42, 0.06);
            margin-bottom: 0.9rem;
            padding: 0.95rem;
        }
        .project-mobile-card .project-mobile-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 0.65rem;
        }
        .project-mobile-card h6 {
            font-size: 0.98rem;
            margin-bottom: 0.2rem;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
        }
        .project-mobile-card .project-mobile-meta {
            display: grid;
            gap: 0.3rem;
            font-size: 0.9rem;
        }
        .project-mobile-card .project-mobile-meta span {
            color: #6c757d;
        }
        .project-mobile-card .project-mobile-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
            flex-wrap: wrap;
            margin-top: 0.75rem;
        }
        .project-mobile-card .project-mobile-actions .btn {
            width: 2.2rem;
            height: 2.2rem;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            border: 1.5px solid;
            font-size: 0.85rem;
        }
        .table-wrapper-desktop {
            display: none;
        }
        .card-footer {
            padding: 0.9rem 1rem;
        }
    }
    @media (max-width: 575.98px) {
        .project-stats-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (min-width: 768px) {
        .project-mobile-card {
            display: none;
        }
        .table-wrapper-desktop {
            display: block;
        }
    }
</style>
@endpush

@section('content')
<div class="page active" id="pg-projects">
    
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

    <div class="project-stats-grid">
        <div class="project-stat-card" style="--stat-color: #16a34a;">
            <div class="stat-label">Total</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </div>
        <div class="project-stat-card" style="--stat-color: #3b82f6;">
            <div class="stat-label">Planning</div>
            <div class="stat-value">{{ $stats['planning'] }}</div>
        </div>
        <div class="project-stat-card" style="--stat-color: #06b6d4;">
            <div class="stat-label">Ongoing</div>
            <div class="stat-value">{{ $stats['ongoing'] }}</div>
        </div>
        <div class="project-stat-card" style="--stat-color: #10b981;">
            <div class="stat-label">Completed</div>
            <div class="stat-value">{{ $stats['completed'] }}</div>
        </div>
        <div class="project-stat-card" style="--stat-color: #f59e0b;">
            <div class="stat-label">On Hold</div>
            <div class="stat-value">{{ $stats['on_hold'] }}</div>
        </div>
        <div class="project-stat-card" style="--stat-color: #6b7280;">
            <div class="stat-label">Archived</div>
            <div class="stat-value">{{ $stats['archived'] }}</div>
        </div>
    </div>

    <div class="card project-card-shell">
        <div class="card-header">
            <div class="section-header">
                <div class="section-title">All Projects</div>
                <div class="project-filters-wrapper">
                    <form method="GET" class="project-filters-form">
                        <input type="search" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search projects or client">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All statuses</option>
                            <option value="planning" {{ request('status') == 'planning' ? 'selected' : '' }}>Planning</option>
                            <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                        @if(request('search') || request('status'))
                            <a href="{{ route('admin.projects.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-wrapper-desktop">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Project Name</th>
                                <th>Location</th>
                                <th>Client</th>
                                <th>Supervisor</th>
                                <th>Start Date</th>
                                <th>Target End</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $project)
                                <tr>
                                    <td>
                                        <strong>{{ $project->project_name }}</strong>
                                    </td>
                                    <td>{{ Str::limit($project->project_location, 30) }}</td>
                                    <td>
                                        @if($project->client && $project->client->user)
                                            {{ $project->client->user->name }}
                                            @if($project->client->company_name)
                                                <br><small class="text-muted">{{ $project->client->company_name }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($project->active_supervisor)
                                            {{ $project->active_supervisor->name }}
                                        @else
                                            <span class="text-muted">Not Assigned</span>
                                        @endif
                                    </td>
                                    <td>{{ $project->start_date->format('M d, Y') }}</td>
                                    <td>{{ $project->target_end_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $project->status_badge }}">
                                            {{ ucfirst($project->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="table-actions" role="group">
                                            <a href="{{ route('admin.projects.show', $project) }}" 
                                               class="btn btn-outline-info" 
                                               title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.projects.edit', $project) }}" 
                                               class="btn btn-outline-primary" 
                                               title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if(!in_array($project->status, ['completed', 'archived']))
                                                <form action="{{ route('admin.projects.archive', $project) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Archive this project?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" 
                                                            class="btn btn-outline-warning" 
                                                            title="Archive">
                                                        <i class="bi bi-archive"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.projects.destroy', $project) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Delete this project permanently?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-outline-danger" 
                                                        title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No projects found. <a href="{{ route('admin.projects.create') }}">Create one now</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @forelse($projects as $project)
                <div class="project-mobile-card d-block d-md-none">
                    <div class="project-mobile-header">
                        <div>
                            <h6 class="mb-1">{{ $project->project_name }}</h6>
                            <span class="badge bg-{{ $project->status_badge }}">{{ ucfirst($project->status) }}</span>
                        </div>
                    </div>
                    <div class="project-mobile-meta">
                        <div><span>Client:</span> {{ $project->client && $project->client->user ? $project->client->user->name : 'N/A' }}</div>
                        <div><span>Supervisor:</span> {{ $project->active_supervisor ? $project->active_supervisor->name : 'Not Assigned' }}</div>
                        <div><span>Start:</span> {{ $project->start_date->format('M d, Y') }}</div>
                        <div><span>Target End:</span> {{ $project->target_end_date->format('M d, Y') }}</div>
                        <div><span>Location:</span> {{ Str::limit($project->project_location, 50) }}</div>
                    </div>
                    <div class="project-mobile-actions">
                        <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-outline-info btn-sm" title="View"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-outline-primary btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                        @if(!in_array($project->status, ['completed', 'archived']))
                            <form action="{{ route('admin.projects.archive', $project) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this project?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-warning btn-sm" title="Archive"><i class="bi bi-archive"></i></button>
                            </form>
                        @endif
                        <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this project permanently?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No projects found. <a href="{{ route('admin.projects.create') }}">Create one now</a>
                </div>
            @endforelse
        </div>
        @if($projects->hasPages())
            <div class="card-footer">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.querySelector('input[name="search"]');
        const statusSelect = document.querySelector('.project-status-filter');
        const filtersForm = document.querySelector('.project-filters-form');

        if (statusSelect && filtersForm) {
            statusSelect.addEventListener('change', function () {
                filtersForm.submit();
            });
        }

        if (searchInput && filtersForm) {
            let debounceTimer;
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    filtersForm.submit();
                }, 500);
            });
        }
    });
</script>
@endpush
