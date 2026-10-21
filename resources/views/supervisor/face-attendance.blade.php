@extends(request()->boolean('embedded') ? 'layouts.face-camera' : (($faceRoutePrefix ?? 'supervisor') === 'admin' ? 'layouts.admin' : 'layouts.supervisor'))

@section('title', 'Camera Facial Attendance')
@section('page_title', 'Facial Group Attendance')

@push('styles')
<style>
.face-attendance-page {max-width:980px; margin:0 auto; padding:1.5rem 1rem 4rem;}
.face-attendance-page .face-panel {background:white; border:1px solid #dce7df; border-radius:16px; padding:1.25rem; margin-bottom:1rem; box-shadow:0 3px 18px rgba(0,0,0,.03);}
.face-attendance-page h2 {font-size:1.25rem; margin:0 0 .5rem; color:#173c29;}
.face-attendance-page .face-muted {color:#63746a;font-size:.92rem;}
.face-attendance-page .face-button {border:0; border-radius:10px; background:#215b3c; color:white; padding:.8rem 1rem; cursor:pointer; font-weight:650;}
.face-attendance-page .face-button:disabled {opacity:.5;cursor:not-allowed;}
.face-attendance-page .face-button-secondary {background:#eaf2ed;color:#194a30;}
.face-attendance-page .face-button-danger {background:#85352f;}
.face-attendance-page .face-actions {display:flex; gap:.65rem;flex-wrap:wrap; margin-top:1rem;}
.face-attendance-page .face-select {display:block;max-width:100%;width:100%;padding:.72rem;border:1px solid #c6d5cb;border-radius:9px;}
.face-attendance-page .face-scan-host {min-height:340px;max-width:640px;margin:auto;}
.face-attendance-page .face-result {display:flex;align-items:center; gap:1rem; margin:1rem 0;}
.face-attendance-page .face-result img {width:84px;height:84px;object-fit:cover;border-radius:12px;}
.face-attendance-page .face-error {background:#fff2ef;color:#8d1b16;border:1px solid #f4c5bf;border-radius:10px;padding:.85rem;}
.face-attendance-page .face-success {background:#e9f5ee;color:#145532;border:1px solid #b5dcc1;border-radius:10px;padding:.85rem;}
@media (max-width:640px) { .face-attendance-page {padding:1rem .65rem 3rem;} .face-attendance-page .face-panel {padding:.9rem;} }
</style>
@endpush

@section('content')
<div class="face-attendance-page">
  <div class="face-panel">
    <h2>{{ ($faceRoutePrefix ?? 'supervisor') === 'admin' ? 'Worker face enrollment' : 'Shared phone camera attendance' }}</h2>
    @if(($faceRoutePrefix ?? 'supervisor') !== 'admin')
    <p class="face-muted">Scan workers one at a time using an iPhone or Android camera. The supervisor must confirm the person before saving Time In.</p>
    <p class="face-muted">Use HTTPS, good lighting, and camera permission. Face matching assists supervisor verification; it does not provide certified liveness detection.</p>
    @endif
    <p class="face-muted">Face enrollment is saved to the worker's database record and works across phones. Existing workers only need their face enrolled once.</p>
    @unless(request()->boolean('embedded'))
    <a href="{{ route(($faceRoutePrefix ?? 'supervisor').'.attendance') }}">Return to attendance</a>
    @endunless
  </div>
  <div id="face-attendance-root"
       data-enrollment-only="{{ ($faceRoutePrefix ?? 'supervisor') === 'admin' ? '1' : '0' }}"
       data-csrf="{{ csrf_token() }}"
       data-models="{{ asset('models/human') }}/"
       data-start="{{ route(($faceRoutePrefix ?? 'supervisor').'.face-attendance.start') }}"
       data-finish="{{ route(($faceRoutePrefix ?? 'supervisor').'.face-attendance.finish') }}"
       data-cancel="{{ route(($faceRoutePrefix ?? 'supervisor').'.face-attendance.cancel') }}"
       data-save="{{ ($faceRoutePrefix ?? 'supervisor') === 'admin' ? '' : route('supervisor.attendance.logWorker') }}"
       data-log-date="{{ now('Asia/Manila')->toDateString() }}"
       data-remove-prefix="{{ url('/'.($faceRoutePrefix ?? 'supervisor').'/face-attendance/enrollments') }}"></div>
  <script type="application/json" id="face-workers-json">@json($faceWorkerChoices)</script>
</div>
@vite('resources/js/face-attendance.jsx')
@endsection
