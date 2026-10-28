import React, { useEffect, useRef, useState } from 'react';

let engine;
async function withTimeout(task, milliseconds, message) {
  let timer;
  try {
    return await Promise.race([task, new Promise((_, reject) => {
      timer = window.setTimeout(() => reject(new Error(message)), milliseconds);
    })]);
  } finally { window.clearTimeout(timer); }
}
async function getEngine() {
  if (!engine) engine = (async () => {
    const { Human } = await import('@vladmandic/human');
    const modelBasePath = (document.getElementById('face-attendance-root') || document.getElementById('face-attendance-inline') || document.getElementById('worker-registration-camera')).dataset.models;
    const human = new Human({
      // Avoid slow graphics drivers and shader compilation on mobile devices.
      backend: 'wasm', wasmPath: new URL('wasm/', new URL(modelBasePath, window.location.href)).href,
      warmup: 'none', modelBasePath, cacheSensitivity: 0,
      filter: { enabled: true, equalization: true },
      face: {
        enabled: true,
        detector: { maxDetected: 2, rotation: true, return: false, minConfidence: 0.6, skipFrames: 0, skipTime: 0 },
        mesh: { enabled: true }, description: { enabled: true, skipFrames: 0, skipTime: 0 },
        iris: { enabled: false }, emotion: { enabled: false },
        antispoof: { enabled: false }, liveness: { enabled: false },
      },
      body: { enabled: false }, hand: { enabled: false },
      object: { enabled: false }, gesture: { enabled: false },
    });
    // Hostinger does not need cross-origin isolation headers or worker threads.
    human.tf.setThreadsCount(1);
    await human.load();
    if (human.tf.getBackend() !== 'wasm') {
      throw new Error('The local face processing engine could not start. Upload the complete public/models/human/wasm folder.');
    }
    if (!['blazeface', 'facemesh', 'faceres'].every(name => human.models.loaded().includes(name))) {
      throw new Error('Required face model files are missing.');
    }
    return human;
  })().catch(error => { engine = null; throw error; });
  return engine;
}

export default function FaceCamera({ onComplete, onError, disabled, autoCapture = false, captureLabel = 'Capture worker face', onClose, compactPreview = false }) {
  const video = useRef(null);
  const stream = useRef(null);
  const active = useRef(true);
  const capturing = useRef(false);
  const [ready, setReady] = useState(false);
  const [progress, setProgress] = useState('Requesting camera access. Allow camera permission if prompted.');
  const [facing, setFacing] = useState('user');
  const [previewFacing, setPreviewFacing] = useState('user');
  useEffect(() => {
    active.current = true;
    let disposed = false;
    let camera;
    let cameraPending = true;
    setReady(false);
    setProgress('Requesting camera access. Allow camera permission if prompted.');
    (async () => {
      const request = navigator.mediaDevices.getUserMedia({
        video: { facingMode: { ideal: facing }, width: { ideal: 640 }, height: { ideal: 480 } }, audio: false,
      }).then(acquired => {
        if (disposed || !cameraPending) {
          acquired.getTracks().forEach(t => t.stop());
          throw new Error('Camera request was cancelled. Retry camera.');
        }
        return acquired;
      });
      try {
        camera = await withTimeout(request, 20000,
          'The browser did not open the camera. Allow camera permission in site settings, close other camera apps, then retry.');
      } finally { cameraPending = false; }
      if (disposed) { camera.getTracks().forEach(t => t.stop()); return; }
      stream.current = camera;
      // Use the camera actually selected by the browser, which may differ
      // from the requested facing mode on devices with only one camera.
      setPreviewFacing(camera.getVideoTracks()[0]?.getSettings().facingMode || facing);
      setProgress('Starting camera preview...');
      video.current.srcObject = camera;
      await withTimeout(video.current.play(), 15000,
        'The camera opened but the preview did not start. Keep the camera panel visible and retry camera.');
      if (disposed) return;
      const preview = video.current;
      if (!preview.videoWidth || !preview.videoHeight || preview.readyState < 2) {
        let onFrame;
        try {
          await withTimeout(new Promise(resolve => {
            onFrame = resolve;
            preview.addEventListener('loadeddata', onFrame, { once: true });
          }), 10000, 'No video frames arrived from the camera. Close other camera apps and retry.');
        } finally { preview.removeEventListener('loadeddata', onFrame); }
      }
      if (disposed) return;
      if (!disposed) setProgress('Loading face recognition. The first visit downloads about 10 MB; keep this page open.');
      await withTimeout(getEngine(), 60000, 'Face models took too long to load. Check your connection and that public/models/human was uploaded.');
      if (!disposed) { setReady(true); setProgress('Place one worker in view. Remove sunglasses and face coverings when safe.'); }
    })().catch(e => {
      camera?.getTracks().forEach(t => t.stop());
      if (!disposed) onError(e.name === 'NotAllowedError'
        ? 'Camera permission denied. Allow camera access in your browser settings and retry.'
        : e.name === 'NotFoundError' ? 'No camera found. Use a phone with a working camera.'
        : e.name === 'NotReadableError' ? 'The camera is busy or unavailable. Close other camera apps and browser tabs, then retry.'
        : 'Camera or recognition models could not load. Check your connection and that model files were deployed. ' + e.message);
    });
    return () => { disposed = true; active.current = false; camera?.getTracks().forEach(t => t.stop()); };
  }, [facing]);

  useEffect(() => {
    if (!autoCapture || !ready || disabled) return;
    const timer = window.setTimeout(() => capture(), 900);
    return () => window.clearTimeout(timer);
  }, [autoCapture, ready, disabled]);

  async function capture() {
    if (capturing.current || disabled) return;
    capturing.current = true;
    setReady(false);
    try {
      const human = await getEngine();
      const samples = [];
      const canvas = document.createElement('canvas');
      canvas.width = 640;
      canvas.height = Math.round(640 * video.current.videoHeight / video.current.videoWidth);
      let preview;
      const deadline = Date.now() + 60000;
      while (samples.length < 3) {
        if (!active.current) return;
        if (Date.now() > deadline) throw new Error('A consistent face scan could not finish. Face the camera in good lighting, then retry.');
        setProgress(`Analyzing face sample ${samples.length + 1} of 3... Hold still.`);
        canvas.getContext('2d').drawImage(video.current, 0, 0, canvas.width, canvas.height);
        const result = await withTimeout(human.detect(canvas), 30000,
          'Face processing took too long on this device. Close other tabs and retry, or try another browser.');
        if (!active.current) return;
        const face = result.face?.[0];
        let hint;
        if (!face) hint = 'No face detected. Look directly at the camera in good lighting.';
        else if (result.face.length !== 1) { hint = 'Show only one worker in the camera.'; samples.length = 0; }
        else if (face.faceScore < 0.6) hint = 'Face is not clear. Face the camera directly.';
        else if (face.box[2] < 100) hint = 'Move closer so your face fills more of the preview.';
        else if (!face.embedding || face.embedding.length !== 1024) {
          throw new Error('Face detected, but its recognition template is unavailable. Check the deployed faceres model files.');
        }
        if (hint) {
          setProgress(hint);
          if (!autoCapture) { setReady(true); return; }
          await new Promise(resolve => setTimeout(resolve, 900));
          continue;
        }
        if (samples.length && human.match.similarity(samples[0], Array.from(face.embedding)) < 0.65) {
          samples.length = 0;
          setProgress('Face changed. Keep the same worker in view while scanning.');
        }
        samples.push(Array.from(face.embedding));
        preview = canvas.toDataURL('image/jpeg', 0.75);
        setProgress(`Face sample ${samples.length} of 3 collected.`);
        await new Promise(resolve => setTimeout(resolve, 400));
      }
      if (active.current) await onComplete(samples, preview);
    } catch (e) {
      if (active.current) onError(e.message || 'Face processing failed. Please retry.');
    } finally { capturing.current = false; }
  }

  return <div className="face-camera">
    <video ref={video} autoPlay muted playsInline style={{ display: 'block', width: '100%', maxWidth: compactPreview ? 560 : '100%', maxHeight: compactPreview ? 360 : 420, aspectRatio: compactPreview ? '4 / 3' : undefined, objectFit: compactPreview ? 'cover' : undefined, margin: '0 auto', background: '#17251c', borderRadius: 12, transform: previewFacing === 'user' ? 'scaleX(-1)' : 'none' }} />
    <p role="status">{progress}</p>
    <div className="face-actions">
      {!autoCapture && <button type="button" className="face-button btn btn-success" disabled={!ready || disabled} onClick={capture}>{captureLabel}</button>}
      <button type="button" className="face-button face-button-secondary btn btn-light border" disabled={!ready || disabled} onClick={() => setFacing(facing === 'user' ? 'environment' : 'user')}>Switch camera</button>
      {onClose && <button type="button" className="face-button face-button-secondary btn btn-light border" disabled={disabled} onClick={onClose}>Close camera</button>}
    </div>
  </div>;
}
