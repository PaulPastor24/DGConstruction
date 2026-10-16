import React, { useEffect, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';
import FaceCamera from './FaceCamera';
import { mountInlineAttendance } from './InlineFaceAttendance';

const mount = document.getElementById('face-attendance-root');
const workers = JSON.parse(document.getElementById('face-workers-json')?.textContent || '[]');
const urls = mount ? mount.dataset : {};
const launch = new URLSearchParams(window.location.search);
const initialPurpose = launch.get('purpose') === 'enroll' ? 'enroll' : 'attendance';
const initialWorkerId = workers.some(w => String(w.worker_id) === launch.get('worker_id')) ? launch.get('worker_id') : '';
function notifyParent(type) {
  if (launch.get('embedded') === '1' && window.parent !== window) {
    window.parent.postMessage({ type }, window.location.origin);
  }
}

async function send(url, body, method = 'POST') {
  const response = await fetch(url, {
    method, credentials: 'same-origin',
    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': urls.csrf },
    body: JSON.stringify(body),
  });
  const payload = await response.json().catch(() => ({}));
  if (!response.ok) throw new Error(payload.message || `Request failed (${response.status}).`);
  return payload;
}

function App() {
  const [purpose, setPurpose] = useState(initialPurpose);
  const [workerId, setWorkerId] = useState(initialWorkerId);
  const launched = useRef(false);
  const [identityConfirmed, setIdentityConfirmed] = useState(false);
  const [privacyAcknowledged, setPrivacyAcknowledged] = useState(false);
  const [session, setSession] = useState(null);
  const [matched, setMatched] = useState(null);
  const [busy, setBusy] = useState(false);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [finished, setFinished] = useState([]);
  const [enrolled, setEnrolled] = useState(workers.filter(w => w.enrolled).map(w => Number(w.worker_id)));
  const cameraCapable = typeof navigator !== 'undefined' && !!navigator.mediaDevices?.getUserMedia;
  const secure = window.isSecureContext;

  useEffect(() => {
    if (launch.get('embedded') === '1' && initialPurpose === 'attendance' && secure && cameraCapable && !launched.current) {
      launched.current = true;
      start();
    }
  }, []);

  const reset = () => { setSession(null); setMatched(null); setMessage(''); setError(''); };
  const cancel = async () => {
    const old = session;
    reset();
    if (old?.session_id) { try { await send(urls.cancel, { session_id: old.session_id }); } catch (_) {} }
  };

  async function removeEnrollment() {
    if (!workerId) { setError('Choose the worker first.'); return; }
    const person = workers.find(w => String(w.worker_id) === String(workerId));
    if (!window.confirm(`Permanently delete the local face enrollment for ${person?.name || 'this worker'}? Existing attendance logs remain.`)) return;
    setBusy(true); setError(''); setMessage('');
    try {
      await send(`${urls.removePrefix}/${encodeURIComponent(workerId)}`, {confirm: true}, 'DELETE');
      setEnrolled(old => old.filter(i => i !== Number(workerId)));
      setMessage('Local face enrollment deleted. Existing attendance records are preserved.');
    } catch (e) { setError(e.message); }
    finally { setBusy(false); }
  }

  async function start() {
    setBusy(true); reset();
    try {
      const payload = { purpose };
      if (purpose === 'enroll') {
        if (!workerId) throw new Error('Select the worker being enrolled.');
        if (!identityConfirmed || !privacyAcknowledged) throw new Error('Confirm in-person identity and privacy notice before enrollment.');
        payload.worker_id = Number(workerId);
        payload.supervisor_confirmed_identity = true;
        payload.privacy_notice_acknowledged = true;
      }
      const result = await send(urls.start, payload);
      setSession(result);
    } catch (e) { setError(e.message); }
    finally { setBusy(false); }
  }

  async function onComplete(samples, preview) {
    if (!session) return;
    setBusy(true);
    try {
      const result = await send(urls.finish, { session_id: session.session_id, model_version: session.model_version, samples });
      setSession(null);
      if (result.purpose === 'enroll') {
        setEnrolled(old => [...new Set([...old, Number(result.worker.worker_id)])]);
        setMessage(`${result.worker.first_name} ${result.worker.last_name} was enrolled. No attendance recorded.`);
        notifyParent('worker-face-enrolled');
      } else {
        setMatched({ ...result, preview });
        setMessage('Face matched. Supervisor must confirm the person before recording Time In.');
      }
    } catch (e) { setError(e.message); setSession(null); }
    finally { setBusy(false); }
  }

  async function recordTimeIn() {
    if (!matched?.worker) return;
    setBusy(true); setError('');
    const w = matched.worker;
    try {
      await send(urls.save, {
        worker_id: Number(w.worker_id), log_date: matched.log_date || urls.logDate,
        action: 'time_in', verification_method: 'face', supervisor_confirmed_identity: true,
      });
      setFinished(old => [{ id: w.worker_id, name: `${w.first_name} ${w.last_name}` }, ...old]);
      setMatched(null);
      setMessage(`${w.first_name} ${w.last_name}: Time In recorded. Ready for the next worker.`);
      notifyParent('worker-attendance-saved');
    } catch (e) { setError(e.message); }
    finally { setBusy(false); }
  }

  return (
    <div>
      <div className="face-panel">
        <h2>1. Choose operation</h2>
        <div className="face-actions">
          <button type="button" className="face-button" disabled={!!session || busy} onClick={() => { reset(); setPurpose('attendance'); }}>Scan attendance</button>
          <button type="button" className="face-button face-button-secondary" disabled={!!session || busy} onClick={() => { reset(); setPurpose('enroll'); }}>Enroll worker face</button>
        </div>
        <p className="face-muted">Current mode: <strong>{purpose === 'attendance' ? 'Identify worker and record Time In' : 'Enroll a face on an existing worker record'}</strong></p>
        <p className="face-muted">Enroll each worker's face once. Saved enrollments remain available when you sign in on another phone.</p>
        {purpose === 'enroll' && (
          <div>
            <label htmlFor="face-worker">Worker to enroll</label>
            <select id="face-worker" className="face-select" value={workerId} disabled={!!session || busy} onChange={e => { setWorkerId(e.target.value); setIdentityConfirmed(false); setPrivacyAcknowledged(false); }}>
              <option value="">Select worker</option>
              {workers.map(w => <option key={w.worker_id} value={w.worker_id}>{w.name}{enrolled.includes(Number(w.worker_id)) ? ' (re-enroll)' : ''}</option>)}
            </select>
            <label style={{display:'block',marginTop:12}}><input type="checkbox" checked={identityConfirmed} onChange={e=>setIdentityConfirmed(e.target.checked)}/> I verified this worker's identity in person.</label>
            <label style={{display:'block',marginTop:8}}><input type="checkbox" checked={privacyAcknowledged} onChange={e=>setPrivacyAcknowledged(e.target.checked)}/> The worker received the applicable biometric privacy notice and an authorized alternative.</label>
            {enrolled.includes(Number(workerId)) && <div className="face-actions"><button type="button" className="face-button face-button-danger" disabled={busy || !!session} onClick={removeEnrollment}>Remove this worker's enrolled face</button></div>}
          </div>
        )}
      </div>
      <div className="face-panel">
        <h2>2. Phone camera</h2>
        {!secure && <div className="face-error">Open the website through HTTPS; camera access requires a secure context.</div>}
        {!cameraCapable && <div className="face-error">Camera access is unavailable in this browser. Use Safari on iPhone, Chrome on Android, or Manual Log.</div>}
        {error && <p className="face-error" role="alert">{error}</p>}
        {message && <p className="face-success" role="status">{message}</p>}
        {session ? (
          <div>
            <div className="face-scan-host">
              <FaceCamera key={session.session_id} disabled={busy} onComplete={onComplete}
                onError={reason => { cancel().finally(() => setError(reason)); }} />
            </div>
            <div className="face-actions"><button type="button" className="face-button face-button-secondary" onClick={cancel}>Cancel scan</button></div>
          </div>
        ) : matched ? (
          <div>
            <div className="face-result">
              {matched.preview && <img src={matched.preview} alt="Current camera capture" />}
              {matched.worker.profile_image_url && <img src={matched.worker.profile_image_url} alt="Enrolled worker profile" />}
              <div><strong>{matched.worker.first_name} {matched.worker.last_name}</strong><p className="face-muted">{matched.worker.trade} | Similarity score {Number(matched.match_score).toFixed(0)}/100</p></div>
            </div>
            <p><strong>Supervisor:</strong> confirm the physical person matches this worker before recording.</p>
            <div className="face-actions">
              <button type="button" className="face-button" disabled={busy} onClick={recordTimeIn}>Confirm person &amp; save Time In</button>
              <button type="button" className="face-button face-button-secondary" disabled={busy} onClick={async () => { await send(urls.cancel, {}).catch(() => {}); reset(); }}>Reject match / Rescan</button>
            </div>
          </div>
        ) : (
          <button type="button" className="face-button" disabled={!secure || !cameraCapable || busy} onClick={start}>{busy ? 'Please wait...' : purpose === 'attendance' ? 'Start worker face scan' : 'Start worker enrollment'}</button>
        )}
      </div>
      <div className="face-panel">
        <h2>Today's confirmed face attendance ({finished.length} in this browser session)</h2>
        {finished.length ? <ul>{finished.map((w,i) => <li key={`${w.id}-${i}`}>{w.name} - Time In saved</li>)}</ul> : <p className="face-muted">After every successful Time In, start a new scan for the next worker.</p>}
        <p className="face-muted">This list is only an on-screen confirmation, not the attendance report. The Laravel attendance database remains the source of truth.</p>
      </div>
    </div>
  );
}

if (mount) createRoot(mount).render(<App />);
const inlineMount = document.getElementById('face-attendance-inline');
if (inlineMount) mountInlineAttendance(inlineMount);
