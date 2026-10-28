import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/js/system-loading.js', import.meta.url), 'utf8');
const deferred = () => {
    let resolve, reject;
    const promise = new Promise((yes, no) => { resolve = yes; reject = no; });
    return { promise, resolve, reject };
};

function setup() {
    let now = 0, nextTimer = 0;
    const timers = new Map(), documentEvents = new Map(), windowEvents = new Map();
    const classes = new Set();
    const overlay = { hidden: true, classList: { toggle(name, enabled) { enabled ? classes.add(name) : classes.delete(name); } } };
    const elements = { systemLoading: overlay, systemLoadingTitle: {}, systemLoadingMessage: {} };
    const requests = [];
    const addListener = events => (name, callback) => {
        if (!events.has(name)) events.set(name, []);
        events.get(name).push(callback);
    };
    class Xhr {
        listeners = new Map();
        open() {}
        send() {}
        addEventListener(name, callback) { this.listeners.set(name, callback); }
    }
    class Form { submit() {} }
    const context = {
        URL, queueMicrotask, XMLHttpRequest: Xhr, HTMLFormElement: Form,
        location: new URL('https://example.test/admin/dashboard'),
        Date: { now: () => now },
        setTimeout(callback, delay) { const id = ++nextTimer; timers.set(id, { callback, due: now + delay }); return id; },
        clearTimeout(id) { timers.delete(id); },
        document: { getElementById: id => elements[id], addEventListener: addListener(documentEvents) },
        addEventListener: addListener(windowEvents),
        fetch() { const request = deferred(); requests.push(request); return request.promise; },
    };
    context.window = context;
    vm.runInNewContext(source, context);
    const emit = (events, name, event = {}) => (events.get(name) || []).forEach(callback => callback(event));
    function advance(ms) {
        const end = now + ms;
        while (true) {
            const task = [...timers].filter(([, timer]) => timer.due <= end).sort((a, b) => a[1].due - b[1].due)[0];
            if (!task) break;
            timers.delete(task[0]); now = task[1].due; task[1].callback();
        }
        now = end;
    }
    return {
        context, overlay, classes, requests, advance,
        emitWindow: (name, event) => emit(windowEvents, name, event),
        emitDocument: (name, event) => emit(documentEvents, name, event),
        ready() { emit(windowEvents, 'load'); advance(500); },
    };
}

test('initial page loading uses the overlay and clears after load', () => {
    const app = setup();
    app.advance(180);
    assert.equal(app.overlay.hidden, false);
    assert.equal(app.classes.has('is-request'), false);
    app.emitWindow('load');
    app.advance(300);
    assert.equal(app.overlay.hidden, true);
});

test('concurrent fetches keep the non-blocking indicator until all settle, including rejection', async () => {
    const app = setup(); app.ready();
    const first = app.context.fetch('/admin/reports');
    const second = app.context.fetch('/admin/projects');
    app.advance(180);
    assert.equal(app.overlay.hidden, false);
    assert.equal(app.classes.has('is-request'), true);
    app.requests[0].resolve({}); await first;
    app.advance(300);
    assert.equal(app.overlay.hidden, false);
    app.requests[1].reject(new Error('Offline'));
    await assert.rejects(second, /Offline/);
    app.advance(300);
    assert.equal(app.overlay.hidden, true);
});

test('response body reads remain tracked after headers arrive', async () => {
    const app = setup(); app.ready();
    const pending = app.context.fetch('/admin/reports');
    const body = deferred();
    app.requests[0].resolve({ json: () => body.promise });
    const response = await pending;
    const result = response.json();
    app.advance(180);
    assert.equal(app.overlay.hidden, false);
    body.resolve({ success: true }); await result;
    app.advance(300);
    assert.equal(app.overlay.hidden, true);
});

test('presence heartbeat requests do not trigger feedback', async () => {
    const app = setup(); app.ready();
    const pending = app.context.fetch('/presence/heartbeat');
    app.advance(500);
    assert.equal(app.overlay.hidden, true);
    app.requests[0].resolve({}); await pending;
});

test('cancelled navigation and download links do not leave an overlay', async () => {
    const app = setup(); app.ready();
    const link = { href: 'https://example.test/admin/projects', target: '', hasAttribute: () => false, closest: () => null };
    const event = { target: { closest: () => link }, button: 0, defaultPrevented: false };
    app.emitDocument('click', event);
    event.defaultPrevented = true;
    await Promise.resolve(); app.advance(500);
    assert.equal(app.overlay.hidden, true);
    link.href = 'https://example.test/admin/reports/1/download-pdf';
    event.defaultPrevented = false;
    app.emitDocument('click', event);
    await Promise.resolve(); app.advance(500);
    assert.equal(app.overlay.hidden, true);
    link.href = 'https://example.test/admin/projects';
    app.emitDocument('click', event);
    await Promise.resolve(); app.advance(180);
    assert.equal(app.overlay.hidden, false);
    app.emitWindow('pageshow', { persisted: true });
    assert.equal(app.overlay.hidden, true);
});

test('XHR failures clear the indicator and stalled navigation eventually unblocks', () => {
    const app = setup(); app.ready();
    const xhr = new app.context.XMLHttpRequest();
    xhr.open('POST', '/admin/reports'); xhr.send();
    app.advance(180);
    assert.equal(app.overlay.hidden, false);
    xhr.listeners.get('loadend')(); app.advance(300);
    assert.equal(app.overlay.hidden, true);
    app.context.SystemLoading.start(true); app.advance(180);
    assert.equal(app.overlay.hidden, false);
    app.advance(16000);
    assert.equal(app.overlay.hidden, true);
});
