@extends('layouts.'.$routePrefix)

@section('title', 'Workers & Allocation')
@section('page_title', 'Workers & Allocation')

@push('scripts')
    <script src="{{ asset('js/worker-allocation.js') }}" defer></script>
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h1 class="h4">Workers & Allocation</h1>
            <p class="text-muted">View workers across projects. Allocate individually or select a group to assign or transfer together.</p>
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
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <p class="text-muted mb-1">{{ $workers->total() }} workers found</p>
                    <small class="text-muted">Select active workers on this page to allocate together.</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span id="workerSelectionCount" class="small text-muted" role="status">0 selected</span>
                    <button type="button" id="clearWorkerSelection" class="btn btn-sm btn-outline-secondary" disabled>Clear</button>
                    <button type="button" id="bulkAllocateWorkers" class="btn btn-success btn-sm" disabled><i class="bi bi-people me-1" aria-hidden="true"></i>Allocate selected</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th><input type="checkbox" class="form-check-input" id="selectPageWorkers" aria-label="Select all active workers on this page" @disabled($projects->isEmpty())></th><th>Worker</th><th>Trade</th><th>Status</th><th>Current projects</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($workers as $worker)
                            <tr>
                                <td><input type="checkbox" class="form-check-input worker-select" value="{{ $worker->worker_id }}" data-worker-name="{{ $worker->full_name }}" data-worker-projects="{{ $assignments->get($worker->worker_id, collect())->pluck('project_name')->implode(', ') ?: 'Unassigned' }}" aria-label="Select {{ $worker->full_name }}" @disabled(!$worker->is_active || $projects->isEmpty())></td>
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
                                        <button type="button" class="btn btn-outline-success btn-sm allocate-worker" data-worker-id="{{ $worker->worker_id }}" aria-label="Allocate {{ $worker->full_name }}"><i class="bi bi-person-plus me-1" aria-hidden="true"></i>Allocate</button>
                                    @else
                                        <span class="text-muted small">{{ $worker->is_active ? 'No projects available' : 'Inactive worker' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No workers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $workers->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="workerAllocationModal" tabindex="-1" aria-labelledby="workerAllocationTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form id="workerAllocationForm" method="POST" action="{{ route($routePrefix.'.workers.bulk-allocate') }}" novalidate class="d-flex flex-column overflow-hidden" style="max-height: 100%;">
                @csrf
                <div class="modal-header bg-success-subtle px-4">
                    <h5 id="workerAllocationTitle" class="modal-title fw-semibold">Allocate workers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close allocation"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="allocationError" class="alert alert-danger" role="alert" tabindex="-1" hidden></div>
                    <h6 id="allocationWorkerCount" class="fw-semibold"></h6>
                    <ul id="allocationWorkerList" class="list-group list-group-flush mb-4" style="max-height: 180px; overflow-y: auto;"></ul>
                    <div id="allocationWorkerIds"></div>
                    <div class="mb-4">
                        <label for="allocationProject" class="form-label fw-semibold">Destination project</label>
                        <select id="allocationProject" name="project_id" class="form-select" data-searchable-project aria-label="Search destination projects">
                            <option value="">Choose project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->project_id }}">{{ $project->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <fieldset>
                        <legend class="fs-6 fw-semibold">Allocation action</legend>
                        <div class="border rounded-3 p-3 mb-2">
                            <div class="form-check">
                                <input id="allocationAssign" class="form-check-input" type="radio" name="mode" value="assign" checked>
                                <label class="form-check-label fw-semibold" for="allocationAssign">Assign to project</label>
                                <div class="text-muted small">Add the selected project and keep current assignments.</div>
                            </div>
                        </div>
                        <div class="border rounded-3 p-3">
                            <div class="form-check">
                                <input id="allocationTransfer" class="form-check-input" type="radio" name="mode" value="transfer">
                                <label class="form-check-label fw-semibold" for="allocationTransfer">Transfer to project</label>
                                <div class="text-muted small">End other current project assignments and move these workers to the selected project.</div>
                            </div>
                        </div>
                    </fieldset>
                    <div id="allocationTransferNotice" class="alert alert-warning mt-3 mb-0" hidden>Confirming this transfer ends the selected workers’ other current assignments. Past attendance and deployment records are preserved.</div>
                </div>
                <div class="modal-footer px-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="confirmWorkerAllocation" class="btn btn-success">Confirm allocation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
