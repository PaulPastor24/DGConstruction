@extends('layouts.admin')

@section('title', 'User Activity Logs')
@section('page_title', 'User Activity Logs')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <p class="text-muted mb-0">Account creation, updates, status changes, and deletions.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to User Management
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.auditLog') }}" class="row g-2 align-items-center mb-3">
                <div class="col-12 col-md-8">
                    <label class="visually-hidden" for="activitySearch">Search user activity</label>
                    <input id="activitySearch" type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search account, admin, action, details, or IP address">
                </div>
                <div class="col-12 col-md-auto">
                    <button class="btn btn-success w-100" type="submit"><i class="bi bi-search me-1"></i>Search</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Activity</th>
                            <th>Details</th>
                            <th>Admin</th>
                            <th>IP Address</th>
                            <th>Date and Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activityLogs as $log)
                            <tr>
                                <td class="fw-semibold">{{ $log->action }}</td>
                                <td>{{ $log->description }}</td>
                                <td>{{ $log->user?->name ?? 'Former user' }}</td>
                                <td>{{ $log->ip_address ?? '—' }}</td>
                                <td class="text-nowrap">{{ $log->created_at?->format('M d, Y h:i A') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-5">No user activity logs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($activityLogs->hasPages())
                <div class="pt-3">{{ $activityLogs->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
