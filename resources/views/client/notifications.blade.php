@extends('layouts.client')

@section('title', 'Client Notifications')
@section('mobileTitle', 'Notifications')

@section('content')
<div class="container-fluid px-0 py-2">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="dashboard-page-eyebrow">Client portal</div>
            <h2 class="dashboard-page-title mb-1">Notifications</h2>
            <p class="dashboard-page-description">Review your latest project updates, reports, and milestone alerts.</p>
        </div>
        <div class="d-flex gap-2">
            
            <button type="button" class="btn btn-success btn-sm" id="markAllClientNotifications">Mark all as read</button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="bi bi-bell fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold">Total</div>
                        <h4 class="mb-0 fw-bold">{{ $totalNotifs ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="bi bi-envelope fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold">Unread</div>
                        <h4 class="mb-0 fw-bold">{{ $unreadCount ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="bi bi-check2-circle fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold">Read</div>
                        <h4 class="mb-0 fw-bold">{{ $readCount ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('client.notifications') }}" class="btn btn-sm {{ request('type', 'all') === 'all' ? 'btn-success' : 'btn-outline-secondary' }}">All</a>
        <a href="{{ route('client.notifications', ['type' => 'unread']) }}" class="btn btn-sm {{ request('type') === 'unread' ? 'btn-success' : 'btn-outline-secondary' }}">Unread</a>
        <a href="{{ route('client.notifications', ['type' => 'read']) }}" class="btn btn-sm {{ request('type') === 'read' ? 'btn-success' : 'btn-outline-secondary' }}">Read</a>
        <a href="{{ route('client.notifications', ['type' => 'project']) }}" class="btn btn-sm {{ request('type') === 'project' ? 'btn-success' : 'btn-outline-secondary' }}">Project</a>
        <a href="{{ route('client.notifications', ['type' => 'report']) }}" class="btn btn-sm {{ request('type') === 'report' ? 'btn-success' : 'btn-outline-secondary' }}">Report</a>
        <a href="{{ route('client.notifications', ['type' => 'milestone']) }}" class="btn btn-sm {{ request('type') === 'milestone' ? 'btn-success' : 'btn-outline-secondary' }}">Milestone</a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        @if($notifications->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-envelope-open display-6 mb-2 d-block"></i>
                No notifications found.
            </div>
        @else
            <div class="list-group list-group-flush">
                @foreach($notifications as $notification)
                    @php
                        $isUnread = !$notification->is_read;
                        $type = strtolower($notification->type ?? 'general');
                        $icon = match ($type) {
                            'report' => 'bi-file-earmark-text',
                            'project' => 'bi-folder2-open',
                            'milestone' => 'bi-calendar3',
                            'phase' => 'bi-bar-chart-steps',
                            'announcement' => 'bi-megaphone',
                            default => 'bi-bell',
                        };
                    @endphp
                    <div class="list-group-item d-flex align-items-start gap-3 {{ $isUnread ? 'bg-light' : '' }}">
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi {{ $icon }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-bold text-dark">{{ $notification->title }}</div>
                                    <div class="text-secondary small mt-1">{{ $notification->message }}</div>
                                </div>
                                <span class="badge rounded-pill bg-light text-muted text-uppercase">{{ $notification->type ?? 'General' }}</span>
                            </div>
                            <div class="text-muted small mt-2">{{ optional($notification->created_at)->diffForHumans() ?? 'Just now' }}</div>
                        </div>
                        @if($isUnread)
                            <button type="button" class="btn btn-sm btn-outline-success notification-read-btn" data-id="{{ $notification->id }}">Mark read</button>
                        @endif
                    </div>
                @endforeach
            </div>
                    <div class="card-footer bg-white d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div class="small text-muted">Showing {{ $notifications->firstItem() }} to {{ $notifications->lastItem() }} of {{ $notifications->total() }}</div>
                <div>
                    @if($notifications->hasPages())
                        {{ $notifications->links('pagination::bootstrap-5-limited') }}
                    @else
                        <nav aria-label="Notification pagination" class="pagination">
                            <span class="page-item disabled">
                                <span class="page-link">&lsaquo;</span>
                            </span>
                            <span class="page-item active">
                                <span class="page-link">{{ $notifications->currentPage() }}</span>
                            </span>
                            <span class="page-item disabled">
                                <span class="page-link">&rsaquo;</span>
                            </span>
                        </nav>
                    @endif
                </div>
            </div>
        @endif
    </div>
    <style>
        .pagination {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.35rem;
            margin-top: 1rem;
            padding-left: 0;
            list-style: none;
        }
        .pagination .page-item .page-link {
            color: var(--brand-green);
            border-color: var(--brand-green);
            min-width: 44px;
            min-height: 44px;
            border-radius: 0.85rem;
            font-weight: 600;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .pagination .page-item.active .page-link {
            background-color: var(--brand-green);
            border-color: var(--brand-green);
            color: #ffffff;
        }
        .pagination .page-item .page-link:hover {
            background-color: rgba(42, 64, 40, 0.1);
            color: var(--brand-green);
        }
        .pagination {
            display: flex !important;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.35rem;
            margin-top: 1rem;
            padding-left: 0;
            list-style: none;
        }
        .pagination .page-item {
            display: inline-block;
        }
        .pagination .page-item .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--brand-green);
            border-color: var(--brand-green);
            min-width: 44px;
            min-height: 44px;
            border-radius: 0.85rem;
            font-weight: 600;
            transition: background-color 0.2s ease, color 0.2s ease;
            padding: 0.75rem 0.95rem;
            background-color: #fff;
        }
        .pagination .page-item.active .page-link {
            background-color: var(--brand-green);
            border-color: var(--brand-green);
            color: #ffffff;
        }
        .pagination .page-item .page-link:hover {
            background-color: rgba(42, 64, 40, 0.1);
            color: var(--brand-green);
        }
        .pagination .page-item.disabled .page-link {
            color: #94a3b8;
            background-color: transparent;
            border-color: #d1d5db;
            cursor: not-allowed;
        }
    </style>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        document.querySelectorAll('.notification-read-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                const id = this.dataset.id;
                if (!id) {
                    return;
                }

                const url = "{{ route('client.notifications.markRead', ['id' => '__ID__']) }}".replace('__ID__', encodeURIComponent(id));
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({})
                }).then(function (response) {
                    if (!response.ok) {
                        console.error('Failed to mark notification read:', response.statusText);
                    }
                }).finally(function () {
                    window.location.reload();
                });
            });
        });

        const markAllButton = document.getElementById('markAllClientNotifications');
        markAllButton?.addEventListener('click', function () {
            fetch("{{ route('client.notifications.markAllRead') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({})
            }).then(function (response) {
                if (!response.ok) {
                    console.error('Failed to mark all notifications read:', response.statusText);
                }
            }).finally(function () {
                window.location.reload();
            });
        });
    });
</script>
@endpush
