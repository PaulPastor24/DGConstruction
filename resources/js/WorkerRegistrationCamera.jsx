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
        if (modal?.classList.contains('show')) start();
      };
      window.addEventListener('worker-registration-reset', reset);
      modal?.addEventListener('shown.bs.modal', start);
      return () => {
        window.removeEventListener('worker-registration-reset', reset);
        modal?.removeEventListener('shown.bs.modal', start);
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
      <p className="small text-muted">Look at the camera and hold still. Your face is scanned automatically before saving.</p>
      {error && <p className="alert alert-danger" role="alert">{error}</p>}
      {captured && <p className="text-success" role="status">Face samples captured. Save Worker Record to verify and complete enrollment.</p>}
      {open ? <>
        <FaceCamera autoCapture onComplete={samples => {
          setOpen(false); setCaptured(true);
          window.dispatchEvent(new CustomEvent('worker-registration-captured', {
            detail: { samples, model_version: mount.dataset.modelVersion },
          }));
        }} onError={reason => { setOpen(false); setError(reason); }} />
        <button type="button" className="btn btn-light border mt-2" onClick={() => setOpen(false)}>Close camera</button>
      </> : <button type="button" className="btn btn-success" onClick={start}>{captured ? 'Scan face again' : 'Retry camera'}</button>}
    </div>;
  }
  createRoot(mount).render(<RegistrationCamera />);
}
