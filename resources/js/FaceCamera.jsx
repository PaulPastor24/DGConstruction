import React, { useEffect, useRef, useState } from 'react';

let engine;
async function getEngine() {
  if (!engine) engine = (async () => {
    const { Human } = await import('@vladmandic/human');
    const human = new Human({
      backend: 'webgl', modelBasePath: (document.getElementById('face-attendance-root') || document.getElementById('face-attendance-inline')).dataset.models, cacheSensitivity: 0,
      filter: { enabled: true, equalization: true },
      face: {
        enabled: true,
        detector: { maxDetected: 2, rotation: true, return: true, minConfidence: 0.6, skipFrames: 0, skipTime: 0 },
        mesh: { enabled: true }, description: { enabled: true, skipFrames: 0, skipTime: 0 },
        iris: { enabled: false }, emotion: { enabled: false },
        antispoof: { enabled: false }, liveness: { enabled: false },
      },
      body: { enabled: false }, hand: { enabled: false },
      object: { enabled: false }, gesture: { enabled: false },
    });
    await human.load();
    if (!['blazeface', 'facemesh', 'faceres'].every(name => human.models.loaded().includes(name))) {
      throw new Error('Required face model files are missing.');
    }
    await human.warmup();
    return human;
  })().catch(error => { engine = null; throw error; });
  return engine;
}

export default function FaceCamera({ onComplete, onError, disabled, autoCapture = false }) {
  const video = useRef(null);
  const stream = useRef(null);
  const active = useRef(true);
  const capturing = useRef(false);
  const [ready, setReady] = useState(false);
  const [progress, setProgress] = useState('Opening camera and loading recognition models...');
  const [facing, setFacing] = useState('user');
  useEffect(() => {
    active.current = true;
    let disposed = false;
    let camera;
    setReady(false);
    setProgress('Opening camera and loading recognition models...');
    (async () => {
      camera = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: { ideal: facing }, width: { ideal: 640 }, height: { ideal: 480 } }, audio: false,
      });
      if (disposed) { camera.getTracks().forEach(t => t.stop()); return; }
      stream.current = camera;
      video.current.srcObject = camera;
      await video.current.play();
      await getEngine();
      if (!disposed) { setReady(true); setProgress('Place one worker in view. Remove sunglasses and face coverings when safe.'); }
    })().catch(e => {
      if (!disposed) onError(e.name === 'NotAllowedError'
        ? 'Camera permission denied. Allow camera access in your browser settings and retry.'
        : e.name === 'NotFoundError' ? 'No camera found. Use a phone with a working camera.'
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
      for (let i = 0; i < 3; i++) {
        if (!active.current) return;
        setProgress(`Capturing sample ${i + 1} of 3. Keep the same worker in view.`);
        canvas.getContext('2d').drawImage(video.current, 0, 0, canvas.width, canvas.height);
        const result = await human.detect(canvas);
        if (!active.current) return;
        if (result.face.length !== 1) throw new Error('Show exactly one face in the camera and retry.');
        const face = result.face[0];
        if (face.faceScore < 0.6 || face.box[2] < 100 || !face.embedding || face.embedding.length !== 1024) {
          throw new Error('Move closer and face the camera in good lighting, then retry.');
        }
        samples.push(Array.from(face.embedding));
        preview = canvas.toDataURL('image/jpeg', 0.75);
        await new Promise(resolve => setTimeout(resolve, 700));
      }
      if (active.current) await onComplete(samples, preview);
    } catch (e) {
      if (active.current) { setProgress(e.message); setReady(true); }
    } finally { capturing.current = false; }
  }

  return <div>
    <video ref={video} autoPlay muted playsInline style={{ width: '100%', maxHeight: 420, background: '#17251c', borderRadius: 12 }} />
    <p role="status">{progress}</p>
    <div className="face-actions d-flex flex-wrap gap-2">
      {!autoCapture && <button type="button" className="face-button btn btn-success" disabled={!ready || disabled} onClick={capture}>Capture worker face</button>}
      <button type="button" className="face-button face-button-secondary btn btn-light border" disabled={!ready || disabled} onClick={() => setFacing(facing === 'user' ? 'environment' : 'user')}>Switch camera</button>
    </div>
  </div>;
}
