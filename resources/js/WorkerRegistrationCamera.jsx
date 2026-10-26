import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
import FaceCamera from './FaceCamera';

export function mountWorkerRegistrationCamera(mount) {
  function RegistrationCamera() {
    const [open, setOpen] = useState(false);
    const [captured, setCaptured] = useState(false);
    const [error, setError] = useState('');
    useEffect(() => {
      if (open) mount.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, [open]);
    useEffect(() => {
      const modal = document.getElementById('registerWorkerModal');
      const reset = () => {
        setOpen(false); setCaptured(false); setError('');
      };
      window.addEventListener('worker-registration-reset', reset);
      const close = () => { setOpen(false); setCaptured(false); setError(''); };
      modal?.addEventListener('hidden.bs.modal', close);
      return () => {
        window.removeEventListener('worker-registration-reset', reset);
        modal?.removeEventListener('hidden.bs.modal', close);
      };
    }, []);
    function start() {
      if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
        setError('Open the HTTPS site and allow camera access to enroll this worker.'); return;
      }
      window.dispatchEvent(new CustomEvent('worker-registration-captured', { detail: null }));
      setCaptured(false); setError(''); setOpen(true);
    }
    return <div className="border rounded p-3">
      <h6>Facial enrollment <span className="text-danger">*</span></h6>
      <p className="small text-muted">Open the camera, position the worker, then select Scan Face to capture samples.</p>
      {error && <p className="alert alert-danger" role="alert">{error}</p>}
      {captured && <p className="text-success" role="status">Face samples captured. Save Worker Record to verify and complete enrollment.</p>}
      {open ? <>
        <FaceCamera compactPreview captureLabel="Scan Face" onClose={() => setOpen(false)} onComplete={samples => {
          setOpen(false); setCaptured(true);
          window.dispatchEvent(new CustomEvent('worker-registration-captured', {
            detail: { samples, model_version: mount.dataset.modelVersion },
          }));
        }} onError={reason => { setOpen(false); setError(reason); }} />
      </> : <button type="button" className="btn btn-success" onClick={start}>{captured ? 'Scan face again' : error ? 'Retry camera' : 'Open Camera'}</button>}
    </div>;
  }
  createRoot(mount).render(<RegistrationCamera />);
}
