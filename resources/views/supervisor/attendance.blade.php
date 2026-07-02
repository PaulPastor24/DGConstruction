@extends('layouts.supervisor')

@section('title', 'Group Attendance - D&G Construction Monitor')
@section('page_title', 'Group Attendance')

@push('styles')
    @vite(['resources/css/supervisor.css'])
    <style>
        /* Forces the modal window to layer flawlessly over an independent CSS backdrop tint */
        #registerWorkerModal {
            z-index: 1060 !important;
            background: rgba(0, 0, 0, 0.55) !important;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid p-0">
    <!-- Top Action Header -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                <div>
                    <h4 class="fw-bold mb-1">Daily Workforce Attendance</h4>
                    <p class="text-muted mb-0 small">Log, verify via fingerprint hardware, and update field personnel attendance shifts.</p>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <button type="button" class="btn btn-primary fw-semibold btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#registerWorkerModal">
                        <i class="bi bi-person-plus-fill"></i> Register New Worker
                    </button>
                    <div style="max-width: 180px;">
                        <input type="date" name="attendance_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Form Sheet -->
    <form action="{{ route('supervisor.attendance.save') }}" method="POST" id="attendanceMainForm">
        @csrf
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="supervisorAttendanceTable">
                        <thead>
                            <tr class="text-muted border-bottom">
                                <th class="pb-3 border-0" style="width: 35%;">Personnel Name</th>
                                <th class="pb-3 border-0" style="width: 20%;">Trade / Designation</th>
                                <th class="pb-3 border-0 text-center" style="width: 20%;">Biometric Link</th>
                                <th class="pb-3 border-0 text-center" style="width: 25%;">Status Log</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($workers) && $workers->count() > 0)
                                @foreach($workers as $worker)
                                    @php
                                        $id = $worker->worker_id ?? $worker->id;
                                        $fullName = $worker->full_name ?? ($worker->first_name . ' ' . $worker->last_name) ?? 'Worker';
                                    @endphp
                                    <tr class="border-bottom" data-worker-id="{{ $id }}">
                                        <td class="py-3">
                                            <div class="fw-semibold text-dark">{{ $fullName }}</div>
                                        </td>
                                        <td class="py-3 text-muted">{{ $worker->trade ?? 'General' }}</td>
                                        <td class="py-3 text-center">
                                            <!-- Field Fingerprint Scanner Triggers -->
                                            <button type="button" class="btn btn-outline-dark btn-sm btn-scan-finger px-2 py-1" data-id="{{ $id }}" data-name="{{ $fullName }}">
                                                <i class="bi bi-fingerprint"></i> Scan Verify
                                            </button>
                                            <div class="bio-indicator bio-unverified" id="bio-status-{{ $id }}">
                                                <i class="bi bi-dash-circle"></i> Untrusted
                                            </div>
                                            <!-- Hidden input payload to pass matching results back to controller stack -->
                                            <input type="hidden" name="biometric_verified[{{ $id }}]" id="bio-input-{{ $id }}" value="0">
                                        </td>
                                        <td class="py-3">
                                            <div class="d-flex justify-content-center flex-wrap gap-2">
                                                <input type="radio" class="btn-check status-present" name="attendance[{{ $id }}]" id="present-{{ $id }}" value="present" checked>
                                                <label class="btn btn-outline-success btn-sm px-3 rounded-pill fw-medium" for="present-{{ $id }}">Present</label>

                                                <input type="radio" class="btn-check" name="attendance[{{ $id }}]" id="late-{{ $id }}" value="late">
                                                <label class="btn btn-outline-warning btn-sm px-3 rounded-pill fw-medium" for="late-{{ $id }}">Late</label>

                                                <input type="radio" class="btn-check" name="attendance[{{ $id }}]" id="absent-{{ $id }}" value="absent">
                                                <label class="btn btn-outline-danger btn-sm px-3 rounded-pill fw-medium" for="absent-{{ $id }}">Absent</label>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted fst-italic">
                                        No personnel assigned to this active work site.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if(isset($workers) && $workers->count() > 0)
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-success px-4 py-2 fw-semibold">
                            Save Attendance Logs
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- FIELD WORKER REGISTRATION MODAL FRAME (Moved completely outside layout containers) -->
<div class="modal fade" id="registerWorkerModal" data-bs-backdrop="false" tabindex="-1" aria-labelledby="registerWorkerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 14px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="registerWorkerModalLabel"><i class="bi bi-person-badge text-primary"></i> Fast Worker Enrollment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <form id="fastWorkerForm">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">First Name</label>
                        <input type="text" id="regFirstName" class="form-control" required placeholder="e.g. Juan">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Last Name</label>
                        <input type="text" id="regLastName" class="form-control" required placeholder="e.g. Dela Cruz">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Trade Specialty</label>
                        <input type="text" id="regTrade" class="form-control" placeholder="e.g. Carpenter, Mason, Welder">
                    </div>
                    
                    <div class="card bg-light border-0 mb-2">
                        <div class="card-body text-center py-3">
                            <label class="d-block small fw-bold text-dark mb-2"><i class="bi bi-fingerprint"></i> Device Passkey Association Layer</label>
                            <button type="button" class="btn btn-dark btn-sm fw-semibold" id="btnRegisterWorkerFingerprint">
                                <i class="bi bi-shield-plus"></i> Initialize Fingerprint Capture
                            </button>
                            <span class="d-block text-muted small mt-1" id="registerFingerprintLabel">Biometrics not captured yet.</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light fw-medium btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary fw-semibold btn-sm" id="btnSaveWorkerRecord" disabled>Save Worker Record</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- DEVICE FINGERPRINT SCAN FOR WORKER VERIFICATION ---
        const scanButtons = document.querySelectorAll('.btn-scan-finger');

        scanButtons.forEach(button => {
            button.addEventListener('click', async function () {
                const workerId = this.getAttribute('data-id');
                const workerName = this.getAttribute('data-name');
                const statusContainer = document.getElementById(`bio-status-${workerId}`);
                const hiddenInput = document.getElementById(`bio-input-${workerId}`);
                const presentRadio = document.getElementById(`present-${workerId}`);

                try {
                    const response = await fetch('/passkeys/login/options', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    const options = await response.json();

                    const credential = await window.startAuthentication(options);

                    const submitResponse = await fetch('/passkeys/login', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify(credential)
                    });

                    if (submitResponse.ok) {
                        statusContainer.innerHTML = '<i class="bi bi-check-circle-fill"></i> Verified Match';
                        statusContainer.className = 'bio-indicator bio-verified';
                        hiddenInput.value = "1";
                        if (presentRadio) presentRadio.checked = true;
                        alert(`Biometrics Authenticated: ${workerName} checked in successfully!`);
                    } else {
                        alert('Authentication failure: Token credentials unmatched for this field worker.');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Hardware sensor connection timed out.');
                }
            });
        });

        // --- NEW FIELD WORKER ENROLLMENT REGISTRATION SUBSYSTEM ---
        const initFingerprintBtn = document.getElementById('btnRegisterWorkerFingerprint');
        const saveWorkerBtn = document.getElementById('btnSaveWorkerRecord');
        const fingerprintLabel = document.getElementById('registerFingerprintLabel');
        let capturedPasskeyCredential = null;

        initFingerprintBtn?.addEventListener('click', async () => {
            const firstName = document.getElementById('regFirstName').value.trim();
            const lastName = document.getElementById('regLastName').value.trim();

            if (!firstName || !lastName) {
                alert('Please input the worker\'s name first before scanning their fingerprint.');
                return;
            }

            try {
                fingerprintLabel.innerText = "Interfacing with hardware reader node...";
                
                const response = await fetch('/passkeys/register/options', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const options = await response.json();

                options.user.name = `${firstName} ${lastName}`;
                options.user.displayName = `${firstName} ${lastName}`;

                capturedPasskeyCredential = await window.startRegistration(options);
                
                fingerprintLabel.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-patch-check-fill"></i> Token Captured Successfully!</span>';
                saveWorkerBtn.disabled = false;
            } catch (error) {
                console.error(error);
                fingerprintLabel.innerHTML = '<span class="text-danger">Registration execution halted.</span>';
                alert('Biometric reading cancelled.');
            }
        });

        saveWorkerBtn?.addEventListener('click', async () => {
            const firstName = document.getElementById('regFirstName').value.trim();
            const lastName = document.getElementById('regLastName').value.trim();
            const trade = document.getElementById('regTrade').value.trim();

            if (!capturedPasskeyCredential) return;

            try {
                const saveResponse = await fetch('/supervisor/workers/register-biometric', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        first_name: firstName,
                        last_name: lastName,
                        trade: trade,
                        credential: capturedPasskeyCredential
                    })
                });

                if (saveResponse.ok) {
                    alert('Worker and biometric token successfully cataloged inside the database!');
                    window.location.reload();
                } else {
                    alert('Server error saving new worker context parameters.');
                }
            } catch (err) {
                console.error(err);
                alert('Connection to server timeout.');
            }
        });
    });
</script>
@endpush