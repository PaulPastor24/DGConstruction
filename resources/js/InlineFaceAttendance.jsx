import React, { useEffect, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';
import FaceCamera from './FaceCamera';

export function mountInlineAttendance(mount) {
  const urls = mount.dataset;
  async function send(url, body) {
    const response = await fetch(url, {
      method: body === undefined ? 'GET' : 'POST', credentials: 'same-origin',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': urls.csrf },
      ...(body === undefined ? {} : { body: JSON.stringify(body) }),
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(payload.message || `Request failed (${response.status}).`);
    return payload;
  }
  function InlineAttendance() {
    const [operation, setOperation] = useState(null);
    const [session, setSession] = useState(null);
    const [matched, setMatched] = useState(null);
    const [confirmed, setConfirmed] = useState(false);
    const [notice, setNotice] = useState(false);
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState('');
    const [message, setMessage] = useState('');
    const generation = useRef(0);
    const currentSession = useRef(null);
    const working = useRef(false);

    useEffect(() => {
      const prompt = document.getElementById('btnGlobalScan')?.closest('.scan-pulse-container');
      const hint = document.getElementById('globalScanStatus');
      if (prompt) prompt.style.display = operation ? 'none' : '';
      if (hint) hint.hidden = !!operation;
    }, [operation]);

    async function startScan(next) {
      if (working.current) return;
      working.current = true;
      const token = ++generation.current;
      setBusy(true); setError(''); setMatched(null);
      try {
        const result = await send(urls.start, {
          purpose: next.purpose,
          ...(next.purpose === 'enroll' ? { worker_id: next.workerId, supervisor_confirmed_identity: true, privacy_notice_acknowledged: true } : {}),
        });
        if (generation.current !== token) { await send(urls.cancel, { session_id: result.session_id }); return; }
        currentSession.current = result;
        setSession(result);
      } catch (e) { if (generation.current === token) setError(e.message); }
      finally { working.current = false; if (generation.current === token) setBusy(false); }
    }

    async function cancel() {
      ++generation.current;
      const old = currentSession.current;
      currentSession.current = null;
      setSession(null); setMatched(null); setOperation(null); setBusy(false); setError('');
      await send(urls.cancel, old ? { session_id: old.session_id } : {}).catch(() => {});
    }

    useEffect(() => {
      const open = async event => {
        if (working.current || currentSession.current) return;
        const next = event.detail;
        setOperation(next); setError(''); setMessage(''); setMatched(null); setConfirmed(false); setNotice(false);
        mount.scrollIntoView({ behavior: 'smooth', block: 'center' });
        if (next.purpose === 'attendance') {
          await startScan(next);
        } else {
          try {
            const result = await send(urls.workers);
            const worker = result.faceWorkerChoices.find(w => Number(w.worker_id) === Number(next.workerId));
            if (!worker) throw new Error('Worker is not assigned to your active projects.');
            setOperation({ ...next, name: worker.name });
          } catch (e) { setError(e.message); }
        }
      };
      window.addEventListener('worker-camera-open', open);
      return () => window.removeEventListener('worker-camera-open', open);
    }, []);

    async function complete(samples, preview) {
      if (!currentSession.current || working.current) return;
      const token = generation.current;
      const scan = currentSession.current;
      working.current = true; setBusy(true); setError('');
      try {
        const result = await send(urls.finish, { session_id: scan.session_id, model_version: scan.model_version, samples });
        if (generation.current !== token) return;
        currentSession.current = null; setSession(null);
        if (result.purpose === 'enroll') {
          setOperation(null);
          setMessage(`${result.worker.first_name} ${result.worker.last_name}: face saved. Use the face button to record attendance.`);
          window.dispatchEvent(new Event('worker-face-enrolled'));
        } else setMatched({ ...result, preview });
      } catch (e) {
        if (generation.current === token) { currentSession.current = null; setSession(null); setError(e.message); }
      } finally { working.current = false; if (generation.current === token) setBusy(false); }
    }

    async function save() {
      if (!matched || working.current) return;
      working.current = true; setBusy(true); setError('');
      try {
        await send(urls.save, {
          worker_id: matched.worker.worker_id, log_date: matched.log_date,
          action: 'time_in', verification_method: 'face', supervisor_confirmed_identity: true,
        });
        setMessage(`${matched.worker.first_name} ${matched.worker.last_name}: Time In saved. Ready for the next worker.`);
        setMatched(null); setOperation(null);
        window.dispatchEvent(new Event('worker-attendance-saved'));
      } catch (e) { setError(e.message); setMatched(null); }
      finally { working.current = false; setBusy(false); }
    }

    return <div className="face-inline">
      {message && <p className="alert alert-success mt-3" role="status">{message}</p>}
      {error && <p className="alert alert-danger mt-3" role="alert">{error}</p>}
      {busy && <p role="status" className="mt-3">Please wait...</p>}
      {operation?.purpose === 'enroll' && !session && !matched && <div className="text-start mt-3">
        <h6>Enroll face: {operation.name || 'worker'}</h6>
        <label className="d-block mb-2"><input type="checkbox" checked={confirmed} onChange={e => setConfirmed(e.target.checked)} /> I verified this worker's identity in person.</label>
        <label className="d-block mb-2"><input type="checkbox" checked={notice} onChange={e => setNotice(e.target.checked)} /> The worker received the biometric privacy notice and an authorized alternative.</label>
        <button className="btn btn-success" disabled={busy || !confirmed || !notice || !operation.name} onClick={() => startScan(operation)}>Open enrollment camera</button>
      </div>}
      {session && <FaceCamera key={session.session_id} disabled={busy} onComplete={complete} onError={reason => {
        currentSession.current = null; setSession(null); setError(reason);
        send(urls.cancel, { session_id: session.session_id }).catch(() => {});
      }} />}
      {matched && <div className="text-start mt-3">
        <div className="d-flex gap-2 mb-2">
          <img src={matched.preview} alt="Current camera capture" width="100" style={{ objectFit: 'cover', borderRadius: 8 }} />
          {matched.worker.profile_image_url && <img src={matched.worker.profile_image_url} alt="Worker profile" width="100" style={{ objectFit: 'cover', borderRadius: 8 }} />}
        </div>
        <strong>{matched.worker.first_name} {matched.worker.last_name}</strong>
        <p>Confirm the person in front of you matches this worker.</p>
        <button className="btn btn-success" disabled={busy} onClick={save}>Confirm &amp; save Time In</button>
      </div>}
      {operation?.purpose === 'attendance' && !session && !matched && !busy && <button className="btn btn-success mt-2" onClick={() => startScan(operation)}>Retry camera</button>}
      {operation && <button className="btn btn-light border mt-2 ms-2" disabled={busy} onClick={cancel}>{matched ? 'Reject match' : 'Close camera'}</button>}
    </div>;
  }
  createRoot(mount).render(<InlineAttendance />);
}
