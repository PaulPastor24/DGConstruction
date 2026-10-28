<div class="modal fade" id="registerWorkerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header enrollment-modal-header">
                <div class="enrollment-heading">
                    <span class="enrollment-modal-icon" aria-hidden="true"><i class="bi bi-person-badge"></i></span>
                    <div>
                        <h5 class="enrollment-modal-title" id="registerWorkerModalLabel">Fast Worker Enrollment</h5>
                        <p class="enrollment-modal-subtitle">Enter worker details and capture their face before saving.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="fastWorkerForm">
                    <section class="mb-4" aria-labelledby="enrollmentDetailsHeading">
                        <h6 class="enrollment-section-heading" id="enrollmentDetailsHeading">Worker Details</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="regProjectId" class="form-label small fw-semibold">Assigned project <span class="text-danger">*</span></label>
                                <select id="regProjectId" class="form-select" data-searchable-project aria-label="Select assigned project" required>
                                    @forelse($projects->filter(fn ($project) => $project->workflowStatus() !== \App\Models\Project::STATUS_ARCHIVED) as $project)
                                    <option value="{{ $project->project_id }}">{{ $project->project_name }}</option>
                                    @empty
                                    <option value="">No active project assigned</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label for="regFirstName" class="form-label small fw-semibold">First name <span class="text-danger">*</span></label>
                                <input type="text" id="regFirstName" class="form-control" maxlength="100" autocomplete="given-name" required placeholder="e.g. Juan">
                            </div>
                            <div class="col-sm-6">
                                <label for="regLastName" class="form-label small fw-semibold">Last name <span class="text-danger">*</span></label>
                                <input type="text" id="regLastName" class="form-control" maxlength="100" autocomplete="family-name" required placeholder="e.g. Dela Cruz">
                            </div>
                            <div class="col-sm-6">
                                <label for="regContactNumber" class="form-label small fw-semibold">Contact number</label>
                                <input type="tel" id="regContactNumber" class="form-control" maxlength="20" autocomplete="tel" inputmode="tel" placeholder="e.g. +63 912 345 6789">
                            </div>
                            <div class="col-sm-6">
                                <label for="regTrade" class="form-label small fw-semibold">Trade specialty</label>
                                <input type="text" id="regTrade" class="form-control" maxlength="100" placeholder="e.g. Carpenter">
                            </div>
                        </div>
                    </section>

                    <section class="enrollment-schedule-panel mb-4" aria-labelledby="enrollmentScheduleHeading">
                        <h6 class="enrollment-section-heading mb-1" id="enrollmentScheduleHeading">Attendance Schedule</h6>
                        <p class="enrollment-section-help">Defaults are 7:00 AM to 5:00 PM with a 1-hour break. You can customize this worker's schedule.</p>
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="workerScheduleStart" class="form-label small fw-semibold">Work starts</label>
                                <input type="time" id="workerScheduleStart" class="form-control" value="07:00" step="60" required>
                            </div>
                            <div class="col-6">
                                <label for="workerScheduleEnd" class="form-label small fw-semibold">Work ends</label>
                                <input type="time" id="workerScheduleEnd" class="form-control" value="17:00" step="60" required>
                            </div>
                            <div class="col-6">
                                <label for="workerBreakMinutes" class="form-label small fw-semibold">Break duration (minutes)</label>
                                <input type="number" id="workerBreakMinutes" class="form-control" value="60" min="0" max="480" step="1" required>
                            </div>
                        </div>
                    </section>


                    <div id="worker-registration-camera" data-models="{{ asset('models/human') }}/" data-model-version="{{ config('face_attendance.model_version') }}"></div>
                </form>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light fw-medium btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success fw-semibold btn-sm" id="btnSaveWorkerRecord" disabled>
                    Save Worker Record
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adminEnrolledWorkersModal" tabindex="-1" aria-labelledby="adminWorkersTitle" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h5 id="adminWorkersTitle">Enrolled Workers</h5><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body">
<p class="text-muted">View worker enrollment status or select a worker below to enroll or replace their face scan.</p>
<div class="enrolled-toolbar"><label>Search workers<input id="enrolledSearch" type="search" class="form-control" placeholder="Search name or trade"></label><label>Enrollment status<select id="enrolledStatus" class="form-select"><option value="">All workers</option><option value="enrolled">Enrolled</option><option value="pending">Not enrolled</option></select></label></div>
<div class="table-responsive enrolled-table"><table class="table"><thead><tr><th>Name</th><th>Trade</th><th>Face enrollment</th><th>Action</th></tr></thead><tbody>
@php($enrolledIds = \Illuminate\Support\Facades\Schema::hasTable('worker_face_enrollments') ? \Illuminate\Support\Facades\DB::table('worker_face_enrollments')->whereNotNull('face_descriptors')->where('model_version', config('face_attendance.model_version'))->pluck('worker_id') : collect())
@foreach($workers as $worker)
<tr data-enrolled-worker data-status="{{ $enrolledIds->contains($worker->worker_id) ? 'enrolled' : 'pending' }}">
<td data-label="Name">{{ trim($worker->first_name.' '.$worker->last_name) }}</td><td data-label="Trade">{{ $worker->trade ?? 'General' }}</td>
<td data-label="Face enrollment" data-enrollment-status="{{ $worker->worker_id }}">{{ $enrolledIds->contains($worker->worker_id) ? 'Enrolled' : 'Not enrolled' }}</td>
<td data-label="Action"><button class="btn btn-sm btn-success" type="button" data-enroll-worker="{{ $worker->worker_id }}">{{ $enrolledIds->contains($worker->worker_id) ? 'Replace face scan' : 'Enroll face' }}</button></td></tr>
@endforeach
</tbody></table></div>
<p id="enrolledEmpty" class="text-center text-muted py-4" hidden>No workers match your filters.</p>
<nav class="enrolled-pagination" aria-label="Worker list pages"><span id="enrolledCount" aria-live="polite"></span><div><button type="button" id="enrolledPrev" class="btn btn-outline-success">Previous</button><span id="enrolledPage" class="mx-3"></span><button type="button" id="enrolledNext" class="btn btn-outline-success">Next</button></div></nav>
<section id="workerEnrollmentPanel" hidden>
<div class="d-flex justify-content-between align-items-center my-3"><h6 class="mb-0">Worker face enrollment</h6><button type="button" id="closeWorkerEnrollment" class="btn btn-sm btn-outline-secondary">Close enrollment</button></div>
<iframe id="adminEnrollmentFrame" title="Worker face enrollment" data-src="{{ route('admin.face-attendance.index', ['embedded' => 1, 'purpose' => 'enroll']) }}" style="width:100%;height:650px;border:0" allow="camera"></iframe>
</section>
</div></div></div></div>

@vite('resources/js/face-attendance.jsx')

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('registerWorkerModal');
    const form = document.getElementById('fastWorkerForm');
    const save = document.getElementById('btnSaveWorkerRecord');
    let capture = null;
    const message = document.createElement('p');
    message.className = 'alert alert-danger'; message.hidden = true; message.setAttribute('role', 'alert');
    form.prepend(message);
    window.addEventListener('worker-registration-captured', event => { capture = event.detail; save.disabled = !capture; });
    modal.addEventListener('hidden.bs.modal', () => { form.reset(); capture = null; save.disabled = true; message.hidden = true; window.dispatchEvent(new Event('worker-registration-reset')); });
    form.addEventListener('submit', event => { event.preventDefault(); save.click(); });
    save.addEventListener('click', async () => {
        if (!form.reportValidity() || !capture) return;
        save.disabled = true; message.hidden = true;
        const value = id => document.getElementById(id).value;
        try {
            const response = await fetch(@json(route('admin.workers.register_camera')), {
                method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                body: JSON.stringify({ first_name: value('regFirstName'), last_name: value('regLastName'), trade: value('regTrade'), contact_number: value('regContactNumber'), project_id: value('regProjectId'), schedule_start: value('workerScheduleStart'), schedule_end: value('workerScheduleEnd'), break_minutes: value('workerBreakMinutes'), model_version: capture.model_version, face_samples: capture.samples })
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Unable to register worker.');
            window.location.reload();
        } catch (error) { message.textContent = error.message; message.hidden = false; save.disabled = !capture; }
    });
    const enrollmentModal = document.getElementById('adminEnrolledWorkersModal');
    const frame = document.getElementById('adminEnrollmentFrame');
    window.addEventListener('message', async event => {
        if (event.origin !== window.location.origin || event.source !== frame.contentWindow || event.data?.type !== 'worker-face-enrolled') return;
        try {
            const response = await fetch(@json(route('admin.face-attendance.index')), { headers: { Accept: 'application/json' } });
            if (!response.ok) return;
            const data = await response.json();
            for (const worker of data.faceWorkerChoices) {
                const cell = document.querySelector('[data-enrollment-status="' + worker.worker_id + '"]');
                if (cell) {
                    cell.textContent = worker.enrolled ? 'Enrolled' : 'Not enrolled';
                    cell.closest('tr').dataset.status = worker.enrolled ? 'enrolled' : 'pending';
                    cell.closest('tr').querySelector('[data-enroll-worker]').textContent = worker.enrolled ? 'Replace face scan' : 'Enroll face';
                }
            }
            renderWorkers();
        } catch (_) { /* Status refresh can be retried by reopening the page. */ }
    });
    const rows = [...enrollmentModal.querySelectorAll('[data-enrolled-worker]')];
    const search = document.getElementById('enrolledSearch');
    const status = document.getElementById('enrolledStatus');
    const prev = document.getElementById('enrolledPrev');
    const next = document.getElementById('enrolledNext');
    const panel = document.getElementById('workerEnrollmentPanel');
    let page = 1;
    function renderWorkers() {
        const filtered = rows.filter(row => row.textContent.toLowerCase().includes(search.value.trim().toLowerCase()) && (!status.value || row.dataset.status === status.value));
        const pages = Math.max(1, Math.ceil(filtered.length / 10));
        page = Math.min(page, pages);
        rows.forEach(row => row.hidden = true);
        filtered.slice((page - 1) * 10, page * 10).forEach(row => row.hidden = false);
        document.getElementById('enrolledEmpty').hidden = filtered.length > 0;
        document.getElementById('enrolledCount').textContent = filtered.length ? String((page - 1) * 10 + 1) + '?' + Math.min(page * 10, filtered.length) + ' of ' + filtered.length + ' workers' : '0 workers';
        document.getElementById('enrolledPage').textContent = page + ' / ' + pages;
        prev.disabled = page === 1; next.disabled = page === pages;
    }
    [search, status].forEach(input => input.addEventListener('input', () => { page = 1; renderWorkers(); }));
    prev.addEventListener('click', () => { page--; renderWorkers(); });
    next.addEventListener('click', () => { page++; renderWorkers(); });
    enrollmentModal.querySelectorAll('[data-enroll-worker]').forEach(button => button.addEventListener('click', () => {
        const url = new URL(frame.dataset.src, window.location.origin);
        url.searchParams.set('worker_id', button.dataset.enrollWorker);
        frame.src = url.href; panel.hidden = false;
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }));
    document.getElementById('closeWorkerEnrollment').addEventListener('click', () => { frame.removeAttribute('src'); panel.hidden = true; });
    renderWorkers();
    enrollmentModal.addEventListener('hidden.bs.modal', () => { frame.removeAttribute('src'); panel.hidden = true; });
});
</script>
