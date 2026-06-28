@extends('layouts.supervisor')

@section('title', 'Supervisor Profile - D&G Construction Monitor')
@section('page_title', 'Supervisor Profile')

@section('content')
<div class="row g-4">
    <div class="col-12 col-xl-4">
        <div class="page-card h-100">
            <div class="page-card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="user-avatar rounded-circle d-flex align-items-center justify-content-center" style="width: 58px; height: 58px; background: linear-gradient(135deg, #2F6B3C, #66BB6A); color: #fff; font-size: 1.2rem; font-weight: 700;">
                        {{ strtoupper(substr($user->name ?? 'S', 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold fs-5">{{ $user->name ?? 'Supervisor' }}</div>
                        <div class="text-muted small">Role: Site Supervisor</div>
                    </div>
                </div>

                <div class="border rounded-3 p-3 mb-3">
                    <div class="small text-muted text-uppercase fw-bold mb-1">Primary Contact</div>
                    <div class="fw-semibold">{{ $user->email ?? 'No email on file' }}</div>
                </div>
                <div class="border rounded-3 p-3">
                    <div class="small text-muted text-uppercase fw-bold mb-1">Assignment Scope</div>
                    <div class="fw-semibold">{{ $assignedProjects->count() }} active project(s)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="page-card h-100">
            <div class="page-card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="eyebrow">Current Workload</div>
                        <div class="fw-bold fs-5">Assigned projects</div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-3">
                    @forelse ($assignedProjects as $project)
                        <div class="border rounded-3 p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">{{ $project->project_name }}</div>
                                    <div class="small text-muted">{{ $project->project_location ?? 'Location pending' }}</div>
                                </div>
                                <span class="badge bg-success-subtle text-success-emphasis">Active</span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">No project assignments are currently linked to this supervisor profile.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
