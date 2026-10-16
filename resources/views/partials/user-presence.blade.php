@auth
<script>
(() => {
    const heartbeat = () => {
        if (document.visibilityState !== 'visible') return;
        fetch(@json(route('presence.heartbeat')), {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
        }).catch(() => {});
    };
    heartbeat();
    window.setInterval(heartbeat, 60000);
    document.addEventListener('visibilitychange', heartbeat);
})();
</script>
@endauth
