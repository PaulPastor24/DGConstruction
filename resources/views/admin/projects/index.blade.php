@extends('layouts.admin')

@section('title', 'Projects Management - D&G Construction Monitor')
@section('page_title', 'Projects Management')

@section('topbar_actions')
    <a href="{{ route('admin.projects.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle"></i> New Project
    </a>
@endsection

@push('styles')
<style>
    #pg-projects {
        padding-bottom: 1.5rem;
    }

    .project-summary-card {
        border-radius: 0.9rem;
        border: 1px solid #eef2f7;
        background: linear-gradient(180deg, #fff 0%, #f9fbff 100%);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .project-summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(15, 23, 42, 0.08) !important;
    }

    .project-card-shell {
        background: #fff;
        border-radius: 1rem;
        border: 1px solid #eceff4;
        overflow: hidden;
        box-shadow: 0 0.2rem 0.8rem rgba(15, 23, 42, 0.06);
    }

    .project-card-shell .card-header {
        background: #f8f9fb;
        border-bottom: 1px solid #eef2f7;
        padding: 0.95rem 1rem;
    }

    .project-filters-form {
        width: 100%;
    }

    .project-filters-form input,
    .project-filters-form select {
        border-radius: 0.7rem;
    }

    .project-filters-form .btn {
        border-radius: 0.7rem;
    }

    .table-responsive {
        border-radius: 0;
    }

    .table thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6c757d;
        background: #f8f9fb;
    }

    .table tbody td {
        vertical-align: middle;
    }

    @media (max-width: 767.98px) {
        .project-summary-row .card-body {
            padding: 0.6rem !important;
        }
        .project-summary-row .fs-5 {
            font-size: 1rem !important;
        }
        .project-filters-form {
            width: 100%;
        }
        .project-filters-form input,
        .project-filters-form select,
        .project-filters-form a {
            width: 100%;
        }
        .project-filters-form select {
            max-width: 100% !important;
        }
        .project-mobile-card {
            display: block !important;
            background: #fff;
            border: 1px solid #eef1f4;
            border-radius: 0.9rem;
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
            gap: 0.35rem;
            justify-content: flex-end;
            flex-wrap: wrap;
            margin-top: 0.75rem;
        }
        .project-mobile-card .project-mobile-actions .btn {
            width: 2.45rem;
            height: 2.45rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .table-wrapper-desktop {
            display: none;
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

    <div class="row g-2 g-md-3 mb-3 project-summary-row">
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100 project-summary-card">
                <div class="card-body p-2 p-md-3">
                    <div class="text-muted small">Total</div>
                    <div class="fs-5 fs-md-4 fw-semibold">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100 project-summary-card">
                <div class="card-body p-2 p-md-3">
                    <div class="text-muted small">Planning</div>
                    <div class="fs-5 fs-md-4 fw-semibold text-secondary">{{ $stats['planning'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100 project-summary-card">
                <div class="card-body p-2 p-md-3">
                    <div class="text-muted small">Ongoing</div>
                    <div class="fs-5 fs-md-4 fw-semibold text-primary">{{ $stats['ongoing'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100 project-summary-card">
                <div class="card-body p-2 p-md-3">
                    <div class="text-muted small">Completed</div>
                    <div class="fs-5 fs-md-4 fw-semibold text-success">{{ $stats['completed'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100 project-summary-card">
                <div class="card-body p-2 p-md-3">
                    <div class="text-muted small">On Hold</div>
                    <div class="fs-5 fs-md-4 fw-semibold text-warning">{{ $stats['on_hold'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100 project-summary-card">
                <div class="card-body p-2 p-md-3">
                    <div class="text-muted small">Archived</div>
                    <div class="fs-5 fs-md-4 fw-semibold text-dark">{{ $stats['archived'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card project-card-shell">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h5 class="mb-0">All Projects</h5>
            </div>
            <div class="project-filters-wrapper w-100 w-md-auto">
                <form method="GET" class="project-filters-form d-flex flex-column flex-md-row gap-2 align-items-stretch align-items-md-center">
                    <input type="search" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search projects or client">
                    <select name="status" class="form-select form-select-sm project-status-filter" style="width: 100%; max-width: 200px;">
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
                                        <div class="btn-group btn-group-sm table-actions" role="group">
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
