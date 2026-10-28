(() => {
    if (window.SystemLoading) return;
    const operations = new Map();
    let sequence = 0;
    let showTimer;
    let hideTimer;
    let shownAt = 0;

    function render() {
        const element = document.getElementById('systemLoading');
        if (!element) return;
        const navigation = Array.from(operations.values()).some(operation => operation.page);
        const title = document.getElementById('systemLoadingTitle');
        const message = document.getElementById('systemLoadingMessage');
        element.classList.toggle('is-request', !navigation);
        title.textContent = navigation ? 'Loading your workspace' : 'Updating your workspace';
        message.textContent = navigation ? 'Please wait a moment.' : 'Your request is being processed.';
        if (operations.size) {
            if (element.hidden) shownAt = Date.now();
            element.hidden = false;
        } else {
            element.hidden = true;
        }
    }

    function start(page = false) {
        const id = ++sequence;
        // A failed navigation or stalled request must never trap the user behind the overlay.
        const timeout = setTimeout(() => finish(id), page ? 15000 : 60000);
        operations.set(id, { page, timeout });
        clearTimeout(hideTimer);
        if (!showTimer) showTimer = setTimeout(() => { showTimer = null; render(); }, 180);
        return id;
    }

    function finish(id) {
        clearTimeout(operations.get(id)?.timeout);
        operations.delete(id);
        if (operations.size) {
            const element = document.getElementById('systemLoading');
            if (element && !element.hidden) render();
            return;
        }
        clearTimeout(showTimer);
        showTimer = null;
        clearTimeout(hideTimer);
        hideTimer = setTimeout(render, Math.max(0, 300 - (Date.now() - shownAt)));
    }

    function reset() {
        operations.forEach(operation => clearTimeout(operation.timeout));
        operations.clear();
        clearTimeout(showTimer);
        clearTimeout(hideTimer);
        showTimer = null;
        render();
    }

    function track(url) {
        try {
            const target = new URL(url, location.href);
            return target.origin === location.origin && !/\/presence(?:\/|$)|\/heartbeat(?:\/|$)/i.test(target.pathname);
        } catch { return false; }
    }

    function isDownload(url) {
        return /\/(?:download[^/]*|export[^/]*|[^/]*pdf)(?:\/|$)|\.(?:pdf|csv|xlsx?|docx?|zip)(?:$|[?#])/i.test(url.pathname);
    }

    function navigates(url, target) {
        try {
            const destination = new URL(url, location.href);
            return (!target || target === '_self') && destination.origin === location.origin
                && ['http:', 'https:'].includes(destination.protocol) && !isDownload(destination)
                && (destination.pathname !== location.pathname || destination.search !== location.search);
        } catch { return false; }
    }

    const nativeFetch = window.fetch;
    if (nativeFetch) {
        window.fetch = function (input, options) {
            if (options?.systemLoading === false || !track(typeof input === 'string' || input instanceof URL ? input : input.url)) {
                return nativeFetch.apply(this, arguments);
            }
            const id = start();
            try {
                return nativeFetch.apply(this, arguments).then(response => {
                    // Keep feedback visible while callers consume a response body, too.
                    for (const method of ['json', 'text', 'blob', 'arrayBuffer', 'formData']) {
                        const read = response[method];
                        if (typeof read !== 'function') continue;
                        response[method] = function (...args) {
                            const bodyId = start();
                            try { return Promise.resolve(read.apply(this, args)).finally(() => finish(bodyId)); }
                            catch (error) { finish(bodyId); throw error; }
                        };
                    }
                    return response;
                }).finally(() => finish(id));
            } catch (error) { finish(id); throw error; }
        };
    }

    const nativeOpen = XMLHttpRequest.prototype.open;
    const nativeSend = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.open = function (method, url) {
        this._systemLoadingUrl = url;
        return nativeOpen.apply(this, arguments);
    };
    XMLHttpRequest.prototype.send = function () {
        if (!track(this._systemLoadingUrl) || this.systemLoading === false) return nativeSend.apply(this, arguments);
        const id = start();
        this.addEventListener('loadend', () => finish(id), { once: true });
        try { return nativeSend.apply(this, arguments); }
        catch (error) { finish(id); throw error; }
    };

    document.addEventListener('click', event => {
        const link = event.target.closest?.('a[href]');
        if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey
            || link.hasAttribute('download') || link.closest('[data-no-global-loading]')) return;
        queueMicrotask(() => {
            if (!event.defaultPrevented && navigates(link.href, link.target)) start(true);
        });
    }, true);
    document.addEventListener('submit', event => {
        const form = event.target;
        queueMicrotask(() => {
            const action = new URL(event.submitter?.hasAttribute('formaction') ? event.submitter.formAction : form.action, location.href);
            const target = event.submitter?.formTarget || form.target;
            if (!event.defaultPrevented && !form.hasAttribute('data-no-global-loading')
                && (!target || target === '_self') && track(action.href) && !isDownload(action)) start(true);
        });
    }, true);
    const nativeSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function () {
        const action = new URL(this.action, location.href);
        const id = !this.hasAttribute('data-no-global-loading') && (!this.target || this.target === '_self')
            && track(action.href) && !isDownload(action) ? start(true) : null;
        try { return nativeSubmit.apply(this, arguments); }
        catch (error) { if (id) finish(id); throw error; }
    };

    window.SystemLoading = { start, finish, reset };
    const initial = start(true);
    window.addEventListener('load', () => finish(initial), { once: true });
    window.addEventListener('pageshow', event => { if (event.persisted) reset(); });
    document.addEventListener('DOMContentLoaded', () => {
        if (operations.size && !showTimer) render();
    }, { once: true });
})();
