@extends('layouts.supervisor')

@section('title', 'Group Attendance - D&G Construction Monitor')
@section('page_title', 'Group Attendance')

@push('styles')
    @vite(['resources/css/supervisor.css'])
    <style>
        #registerWorkerModal {
            z-index: 1060 !important;
            background: rgba(0, 0, 0, 0.55) !important;
        }
        .scan-pulse-container {
            border: 2px dashed #dee2e6;
            border-radius: 16px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }
        .scan-pulse-container:hover {
            border-color: #0d6efd;
            background-color: #f1f7ff;
        }
        .fingerprint-trigger-btn {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .fingerprint-trigger-btn:active {
            transform: scale(0.95);
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
                    <p class="text-muted mb-0 small">Scan a worker's fingerprint to automatically identify them and log their attendance.</p>
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

    <div class="row g-4">
        <!-- Center Scanning Interface Platform -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                    <h5 class="fw-bold text-dark mb-3">Biometric Identification</h5>
                    
                    <div class="scan-pulse-container p-5 mb-3">
                        <button type="button" id="btnGlobalScan" class="btn btn-primary fingerprint-trigger-btn mb-3">
                            <i class="bi bi-fingerprint"></i>
                        </button>
                        <p class="fw-semibold text-dark mb-1">Ready to Identify</p>
                        <span class="text-muted small">Click the fingerprint button to initialize physical reader stream.</span>
                    </div>

                    <div id="globalScanStatus" class="alert alert-light border text-muted small py-2 mb-0">
                        <i class="bi bi-info-circle-fill text-primary"></i> Awaiting hardware input node...
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Attendance Session Log -->
        <div class="col-12 col-lg-8">
            <form action="{{ route('supervisor.attendance.save') }}" method="POST" id="attendanceMainForm">
                @csrf
                <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0">Scanned Personnel Log</h5>
                            <span class="badge bg-secondary rounded-pill" id="scannedCount">0 Active Logs</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle mb-0" id="supervisorAttendanceTable">
                                <thead>
                                    <tr class="text-muted border-bottom">
                                        <th class="pb-3 border-0" style="width: 40%;">Personnel Name</th>
                                        <th class="pb-3 border-0" style="width: 25%;">Trade / Designation</th>
                                        <th class="pb-3 border-0 text-center" style="width: 35%;">Status Log</th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceLogTableBody">
                                    <tr id="emptyRowPlaceholder">
                                        <td colspan="3" class="text-center py-5 text-muted fst-italic">
                                            <i class="bi bi-person-bounding-box d-block fs-2 mb-2 text-secondary"></i>
                                            No personnel checked in yet during this session.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-4 d-none" id="formSubmitContainer">
                            <button type="submit" class="btn btn-success px-4 py-2 fw-semibold">
                                Save Attendance Logs
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- FIELD WORKER REGISTRATION MODAL FRAME -->
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
<script src="https://unpkg.com/@simplewebauthn/browser@13.3.0/dist/bundle/index.umd.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnGlobalScan = document.getElementById('btnGlobalScan');
        const globalScanStatus = document.getElementById('globalScanStatus');
        const attendanceLogTableBody = document.getElementById('attendanceLogTableBody');
        const emptyRowPlaceholder = document.getElementById('emptyRowPlaceholder');
        const formSubmitContainer = document.getElementById('formSubmitContainer');
        const scannedCountBadge = document.getElementById('scannedCount');
        
        let scannedWorkerIds = new Set();

        // --- GLOBAL HARDWARE SCAN AND LOOKUP ENGINES ---
        btnGlobalScan?.addEventListener('click', async function () {
            globalScanStatus.className = "alert alert-warning border text-dark small py-2 mb-0";
            globalScanStatus.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Polling hardware credential layer...';

            try {
                // 1. Ask controller layer for WebAuthn authentication requirements
                const response = await fetch('/passkeys/login/options', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const options = await response.json();

                    const credential = await window.startAuthentication(options);

                // 3. Post payload assertion to server architecture
                const submitResponse = await fetch('/passkeys/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
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
                return;
            }

            // Purge default backdrop empty view state block
            if (emptyRowPlaceholder) {
                emptyRowPlaceholder.remove();
            }

            scannedWorkerIds.add(worker.id);
            scannedCountBadge.innerText = `${scannedWorkerIds.size} Active Logs`;
            formSubmitContainer.classList.remove('d-none');

            const tableRowHtml = `
                <tr class="border-bottom" id="row-worker-${worker.id}" style="transition: background-color 0.5s ease;">
                    <td class="py-3">
                        <div class="fw-semibold text-dark">${worker.full_name}</div>
                        <input type="hidden" name="biometric_verified[${worker.id}]" value="1">
                    </td>
                    <td class="py-3 text-muted">${worker.trade || 'General'}</td>
                    <td class="py-3">
                        <div class="d-flex justify-content-center flex-wrap gap-2">
                            <input type="radio" class="btn-check" name="attendance[${worker.id}]" id="present-${worker.id}" value="present" checked>
                            <label class="btn btn-outline-success btn-sm px-3 rounded-pill fw-medium" for="present-${worker.id}">Present</label>

                            <input type="radio" class="btn-check" name="attendance[${worker.id}]" id="late-${worker.id}" value="late">
                            <label class="btn btn-outline-warning btn-sm px-3 rounded-pill fw-medium" for="late-${worker.id}">Late</label>

                            <input type="radio" class="btn-check" name="attendance[${worker.id}]" id="absent-${worker.id}" value="absent">
                            <label class="btn btn-outline-danger btn-sm px-3 rounded-pill fw-medium" for="absent-${worker.id}">Absent</label>
                        </div>
                    </td>
                </tr>
            `;

            attendanceLogTableBody.insertAdjacentHTML('beforeend', tableRowHtml);
        }

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