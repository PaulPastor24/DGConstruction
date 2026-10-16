<div class="avatar-circle-ui {{ !empty($mobile) ? 'ug-user-avatar' : 'shadow-sm' }} d-flex align-items-center justify-content-center fw-bold text-white uppercase position-relative overflow-hidden flex-shrink-0" data-user-avatar="{{ $user->user_id }}">
    <span>{{ strtoupper(substr($user->first_name ?? $user->name ?? 'U', 0, 1) . substr($user->last_name ?? '', 0, 1)) }}</span>
    @if($user->profile_photo_url)
        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }} profile photo" loading="lazy"
             style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;"
             onerror="this.hidden=true;">
    @endif
</div>
