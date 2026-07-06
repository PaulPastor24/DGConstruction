@extends('layouts.supervisor')

@section('title', 'Supervisor Notifications')
@section('page_title', 'Notifications')

@push('styles')
    <style>
        :root {
            --cms-green-dark: #2a4028;
            --cms-green-light: #e8efe0;
            --cms-green-muted: rgba(42, 64, 40, 0.12);
            --cms-text-muted: #6c757d;
        }

        .metric-card, .main-notif-card {
            border-radius: 12px;
            border: 1px solid var(--cms-green-muted);
            background: #fff;
            box-shadow: 0 4px 12px rgba(9, 96, 86, 0.03);
        }

        .metric-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        /* Type Filter Chips */
        .filter-scroll-container {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 4px;
        }
        .filter-scroll-container::-webkit-scrollbar {
            height: 4px;
        }
        .filter-scroll-container::-webkit-scrollbar-thumb {
            background: var(--cms-green-muted);
            border-radius: 10px;
        }

        .btn-filter-chip {
            padding: 0.4rem 0.85rem;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 500;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            color: #495057;
            white-space: nowrap;
            transition: all 0.2s ease;
        }
        .btn-filter-chip:hover {
            background-color: #e9ecef;
        }
        .btn-filter-chip.active {
            background-color: var(--cms-green-dark);
            border-color: var(--cms-green-dark);
            color: #fff;
            font-weight: 600;
        }
        .btn-filter-chip .badge {
            font-size: 0.72rem;
            padding: 0.25rem 0.45rem;
            margin-left: 0.35rem;
        }

        /* Notification List Row Elements */
        .notif-list-group-item {
            padding: 1.15rem 1.25rem;
            border-bottom: 1px solid #f1f3f5;
            transition: background-color 0.15s ease;
        }
        .notif-list-group-item:last-child {
            border-bottom: none;
        }
        .notif-list-group-item:hover {
            background-color: #fafbfc;
        }

        .unread-dot-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #2a4028;
            display: inline-block;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .notif-list-group-item.unread-row .unread-dot-indicator {
            opacity: 1;
        }

        .notif-circle-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* Context Categorized Theme Mappings */
        .notif-report { background-color: #e8f5e9; color: #2e7d32; }
        .notif-phase { background-color: #e3f2fd; color: #1565c0; }
        .notif-timeline { background-color: #f3e5f5; color: #6a1b9a; }
        .notif-attendance { background-color: #ffebee; color: #c62828; }
        .notif-announcement { background-color: #fff8e1; color: #f57f17; }
        .notif-system { background-color: #eceff1; color: #37474f; }

        .badge-type-pill {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            padding: 0.3rem 0.65rem;
            border-radius: 4px;
            text-transform: uppercase;
        }
        
        .badge-type-pill.bg-report { background-color: #e8f5e9; color: #2e7d32; }
        .badge-type-pill.bg-phase { background-color: #e3f2fd; color: #1565c0; }
        .badge-type-pill.bg-timeline { background-color: #f3e5f5; color: #6a1b9a; }
        .badge-type-pill.bg-attendance { background-color: #ffebee; color: #c62828; }
        .badge-type-pill.bg-announcement { background-color: #fff8e1; color: #f57f17; }
        .badge-type-pill.bg-system { background-color: #eceff1; color: #37474f; }

        .btn-action-view {
            border: 1px solid var(--cms-green-dark);
            color: var(--cms-green-dark);
            background-color: transparent;
            font-size: 0.82rem;
            font-weight: 600;
            padding: 0.4rem 0.9rem;
            border-radius: 6px;
            white-space: nowrap;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-action-view:hover {
            background-color: var(--cms-green-dark);
            color: #fff;
            text-decoration: none;
        }
        .notif-circle-icon i { color: var(--cms-green-dark); }
    </style>
@endpush

@section('content')
@php
    // Use controller-provided summary counts when available
    $totalNotifs = $totalNotifs ?? ($notifications->total() ?? ($notifications->count() ?? 0));
    $unreadCount = $unreadCount ?? 0;
    $readCount = $readCount ?? 0;
    $archivedCount = $archivedCount ?? 0;
@endphp

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card p-3 d-flex align-items-center gap-3">
            <div class="metric-icon-wrapper bg-success-subtle text-success">
                <i class="bi bi-bell"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold">Total Notifications</div>
                <h4 class="mb-0 fw-bold">{{ $totalNotifs }}</h4>
                <span class="text-muted" style="font-size: 0.75rem;">All notifications</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card p-3 d-flex align-items-center gap-3">
            <div class="metric-icon-wrapper bg-warning-subtle text-warning">
                <i class="bi bi-envelope"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold">Unread</div>
                <h4 class="mb-0 fw-bold">{{ $unreadCount }}</h4>
                <span class="text-muted" style="font-size: 0.75rem;">Pending action</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card p-3 d-flex align-items-center gap-3">
            <div class="metric-icon-wrapper bg-primary-subtle text-primary">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold">Read</div>
                <h4 class="mb-0 fw-bold">{{ $readCount }}</h4>
                <span class="text-muted" style="font-size: 0.75rem;">Completed</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card p-3 d-flex align-items-center gap-3">
            <div class="metric-icon-wrapper bg-danger-subtle text-danger">
                <i class="bi bi-archive"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold">Archived</div>
                <h4 class="mb-0 fw-bold">{{ $archivedCount }}</h4>
                <span class="text-muted" style="font-size: 0.75rem;">Archived</span>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
    <div class="filter-scroll-container w-100 style-scrollbar">
        <a href="?type=all" class="btn-filter-chip {{ request('type', 'all') == 'all' ? 'active' : '' }}">All</a>
        <a href="?type=unread" class="btn-filter-chip {{ request('type') == 'unread' ? 'active' : '' }}">
            Unread <span class="badge rounded-pill bg-success">{{ $unreadCount }}</span>
        </a>
        <a href="?type=read" class="btn-filter-chip {{ request('type') == 'read' ? 'active' : '' }}">Read</a>
        <a href="?type=project" class="btn-filter-chip {{ request('type') == 'project' ? 'active' : '' }}">Project</a>
        <a href="?type=report" class="btn-filter-chip {{ request('type') == 'report' ? 'active' : '' }}">Report</a>
        <a href="?type=phase" class="btn-filter-chip {{ request('type') == 'phase' ? 'active' : '' }}">Phase</a>
        <a href="?type=timeline" class="btn-filter-chip {{ request('type') == 'timeline' ? 'active' : '' }}">Timeline</a>
        <a href="?type=attendance" class="btn-filter-chip {{ request('type') == 'attendance' ? 'active' : '' }}">Attendance</a>
        <a href="?type=announcement" class="btn-filter-chip {{ request('type') == 'announcement' ? 'active' : '' }}">Announcement</a>
        <a href="?type=system" class="btn-filter-chip {{ request('type') == 'system' ? 'active' : '' }}">System</a>
    </div>
    
    <div class="d-flex gap-2 flex-shrink-0 align-self-end align-self-md-auto">
        <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold d-flex align-items-center gap-1" style="font-size: 0.82rem; border-color: #ced4da;">
            <i class="bi bi-check2-all text-success"></i> Mark all as read
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold d-flex align-items-center gap-1" style="font-size: 0.82rem; border-color: #ced4da;">
            <i class="bi bi-funnel"></i> Filter <i class="bi bi-chevron-down small"></i>
        </button>
    </div>
</div>

<section class="main-notif-card overflow-hidden mb-4">
    <div class="d-flex flex-column">
        @if($notifications->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-envelope-open display-6 mb-2 d-block"></i>
                No notifications found matching selected filters.
            </div>
        @else
            @foreach($notifications as $notification)
                @php
                    $isUnread = !($notification->is_read ?? ($notification['is_read'] ?? false));
                    $rawType = strtolower($notification['type'] ?? ($notification->type ?? 'system'));
                    
                    // Maps appropriate icons cleanly using bootstrap iconography
                    $iconMapping = [
                        'report'       => 'bi-file-earmark-text',
                        'phase'        => 'bi-bar-chart-steps',
                        'timeline'     => 'bi-calendar3',
                        'attendance'   => 'bi-people',
                        'announcement' => 'bi-megaphone',
                        'system'       => 'bi-gear'
                    ];
                    $currentIcon = $iconMapping[$rawType] ?? 'bi-bell';

                    // Context action title mapping setup 
                    $actionTextMapping = [
                        'report'       => 'View Report',
                        'phase'        => 'View Phase',
                        'timeline'     => 'View Timeline',
                        'attendance'   => 'Open Attendance',
                        'announcement' => 'View Announcement',
                        'system'       => 'View Details'
                    ];
                    $actionBtnLabel = $actionTextMapping[$rawType] ?? 'View';
                @endphp

                <div class="notif-list-group-item d-flex align-items-start gap-3 {{ $isUnread ? 'unread-row' : '' }}">
                    <div class="pt-2">
                        <span class="unread-dot-indicator"></span>
                    </div>

                    <div class="notif-circle-icon notif-{{ $rawType }}">
                        <i class="bi {{ $currentIcon }}"></i>
                    </div>

                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">{{ $notification['title'] }}</div>
                        <div class="text-secondary my-1" style="font-size: 0.88rem; line-height: 1.4;">
                            {{ $notification['message'] }}
                        </div>
                        @if(isset($notification['report_id']))
                            <div class="text-muted font-monospace mb-1" style="font-size: 0.78rem;">
                                <i class="bi bi-shield-check"></i> Report ID: RPT-2026-{{ str_pad($notification['report_id'], 4, '0', STR_PAD_LEFT) }}
                            </div>
                        @endif
                    </div>

                    <div class="d-none d-md-block text-nowrap">
                        <span class="badge-type-pill badge-type-pill px-2 py-1 bg-{{ $rawType }}">
                            {{ $notification['type'] ?? 'General' }}
                        </span>
                    </div>

                    <div class="text-nowrap d-none d-sm-block text-start" style="width: 110px;">
                        <div class="fw-bold text-dark" style="font-size: 0.8rem;">
                            {{ isset($notification['created_at']) ? $notification['created_at']->format('M d, Y') : 'Jul 03, 2026' }}
                        </div>
                        <div class="text-muted" style="font-size: 0.72rem;">
                            {{ isset($notification['created_at']) ? $notification['created_at']->diffForHumans() : 'Just now' }}
                        </div>
                    </div>

                    <div class="text-end flex-shrink-0">
                        @php
                            $href = '#';
                            try {
                                $href = route($notification['module'] ?? ($notification->data['module'] ?? 'supervisor.dashboard'));
                            } catch (\Throwable $e) {
                                $href = route('supervisor.dashboard');
                            }
                        @endphp
                        <a href="{{ $href }}" class="btn-action-view notif-action-view" data-notif-id="{{ $notification->id ?? $notification['id'] ?? '' }}">
                            {{ $actionBtnLabel }}
                        </a>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="p-3 bg-light d-flex justify-content-between align-items-center border-top">
        <div class="small text-muted">
            Showing {{ $notifications->firstItem() ?? 0 }} to {{ $notifications->lastItem() ?? 0 }} of {{ $notifications->total() ?? $notifications->count() }} notifications
        </div>
        <nav aria-label="Page navigation example">
            {!! $notifications->links('pagination::bootstrap-5') !!}
        </nav>
    </div>
</section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '{{ csrf_token() }}';

            // Mark all as read
            document.querySelectorAll('.btn.btn-sm').forEach(btn => {
                if (btn.textContent.includes('Mark all as read')) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Mark all notifications as read?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, Mark All',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#166534'
                        }).then(res => {
                            if (res.isConfirmed) {
                                fetch('{{ route('supervisor.notifications.markAllRead') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrf
                                    },
                                    body: JSON.stringify({})
                                }).then(r => r.json()).then(json => {
                                    if (json.success) {
                                        Swal.fire({ title: 'All notifications have been marked as read.', icon: 'success', confirmButtonColor: '#166534' })
                                        .then(() => location.reload());
                                    } else {
                                        Swal.fire({ title: 'Error', text: json.message || 'Failed to mark all as read', icon: 'error', confirmButtonColor: '#166534' });
                                    }
                                }).catch(err => {
                                    Swal.fire({ title: 'Error', text: 'Request failed', icon: 'error', confirmButtonColor: '#166534' });
                                });
                            }
                        });
                    });
                }
            });

            // Mark individual and navigate when clicking view
            document.querySelectorAll('.notif-action-view').forEach(el => {
                el.addEventListener('click', function(e) {
                    const id = this.getAttribute('data-notif-id');
                    const href = this.getAttribute('href');
                    if (id) {
                        e.preventDefault();
                        fetch(`/supervisor/notifications/${id}/mark-read`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            },
                            body: JSON.stringify({})
                        }).then(r => r.json()).then(json => {
                            window.location = href;
                        }).catch(() => {
                            window.location = href;
                        });
                    }
                });
            });
        });
    </script>
@endpush