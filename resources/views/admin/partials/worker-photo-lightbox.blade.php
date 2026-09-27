<div id="adminWorkerPhotoLightbox" class="admin-worker-photo-lightbox" role="dialog" aria-modal="true" aria-label="Worker profile photo" hidden>
    <button type="button" id="adminWorkerPhotoLightboxClose" class="admin-worker-photo-lightbox-close" aria-label="Back to attendance" title="Back to attendance">
        <i class="bi bi-x-lg" aria-hidden="true"></i>
    </button>
    <div class="admin-worker-photo-lightbox-content">
        <img id="adminWorkerPhotoLightboxImage" class="admin-worker-photo-lightbox-image" alt="">
        <p id="adminWorkerPhotoLightboxCaption" class="admin-worker-photo-lightbox-caption"></p>
    </div>
</div>

<script>
    (() => {
        if (window.adminWorkerPhotoLightboxInitialized) return;
        window.adminWorkerPhotoLightboxInitialized = true;

        const lightbox = document.getElementById('adminWorkerPhotoLightbox');
        const image = document.getElementById('adminWorkerPhotoLightboxImage');
        const caption = document.getElementById('adminWorkerPhotoLightboxCaption');
        const closeButton = document.getElementById('adminWorkerPhotoLightboxClose');
        let returnFocus = null;
        let previousBodyOverflow = '';

        if (!lightbox || !image || !caption || !closeButton) return;

        const closeLightbox = () => {
            lightbox.hidden = true;
            image.removeAttribute('src');
            caption.textContent = '';
            document.body.style.overflow = previousBodyOverflow;
            returnFocus?.focus();
            returnFocus = null;
        };

        document.addEventListener('click', event => {
            const trigger = event.target.closest('[data-worker-photo-zoom]');

            if (trigger) {
                const thumbnail = trigger.querySelector('img');
                const photoUrl = trigger.dataset.workerPhotoZoom || thumbnail?.currentSrc || thumbnail?.src;

                if (!photoUrl || thumbnail?.hidden) return;

                returnFocus = trigger;
                previousBodyOverflow = document.body.style.overflow;
                image.src = photoUrl;
                image.alt = `${trigger.dataset.workerPhotoName || 'Worker'} profile photo`;
                caption.textContent = trigger.dataset.workerPhotoName || 'Worker profile photo';
                lightbox.hidden = false;
                document.body.style.overflow = 'hidden';
                closeButton.focus();
                return;
            }

            if (event.target === lightbox) {
                closeLightbox();
            }
        });

        closeButton.addEventListener('click', closeLightbox);
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !lightbox.hidden) {
                event.preventDefault();
                event.stopImmediatePropagation();
                closeLightbox();
            }
        }, true);
    })();
</script>
