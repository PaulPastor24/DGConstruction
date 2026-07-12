@extends('layouts.admin')

@section('title', 'Project Archives - D&G Construction Monitor')
@section('page_title', 'Project Archives')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Archived Projects</h2>
            <p class="text-muted mb-0">Review archived project snapshots and manage historical records.</p>
        </div>
        <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Projects
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form id="archiveFilterForm" method="GET" class="row g-2 align-items-end mb-3">
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted">Search</label>
                    <input type="text" id="archiveSearch" name="search" value="{{ request('search') }}" class="form-control" placeholder="Project name, location, or client">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted">Client</label>
                    <select id="archiveClientFilter" name="client_id" class="form-select">
                        <option value="">All Clients</option>
                        @foreach($filterClients as $client)
                            <option value="{{ $client->client_id }}" {{ request('client_id') == $client->client_id ? 'selected' : '' }}>{{ $client->company_name ?? 'Client' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted">Engineer</label>
                    <select id="archiveEngineerFilter" name="engineer_id" class="form-select">
                        <option value="">All Engineers</option>
                        @foreach($filterEngineers as $engineer)
                            <option value="{{ $engineer->user_id }}" {{ request('engineer_id') == $engineer->user_id ? 'selected' : '' }}>{{ $engineer->full_name ?? $engineer->name ?? 'Engineer' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <a href="{{ route('admin.project-archives.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive rounded-3 border overflow-hidden">
                <table class="table align-middle mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>Project</th>
                            <th>Location</th>
                            <th>Client</th>
                            <th>Engineer</th>
                            <th>Timeline</th>
                            <th>Archived At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($archives as $archive)
                            @php
                                $archiveClientLabel = trim((string) ($archive->client?->company_name ?? ''));
                                if ($archiveClientLabel === '' || strtolower($archiveClientLabel) === 'd&g construction corp') {
                                    $archiveClientLabel = null;
                                }

                                $archiveClientContact = trim((string) ($archive->client?->user?->name ?? ''));
                                if ($archiveClientContact === '' || strtolower($archiveClientContact) === 'd&g construction corp') {
                                    $archiveClientContact = null;
                                }

                                $archiveEngineerLabel = trim((string) ($archive->engineer?->full_name ?: $archive->engineer?->name ?? ''));
                                if ($archiveEngineerLabel === '' || strtolower($archiveEngineerLabel) === 'lead engineer') {
                                    $archiveEngineerLabel = null;
                                }

                                $archiveEngineerEmail = trim((string) ($archive->engineer?->email ?? ''));
                                if ($archiveEngineerEmail === '' || strtolower($archiveEngineerEmail) === 'lead engineer') {
                                    $archiveEngineerEmail = null;
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $archive->project_name }}</div>
                                    <div class="small text-muted">#{{ $archive->project_id }}</div>
                                </td>
                                <td>{{ $archive->project_location ?: optional($archive->project)->location ?: optional($archive->project)->project_location ?: '—' }}</td>
                                <td>
                                    <div class="fw-semibold small text-dark">{{ $archiveClientLabel }}</div>
                                    <div class="small text-muted">{{ $archiveClientContact }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold small text-dark">{{ $archiveEngineerLabel }}</div>
                                    <div class="small text-muted">{{ $archiveEngineerEmail }}</div>
                                </td>
                                <td>
                                    <div class="small text-muted">Start: {{ $archive->start_date ? $archive->start_date->format('M d, Y') : '—' }}</div>
                                    <div class="small text-muted">Target: {{ $archive->target_end_date ? $archive->target_end_date->format('M d, Y') : '—' }}</div>
                                    <div class="small text-muted">Actual: {{ $archive->actual_end_date ? $archive->actual_end_date->format('M d, Y') : '—' }}</div>
                                </td>
                                <td>{{ $archive->archived_at ? $archive->archived_at->format('M d, Y H:i') : '—' }}</td>
                                <td>
                                    @if($archive->project)
                                        <form action="{{ route('admin.projects.restore', $archive->project) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm" style="border-color:#c8e6c9; color:#166534; background:#f6fff7;">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary" disabled>
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No archived projects found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($archives->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $archives->firstItem() ?: 0 }} to {{ $archives->lastItem() ?: 0 }} of {{ $archives->total() }} archived projects
                    </div>
                    <nav aria-label="Archived projects pagination">
                        <ul class="pagination pagination-sm mb-0" style="display:flex; gap:4px;">
                            <li class="page-item {{ $archives->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $archives->previousPageUrl() ?? '#' }}" style="color:#198754;" aria-label="Previous page">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            @for($page = 1; $page <= $archives->lastPage(); $page++)
                                <li class="page-item {{ $page == $archives->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $archives->url($page) }}" style="{{ $page == $archives->currentPage() ? 'background-color:#198754; border-color:#198754; color:#fff;' : 'color:#198754;' }}">{{ $page }}</a>
                                </li>
                            @endfor
                            <li class="page-item {{ $archives->hasMorePages() ? '' : 'disabled' }}">
                                <a class="page-link" href="{{ $archives->nextPageUrl() ?? '#' }}" style="color:#198754;" aria-label="Next page">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('archiveFilterForm');
    const search = document.getElementById('archiveSearch');
    const clientFilter = document.getElementById('archiveClientFilter');
    const engineerFilter = document.getElementById('archiveEngineerFilter');

    if (form && search) {
        search.addEventListener('input', function () {
            form.submit();
        });
    }

    [clientFilter, engineerFilter].forEach(function (element) {
        if (element) {
            element.addEventListener('change', function () {
                form && form.submit();
            });
        }
    });
});
</script>
@endpush
