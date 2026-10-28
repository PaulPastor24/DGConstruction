@extends('layouts.'.$routePrefix)

@section('title', 'Workers & Allocation')
@section('page_title', 'Workers & Allocation')

@section('content')
<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h1 class="h4">Workers & Allocation</h1>
            <p class="text-muted">View all workers across projects. Assign adds a project; Transfer ends other current assignments and moves the worker to the selected project.</p>
            @if(session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="GET" action="{{ route($routePrefix.'.workers.index') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="workerSearch" class="form-label">Search workers</label>
                    <input id="workerSearch" name="search" class="form-control" value="{{ request('search') }}" placeholder="First name, last name, or trade" maxlength="100">
                </div>
                <div class="col-md-3">
                    <label for="workerStatus" class="form-label">Worker status</label>
                    <select id="workerStatus" name="status" class="form-select">
                        <option value="">All workers</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-success" type="submit">Search</button>
                    <a class="btn btn-outline-secondary" href="{{ route($routePrefix.'.workers.index') }}">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <p class="text-muted">{{ $workers->total() }} workers found</p>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Worker</th><th>Trade</th><th>Status</th><th>Current projects</th><th style="min-width: 300px">Allocate to project</th></tr></thead>
                    <tbody>
                        @forelse($workers as $worker)
                            <tr>
                                <td><strong>{{ $worker->full_name }}</strong><div class="small text-muted">Worker #{{ $worker->worker_id }}</div></td>
                                <td>{{ $worker->trade ?: 'General' }}</td>
                                <td><span class="badge {{ $worker->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $worker->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td>
                                    @forelse($assignments->get($worker->worker_id, collect()) as $assignment)
                                        <div>{{ $assignment->project_name }}</div>
                                    @empty
                                        <span class="text-muted">Unassigned</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if($worker->is_active && $projects->isNotEmpty())
                                        <form method="POST" action="{{ route($routePrefix.'.workers.allocate', $worker->worker_id) }}" class="d-grid gap-2">
                                            @csrf
                                            <label for="workerProject-{{ $worker->worker_id }}" class="visually-hidden">Project for {{ $worker->full_name }}</label>
                                            <select id="workerProject-{{ $worker->worker_id }}" name="project_id" class="form-select form-select-sm" required>
                                                <option value="">Choose project</option>
                                                @foreach($projects as $project)
                                                    <option value="{{ $project->project_id }}">{{ $project->project_name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="d-flex gap-2">
                                                <label for="workerMode-{{ $worker->worker_id }}" class="visually-hidden">Allocation action for {{ $worker->full_name }}</label>
                                                <select id="workerMode-{{ $worker->worker_id }}" name="mode" class="form-select form-select-sm" required>
                                                    <option value="assign">Assign to project</option>
                                                    <option value="transfer">Transfer to project</option>
                                                </select>
                                                <button class="btn btn-success btn-sm" type="submit">Save</button>
                                            </div>
                                        </form>
                                    @else
                                        <span class="text-muted small">{{ $worker->is_active ? 'No projects available' : 'Inactive worker' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No workers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $workers->links() }}
        </div>
    </div>
</div>
@endsection
