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
        <div style="position: relative;">
            <button type="button" class="dashboard-notification-button notification-toggle-btn {{ ($clientUnreadCount ?? 0) > 0 ? 'notification-bell-animate' : '' }}" style="position: relative;" aria-label="Notifications">
                <i class="bi bi-bell"></i>
                @if(($clientUnreadCount ?? 0) > 0)
                    <span class="notification-badge" aria-label="{{ $clientUnreadCount ?? 0 }} unread notifications">{{ $clientUnreadCount ?? 0 }}</span>
                @endif
            </button>
        </div>
    </div>
</div>
