@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'date' => now()->format('D, M d, Y'),
])

<div class="dashboard-page-header">
    <div class="dashboard-page-heading">
        @if($eyebrow)
            <span class="dashboard-page-eyebrow">{{ $eyebrow }}</span>
        @endif
        <h2 class="dashboard-page-title">{{ $title }}</h2>
        @if($description)
            <p class="dashboard-page-description">{{ $description }}</p>
        @endif
    </div>

    <div class="dashboard-page-tools" aria-label="Page utilities">
        {!! $extra ?? '' !!}
        <div class="dashboard-date-pill">
            <i class="bi bi-calendar3"></i>
            <span>{{ $date }}</span>
        </div>
        <div style="position: relative;">
            <button type="button" class="dashboard-notification-button notification-toggle-btn {{ ($clientUnreadCount ?? 0) > 0 ? 'notification-bell-animate' : '' }}" style="position: relative;" aria-label="Notifications">
                <i class="bi bi-bell"></i>
                @if(($clientUnreadCount ?? 0) > 0)
                    <span class="notification-badge" style="position:absolute;top:8px;right:8px;width:12px;height:12px;background:#22c55e;border:2px solid #ffffff;border-radius:50%;box-shadow:0 0 0 4px rgba(34,197,94,0.25);animation:ping-dot 1.4s ease-out infinite;"></span>
                @endif
            </button>
        </div>
    </div>
</div>
