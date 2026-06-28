@extends('layouts.supervisor')

@section('title', 'Supervisor Notifications')
@section('page_title', 'Notifications')

@push('styles')
    <style>
        .notif-hero { padding: 1.15rem 1.2rem; }
        .notif-chip { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.45rem 0.7rem; border-radius: 999px; background: rgba(9,96,86,0.08); color: var(--supervisor-primary); font-size: 0.82rem; font-weight: 700; }
        .notif-card { border-radius: 18px; border: 1px solid rgba(9,96,86,0.08); background: #fff; box-shadow: 0 10px 24px rgba(9,96,86,0.05); }
        .notif-card .section-card-body { gap: 0.8rem; }
        .notif-row { display: flex; justify-content: space-between; gap: 1rem; align-items: start; padding: 0.9rem 0; border-bottom: 1px solid rgba(9,96,86,0.08); }
        .notif-row:last-child { border-bottom: none; }
        .notif-title { font-weight: 700; color: var(--supervisor-text); }
        .notif-message { color: var(--supervisor-muted); font-size: 0.92rem; } 
        .notif-pill { display: inline-flex; align-items: center; padding: 0.38rem 0.7rem; border-radius: 999px; font-size: 0.76rem; font-weight: 700; }
        .notif-pill.high { background: #fef2f2; color: #b91c1c; }
        .notif-pill.medium { background: #fefce8; color: #a16207; }
        .notif-pill.low { background: #f0fdf4; color: #166534; }
        .notif-filters { display: flex; flex-wrap: wrap; gap: 0.6rem; }
    </style>
@endpush

@section('content')
<section class="page-card notif-hero mb-3">
    <div class="page-card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
        <div>
            <div class="eyebrow">Inbox Workspace</div>
            <h1 class="page-title mb-2">Notifications</h1>
            <p class="page-subtitle mb-0">Keep your site updates, reminders, and approvals in one professional workspace.</p>
        </div>
        <div class="notif-filters">
            <span class="notif-chip"><i class="bi bi-envelope-open"></i> {{ $notifications->where('status', 'Unread')->count() }} unread</span>
            <span class="notif-chip"><i class="bi bi-clock-history"></i> Updated today</span>
        </div>
    </div>
</section>

<section class="section-card">
    <div class="section-card-body">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
            <div>
                <h5 class="fw-bold mb-1">Recent notifications</h5>
                <p class="text-muted small mb-0">Actionable updates from reports, materials, and project timelines.</p>
            </div>
        </div>

        <div class="notif-card p-3">
            @foreach($notifications as $notification)
                <div class="notif-row">
                    <div>
                        <div class="notif-title">{{ $notification['title'] }}</div>
                        <div class="notif-message">{{ $notification['message'] }}</div>
                        <div class="small text-muted mt-2">{{ $notification['type'] }} • {{ $notification['created_at']->diffForHumans() }}</div>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        <span class="notif-pill {{ strtolower($notification['priority']) }}">{{ $notification['priority'] }}</span>
                        <a href="{{ route($notification['module']) }}" class="btn btn-sm btn-outline-soft">Open</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
