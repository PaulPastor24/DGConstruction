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
        <button type="button" class="dashboard-notification-button" aria-label="Notifications">
            <i class="bi bi-bell"></i>
        </button>
    </div>
</div>
