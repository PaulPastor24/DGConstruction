@extends('layouts.admin')

@section('title', 'Notifications - D&G Construction Monitor')
@section('page_title', 'Notifications')

@push('styles')
<!-- Import the Syne and Plus Jakarta Sans fonts if they aren't globally loaded -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --ug-dark: var(--brand-dark, #212529);
        --ug-muted: #64748b;
        --ug-border: var(--border, #dee2e6);
        --ug-background: var(--bg-page, #f8f9fa);
        --ug-white: var(--surface, #ffffff);
        --ug-accent: var(--brand-green, #198754);
        --ug-accent-soft: var(--brand-accent-soft, #e8f5e9);
        --ug-accent-hover: var(--brand-green, #198754);
        --ug-info: #0d6efd;
        --ug-info-soft: #e8f0fe;
        --ug-orange: #fd7e14;
        --ug-orange-soft: #fff4e6;
        --ug-purple: #6f42c1;
        --ug-purple-soft: #f3e8ff;
        --ug-danger: #dc3545;
        --ug-danger-soft: #fdf2f2;
    }

    #pg-alerts {
        width: 100%;
        padding: 0 0 28px;
        background-color: var(--ug-background);
        font-family: 'Plus Jakarta Sans', 'Helvetica Neue', Arial, sans-serif;
    }

    /* 100% typography match to the User Management page title */
    .mi-page.inventory-green-theme .dashboard-title-area h2,
    .mi-page.inventory-green-theme .dashboard-title-area h2 *,
    #pg-alerts .dashboard-title-area h2,
    #pg-alerts .dashboard-title-area h2 * {
        font-family: 'Syne', 'Plus Jakarta Sans', 'Helvetica Neue', Arial, sans-serif !important;
        font-size: 28px !important;
        font-weight: 600 !important;
        color: #111827 !important;
        letter-spacing: -0.02em !important;
        margin-bottom: 6px !important;
    }

    #pg-alerts .card-title-sm {
        font-family: 'Syne', 'Plus Jakarta Sans', 'Helvetica Neue', Arial, sans-serif;
        font-weight: 700;
        color: #111827;
    }

    #pg-alerts .notif-card-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    #pg-alerts .notif-card-icon.accent { background-color: var(--ug-accent-soft); color: var(--ug-accent); }
    #pg-alerts .notif-card-icon.info { background-color: var(--ug-info-soft); color: var(--ug-info); }
    #pg-alerts .notif-card-icon.orange { background-color: var(--ug-orange-soft); color: var(--ug-orange); }
    #pg-alerts .notif-card-icon.purple { background-color: var(--ug-purple-soft); color: var(--ug-purple); }
    #pg-alerts .notif-card-icon.danger { background-color: var(--ug-danger-soft); color: var(--ug-danger); }

    #pg-alerts .notif-row-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    #pg-alerts .notif-row-icon.accent { background-color: var(--ug-accent-soft); color: var(--ug-accent); }
    #pg-alerts .notif-row-icon.info { background-color: var(--ug-info-soft); color: var(--ug-info); }
    #pg-alerts .notif-row-icon.orange { background-color: var(--ug-orange-soft); color: var(--ug-orange); }
    #pg-alerts .notif-row-icon.purple { background-color: var(--ug-purple-soft); color: var(--ug-purple); }
    #pg-alerts .notif-row-icon.danger { background-color: var(--ug-danger-soft); color: var(--ug-danger); }

    #pg-alerts .custom-admin-table th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
        font-size: 12px;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        border-bottom-width: 1px;
        padding-top: 14px;
        padding-bottom: 14px;
    }

    #pg-alerts .btn-icon-only {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    #pg-alerts .pulse-indicator {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }

    #pg-alerts .bg-purple-subtle { background-color: var(--ug-purple-soft) !important; }
    #pg-alerts .text-purple { color: var(--ug-purple) !important; }
    #pg-alerts .border-purple-subtle { border-color: #e4d4fb !important; }

    #pg-alerts .nav-tab-item {
        font-weight: 500;
        color: var(--ug-muted);
        transition: all 0.2s ease-in-out;
    }
    #pg-alerts .nav-tab-item:hover {
        color: var(--ug-accent) !important;
    }
    #pg-alerts .nav-tab-item.active {
        color: var(--ug-accent) !important;
        border-color: var(--ug-accent) !important;
        font-weight: 700;
    }
    #pg-alerts .nav-tab-item .badge {
        background-color: var(--ug-accent) !important;
    }

    #pg-alerts .hover-row:hover {
        background-color: #f8fafc !important;
    }
    #pg-alerts .form-check-input:checked {
        background-color: var(--ug-accent) !important;
        border-color: var(--ug-accent) !important;
    }
    #pg-alerts .form-control:focus,
    #pg-alerts .form-select:focus {
        border-color: var(--ug-accent) !important;
        box-shadow: 0 0 0 3px var(--ug-accent-soft) !important;
    }
    #pg-alerts .page-item-link {
        text-decoration: none;
        transition: all 0.2s ease;
    }
    #pg-alerts .page-item-link:hover {
        background-color: #f1f5f9;
    }
    #pg-alerts .page-item.active .page-item-link {
        background-color: var(--ug-accent) !important;
        border-color: var(--ug-accent) !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 pb-3 pt-1 mi-page inventory-green-theme" id="pg-alerts">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 dashboard-title-area">
        <div>
            <h2 style="font-family: 'Syne', 'Plus Jakarta Sans', 'Helvetica Neue', Arial, sans-serif; font-size: 28px; font-weight: 600; color: #111827; letter-spacing: -0.02em; margin-bottom: 6px;">Notifications</h2>
            <p class="text-muted small mb-0">Manage, filter, and track system-wide construction alerts.</p>
        </div>
        <button type="button" class="btn btn-success btn-sm d-flex align-items-center gap-2" id="markAllAdminNotifications" style="background-color: var(--ug-accent); border-color: var(--ug-accent);">
            <i class="bi bi-check2-all"></i> Mark all as read
        </button>
    </div>

    <!-- Top Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Notifications -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="notif-card-icon accent">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Notifications</div>
                        <div id="summaryTotal" class="fs-2 fw-bold text-dark lh-1 my-1">{{ $summary['total_count'] ?? 0 }}</div>
                        <div class="text-muted" style="font-size: 11px;">All time</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Unread Notifications -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="notif-card-icon accent">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Unread Notifications</div>
                        <div id="summaryUnread" class="fs-2 fw-bold text-dark lh-1 my-1">{{ $summary['unread_count'] ?? 0 }}</div>
                        <div class="text-danger fw-semibold" style="font-size: 11px;">Require attention</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Sent This Month -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="notif-card-icon accent">
                        <i class="bi bi-send-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Sent This Month</div>
                        <div id="summarySentThisMonth" class="fs-2 fw-bold text-dark lh-1 my-1">{{ $summary['sent_this_month'] ?? 0 }}</div>
                        <div class="text-muted" style="font-size: 11px;">Since the 1st</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Total Recipients -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="notif-card-icon accent">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Recipients</div>
                        <div id="summaryRecipients" class="fs-2 fw-bold text-dark lh-1 my-1">{{ $summary['total_recipients'] ?? 0 }}</div>
                        <div class="text-muted" style="font-size: 11px;">Admin-level accounts</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs / Filters -->
    <form method="GET" action="{{ route('admin.alerts') }}" id="notifFilterForm">
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
            @php
                $activeType = strtolower(request('type', 'all'));
            @endphp
            <!-- Sub Tabs -->
            <div class="d-flex flex-wrap gap-2 border-bottom pb-3 mb-3">
                <a href="{{ route('admin.alerts', array_filter(['search' => request('search')])) }}" class="btn btn-link nav-tab-item {{ $activeType === 'all' ? 'active border-bottom border-2' : '' }} pb-2 px-3 text-decoration-none" style="font-size: 13px;">All Notifications</a>
                <a href="{{ route('admin.alerts', array_filter(['type' => 'unread', 'search' => request('search')])) }}" class="btn btn-link nav-tab-item {{ $activeType === 'unread' ? 'active border-bottom border-2' : '' }} pb-2 px-3 text-decoration-none" style="font-size: 13px;">Unread <span class="badge rounded-pill">{{ $summary['unread_count'] ?? 0 }}</span></a>
                <a href="{{ route('admin.alerts', array_filter(['type' => 'system', 'search' => request('search')])) }}" class="btn btn-link nav-tab-item {{ $activeType === 'system' ? 'active border-bottom border-2' : '' }} pb-2 px-3 text-decoration-none" style="font-size: 13px;">System Alerts</a>
                <a href="{{ route('admin.alerts', array_filter(['type' => 'project', 'search' => request('search')])) }}" class="btn btn-link nav-tab-item {{ in_array($activeType, ['project','phase']) ? 'active border-bottom border-2' : '' }} pb-2 px-3 text-decoration-none" style="font-size: 13px;">Project Updates</a>
                <a href="{{ route('admin.alerts', array_filter(['type' => 'report', 'search' => request('search')])) }}" class="btn btn-link nav-tab-item {{ $activeType === 'report' ? 'active border-bottom border-2' : '' }} pb-2 px-3 text-decoration-none" style="font-size: 13px;">Reports</a>
                <a href="{{ route('admin.alerts', array_filter(['type' => 'milestone', 'search' => request('search')])) }}" class="btn btn-link nav-tab-item {{ $activeType === 'milestone' ? 'active border-bottom border-2' : '' }} pb-2 px-3 text-decoration-none" style="font-size: 13px;">Milestones</a>
            </div>

            <input type="hidden" name="type" value="{{ request('type', 'all') }}">

            <!-- Filter Row -->
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input id="notifSearchInput" type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Search notifications..." autocomplete="off" style="font-size: 13px;">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select text-muted" style="font-size: 13px;" onchange="document.getElementById('notifFilterForm').submit()">
                        <option value="" {{ request('status') ? '' : 'selected' }}>All Status</option>
                        <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Unread</option>
                        <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read</option>
                    </select>
                </div>
                <div class="col-12 col-md-auto ms-auto d-flex gap-2">
                    <!-- Removed Clear and Filter buttons: search is automatic and status select auto-applies -->
                </div>
            </div>
        </div>
    </form>

    <!-- Layout Grid: Main Table vs Preview Panel -->
    <div class="row g-4">

        <!-- Left Side: Interactive Table Panel -->
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 custom-admin-table" style="font-size: 13px;">
                        <thead>
                            <tr>
                                <th>Notification</th>
                                <th>Type</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="border-0">
                            @forelse($notifications as $notification)
                                @php
                                    $severity = strtolower($notification->severity ?? 'info');
                                    $iconVariant = 'info';
                                    $iconClass = 'bi-file-text';
                                    $badgeClass = 'bg-info-subtle text-info border border-info-subtle';
                                    $badgeLabel = ucfirst($notification->type ?? 'Info');

                                    if ($severity === 'assignment') {
                                        $iconVariant = 'accent';
                                        $iconClass = 'bi-calendar-check';
                                        $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                    } elseif ($severity === 'danger') {
                                        $iconVariant = 'danger';
                                        $iconClass = 'bi-exclamation-triangle';
                                        $badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                                    } elseif ($severity === 'system') {
                                        $iconVariant = 'purple';
                                        $iconClass = 'bi-person-badge';
                                        $badgeClass = 'bg-purple-subtle text-purple border border-purple-subtle';
                                    } elseif ($severity === 'reminder') {
                                        $iconVariant = 'orange';
                                        $iconClass = 'bi-clock-history';
                                        $badgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
                                    }
                                @endphp
                                <tr class="border-bottom hover-row notif-row" style="cursor: pointer;"
                                    data-id="{{ $notification->id }}"
                                    data-title="{{ $notification->title }}"
                                    data-message="{{ $notification->message }}"
                                    data-type="{{ $badgeLabel }}"
                                    data-project="{{ $notification->source ?? 'General' }}"
                                    data-recipient="{{ $notification->recipient ?? 'Admin' }}"
                                    data-status="{{ $notification->is_read ? 'Read' : 'Unread' }}"
                                    data-date="{{ optional($notification->created_at)->format('M d, Y h:i A') }}">
                                    <td>
                                        <div class="d-flex align-items-center gap-3 py-2 ps-2">
                                            <span class="pulse-indicator" style="background-color: {{ !$notification->is_read ? 'var(--ug-accent)' : 'transparent' }};"></span>
                                            <div class="notif-row-icon {{ $iconVariant }}">
                                                <i class="bi {{ $iconClass }} fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size: 13.5px;">{{ $notification->title }}</div>
                                                <div class="text-muted text-truncate" style="max-width: 260px; font-size: 11.5px;">{{ $notification->message }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill px-2 py-1 fw-semibold {{ $badgeClass }}" style="font-size: 11px;">
                                            {{ $badgeLabel }}
                                        </span>
                                    </td>
                                    <td class="text-muted fw-medium small">
                                        {{ $notification->source ?? 'General' }}
                                    </td>
                                    <td>
                                        @if(!$notification->is_read)
                                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1 d-inline-flex align-items-center gap-1" style="font-size: 10px;">
                                                <span class="pulse-indicator bg-success"></span> Unread
                                            </span>
                                        @else
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 d-inline-flex align-items-center gap-1" style="font-size: 10px;">
                                                <span class="pulse-indicator bg-secondary"></span> Read
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-muted" style="font-size: 11.5px;">
                                        <div class="fw-medium">{{ optional($notification->created_at)->format('M d, Y') }}</div>
                                        <div class="text-muted" style="font-size: 10.5px;">{{ optional($notification->created_at)->format('h:i A') }}</div>
                                    </td>
                                    <td class="text-end pe-4" onclick="event.stopPropagation();">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-icon-only border rounded-3 bg-white shadow-sm text-secondary" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" style="font-size: 12px;">
                                                <li><a class="dropdown-item admin-notif-read-btn" href="#" data-id="{{ $notification->id }}"><i class="bi bi-check2 me-2"></i>Mark Read</a></li>
                                                <li><a class="dropdown-item text-danger admin-notif-delete-btn" href="#" data-id="{{ $notification->id }}"><i class="bi bi-trash3-fill me-2"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <div class="mb-2 fs-2 text-secondary-subtle"><i class="bi bi-bell-slash"></i></div>
                                        <h6 class="fw-bold mb-1">No alerts triggered</h6>
                                        <p class="small mb-0 text-secondary-50">All active systems are working within safe guidelines.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($notifications instanceof \Illuminate\Contracts\Pagination\Paginator || $notifications instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="card-footer bg-white border-top border-light-subtle d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
                    <div class="text-muted small fw-semibold">
                        Showing <span class="text-dark">{{ $notifications->firstItem() ?? 0 }}</span> to <span class="text-dark">{{ $notifications->lastItem() ?? 0 }}</span> of <span class="text-dark">{{ $notifications->total() }}</span> recorded entries
                    </div>
                    <div>
                        {{ $notifications->links('pagination::bootstrap-5') }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Right Side: Real-time UI Details Panel -->
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100" id="notifDetailsPanel">
                <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 15px;">
                        <i class="bi bi-bell" style="color: var(--ug-accent);"></i> Notification Details
                    </h5>
                </div>

                <div class="d-flex flex-column gap-3" id="notifDetailsEmpty">
                    <p class="text-muted text-center py-5 mb-0" style="font-size: 13px;">Select a notification from the list to view its details.</p>
                </div>

                <div class="d-flex flex-column gap-3 d-none" id="notifDetailsContent">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1" id="detailType" style="font-size: 11px;"></span>
                        <span class="text-muted opacity-50" style="font-size: 12px;">&bull;</span>
                        <span class="text-muted fw-semibold" id="detailStatus" style="font-size: 11px;"></span>
                    </div>

                    <div>
                        <h4 class="card-title-sm" id="detailTitle" style="font-size: 18px; line-height: 1.3;"></h4>
                        <p class="text-muted mt-2" id="detailMessage" style="font-size: 13px; line-height: 1.6;"></p>
                    </div>

                    <hr class="text-muted opacity-25 my-1">

                    <div class="d-flex flex-column gap-3 py-2">
                        <div class="d-flex align-items-start gap-3">
                            <div class="text-muted pt-0.5"><i class="bi bi-building"></i></div>
                            <div>
                                <span class="d-block text-muted" style="font-size: 11px;">Project</span>
                                <span class="fw-bold text-dark" id="detailProject" style="font-size: 13px;"></span>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div class="text-muted pt-0.5"><i class="bi bi-people"></i></div>
                            <div>
                                <span class="d-block text-muted" style="font-size: 11px;">Recipients</span>
                                <span class="fw-bold text-dark" id="detailRecipient" style="font-size: 13px;"></span>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div class="text-muted pt-0.5"><i class="bi bi-calendar"></i></div>
                            <div>
                                <span class="d-block text-muted" style="font-size: 11px;">Date Sent</span>
                                <span class="fw-bold text-dark" id="detailDate" style="font-size: 13px;"></span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-3 mt-auto">
                        <button class="btn btn-success flex-grow-1 d-flex align-items-center justify-content-center gap-2 py-2 rounded-3" style="font-size: 13px; background-color: var(--ug-accent); border-color: var(--ug-accent);" id="detailMarkReadBtn">
                            <i class="bi bi-check-lg"></i> Mark as Read
                        </button>
                        <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2 py-2 px-3 border-light shadow-sm text-dark bg-white rounded-3" style="font-size: 13px;" id="detailDeleteBtn">
                            <i class="bi bi-trash3-fill"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    let selectedId = null;

    function postJson(url) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({})
        });
    }

    function markReadUrl(id) {
        return "{{ route('admin.notifications.markRead', ['id' => '__ID__']) }}".replace('__ID__', encodeURIComponent(id));
    }

    function deleteUrl(id) {
        return "{{ route('admin.notifications.destroy', ['id' => '__ID__']) }}".replace('__ID__', encodeURIComponent(id));
    }

    // Debounce helper
    function debounce(fn, wait) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    // Auto-submit search input (debounced)
    const searchInput = document.getElementById('notifSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function () {
            const form = document.getElementById('notifFilterForm');
            if (form) form.submit();
        }, 450));
    }

    // Row click -> populate details panel and mark read automatically
    document.querySelectorAll('.notif-row').forEach(function (row) {
        row.addEventListener('click', function () {
            selectedId = row.dataset.id;

            document.getElementById('notifDetailsEmpty').classList.add('d-none');
            document.getElementById('notifDetailsContent').classList.remove('d-none');

            document.getElementById('detailType').textContent = row.dataset.type || 'Notification';
            document.getElementById('detailStatus').textContent = row.dataset.status || '';
            document.getElementById('detailTitle').textContent = row.dataset.title || '';
            document.getElementById('detailMessage').textContent = row.dataset.message || '';
            document.getElementById('detailProject').textContent = row.dataset.project || 'General';
            document.getElementById('detailRecipient').textContent = row.dataset.recipient || 'Admin';
            document.getElementById('detailDate').textContent = row.dataset.date || '';

            // If the notification is unread, mark it as read in background and update UI
            if ((row.dataset.status || '').toLowerCase() === 'unread') {
                postJson(markReadUrl(selectedId)).then(function () {
                    // update row status and visual indicators
                    row.dataset.status = 'Read';
                    const pulse = row.querySelector('.pulse-indicator');
                    if (pulse) pulse.style.backgroundColor = 'transparent';
                    const statusCell = row.querySelector('td:nth-child(4)');
                    if (statusCell) {
                        statusCell.innerHTML = '<span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 d-inline-flex align-items-center gap-1" style="font-size: 10px;"><span class="pulse-indicator bg-secondary"></span> Read</span>';
                    }

                    // decrement summary unread counter if present
                    const unreadEl = document.getElementById('summaryUnread');
                    if (unreadEl) {
                        const current = parseInt(unreadEl.textContent || '0', 10) || 0;
                        if (current > 0) unreadEl.textContent = current - 1;
                    }
                    // update detail status text
                    const detailStatus = document.getElementById('detailStatus');
                    if (detailStatus) detailStatus.textContent = 'Read';
                }).catch(() => {
                    // ignore errors for now; user can still mark read manually
                });
            }
        });
    });

    // Row-level "Mark Read"
    document.querySelectorAll('.admin-notif-read-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const id = this.dataset.id;
            if (!id) return;
            postJson(markReadUrl(id)).finally(function () {
                window.location.reload();
            });
        });
    });

    // Row-level "Delete"
    document.querySelectorAll('.admin-notif-delete-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const id = this.dataset.id;
            if (!id) return;
            postJson(deleteUrl(id)).finally(function () {
                window.location.reload();
            });
        });
    });

    // Details panel actions (act on currently selected notification)
    document.getElementById('detailMarkReadBtn')?.addEventListener('click', function () {
        if (!selectedId) return;
        postJson(markReadUrl(selectedId)).finally(function () {
            window.location.reload();
        });
    });

    document.getElementById('detailDeleteBtn')?.addEventListener('click', function () {
        if (!selectedId) return;
        postJson(deleteUrl(selectedId)).finally(function () {
            window.location.reload();
        });
    });

    // Mark all as read
    document.getElementById('markAllAdminNotifications')?.addEventListener('click', function () {
        postJson("{{ route('admin.notifications.markAllRead') }}").finally(function () {
            window.location.reload();
        });
    });
});
</script>
@endpush