@php
    $presenceLabel = !$user->is_active ? 'Disabled' : (in_array($user->user_id, $onlineUserIds) ? 'Online' : 'Offline');
    $presenceColor = $presenceLabel === 'Online' ? 'success' : ($presenceLabel === 'Disabled' ? 'warning' : 'secondary');
@endphp
<span data-user-presence="{{ $user->user_id }}" class="badge bg-{{ $presenceColor }}-subtle text-{{ $presenceColor }} border border-{{ $presenceColor }}-subtle rounded-pill px-2 py-1 fw-semibold d-inline-flex align-items-center gap-1">
    <span data-presence-dot class="pulse-indicator bg-{{ $presenceColor }}"></span>
    <span data-presence-label>{{ $presenceLabel }}</span>
</span>
