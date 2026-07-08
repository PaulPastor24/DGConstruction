@extends('layouts.supervisor')

@section('title', 'Group Attendance - D&G Construction Monitor')
@section('page_title', 'Group Attendance')

@push('styles')
    @vite(['resources/css/supervisor.css'])
    <style>
    /* Ensure modal sits on top of the sticky topbar/sidebar */
    .modal {
        z-index: 1070 !important;
    }

    .modal-backdrop {
        z-index: 1060 !important;
    }

    .modal.fade {
        display: none !important;
    }

    .modal.fade.show {
        display: flex !important;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.6);
    }

    /* Desktop / web modal size */
    .workers-modal-dialog {
        width: min(820px, calc(100vw - 40px)) !important;
        max-width: 820px !important;
        margin: auto !important;
    }

    #viewWorkersModal .modal-content {
        border-radius: 14px;
        overflow: hidden;
    }

    #viewWorkersModal .modal-body {
        max-height: 62vh;
        overflow-y: auto;
    }

    #viewWorkersModal table {
        font-size: 0.95rem;
    }

    #viewWorkersModal th,
    #viewWorkersModal td {
        padding: 0.85rem 1rem;
    }

    /* Tablet */
    @media (max-width: 768px) {
        .workers-modal-dialog {
            width: calc(100vw - 24px) !important;
            max-width: calc(100vw - 24px) !important;
            margin: 80px auto 1rem !important;
        }

        #viewWorkersModal .modal-body {
            max-height: 55vh !important;
        }

        .modal-content {
            border-radius: 18px !important;
        }
    }
    .fingerprint-trigger-btn {
                width: 100px;   /* Slightly smaller than desktop but still large */
                height: 100px;
                font-size: 2.8rem;
            }
            .scan-pulse-container {
                padding: 2rem !important; /* Reduces padding to give button more room */
            }
    /* Phone */
    @media (max-width: 576px) {
        .fingerprint-trigger-btn {
                width: 100px;   /* Slightly smaller than desktop but still large */
                height: 100px;
                font-size: 2.8rem;
            }
            .scan-pulse-container {
                padding: 2rem !important; /* Reduces padding to give button more room */
            }
        .workers-modal-dialog {
            width: calc(100vw - 18px) !important;
            max-width: calc(100vw - 18px) !important;
        }

        #viewWorkersModal .modal-title {
            font-size: 1rem;
        }

        #viewWorkersModal thead th,
        #allWorkersTableBody td {
            font-size: 0.82rem;
            padding: 0.55rem !important;
        }

        #viewWorkersModal .modal-footer {
            padding: 0.5rem 0.75rem;
        }

        #viewWorkersModal .btn {
            padding: 0.25rem 0.6rem;
        }
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
                    <button type="button" class="btn btn-outline-secondary fw-semibold btn-sm px-3 py-2" 
                            data-bs-toggle="modal" data-bs-target="#viewWorkersModal">
                        <i class="bi bi-people-fill"></i> View Enrolled Workers
                    </button>
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
<div class="modal fade" id="registerWorkerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
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

<!-- VIEW ENROLLED WORKERS MODAL -->
<div class="modal fade" id="viewWorkersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable workers-modal-dialog">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">All Enrolled Workers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Trade</th>
                                <th>Enrolled</th>
                            </tr>
                        </thead>
                        <tbody id="allWorkersTableBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-between py-2">
                <button id="prevPage" type="button" class="btn btn-sm btn-outline-primary" disabled>
                    Previous
                </button>

                <span id="pageInfo" class="small text-muted">Page 1</span>

                <button id="nextPage" type="button" class="btn btn-sm btn-outline-primary">
                    Next
                </button>
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
        const viewWorkersModal = document.getElementById('viewWorkersModal');
        
        // Load workers immediately when the View Enrolled Workers modal opens.
        viewWorkersModal?.addEventListener('show.bs.modal', function() {
            // Force reset to page 1 every time the modal is opened.
            loadWorkers(1);
        });
        
        let scannedWorkerIds = new Set();

        // --- GLOBAL HARDWARE SCAN AND LOOKUP ENGINE ---
        btnGlobalScan?.addEventListener('click', async function () {
            globalScanStatus.className = "alert alert-warning border text-dark small py-2 mb-0";
            globalScanStatus.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Polling hardware credential layer...';

            try {
                // 1. Fetch authentication options from your WebAuthnController
                const response = await fetch('/passkeys/login/options', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    }
                });

                const options = await response.json();

                // 2. Trigger the browser's biometric/security key prompt
                const credential = await SimpleWebAuthnBrowser.startAuthentication(options);

                // 3. Post the credential assertion to the server for verification
                const submitResponse = await fetch('/passkeys/login', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'Accept': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    body: JSON.stringify(credential)
                });

                const result = await submitResponse.json();

                if (submitResponse.ok && result.worker) {
                    globalScanStatus.className = "alert alert-success border text-success small py-2 mb-0";
                    globalScanStatus.innerHTML = `<i class="bi bi-check-circle-fill"></i> Verified: <strong>${result.worker.first_name} ${result.worker.last_name}</strong>`;
                    
                    appendWorkerToLog(result.worker);
                } else {
                    throw new Error('Authentication failed: Worker not recognized.');
                }
            } catch (err) {
                console.error(err);
                globalScanStatus.className = "alert alert-danger border text-danger small py-2 mb-0";
                globalScanStatus.innerHTML = `<i class="bi bi-exclamation-triangle-fill"></i> ${err.message}`;
            }
        });

        // --- DYNAMICALLY CONSTRUCT ATTENDANCE LOG VIEW ---
        function appendWorkerToLog(worker) {
            if (scannedWorkerIds.has(worker.worker_id)) {
                const existingRow = document.getElementById(`row-worker-${worker.worker_id}`);

                if (existingRow) {
                    existingRow.style.backgroundColor = '#fff3cd';

                    setTimeout(() => {
                        existingRow.style.backgroundColor = 'transparent';
                    }, 1500);
                }

                return;
            }

            if (emptyRowPlaceholder) {
                emptyRowPlaceholder.remove();
            }

            scannedWorkerIds.add(worker.worker_id);
            scannedCountBadge.innerText = `${scannedWorkerIds.size} Active Logs`;
            formSubmitContainer.classList.remove('d-none');

            const tableRowHtml = `
                <tr class="border-bottom" id="row-worker-${worker.worker_id}">
                    <td class="py-3">
                        <div class="fw-semibold text-dark">${worker.first_name} ${worker.last_name}</div>
                        <input type="hidden" name="biometric_verified[${worker.worker_id}]" value="1">
                    </td>
                    <td class="py-3 text-muted">${worker.trade || 'General'}</td>
                    <td class="py-3">
                        <div class="d-flex justify-content-center flex-wrap gap-2">
                            <input type="radio" class="btn-check" name="attendance[${worker.worker_id}]" id="present-${worker.worker_id}" value="present" checked>
                            <label class="btn btn-outline-success btn-sm px-3 rounded-pill" for="present-${worker.worker_id}">Present</label>

                            <input type="radio" class="btn-check" name="attendance[${worker.worker_id}]" id="late-${worker.worker_id}" value="late">
                            <label class="btn btn-outline-warning btn-sm px-3 rounded-pill" for="late-${worker.worker_id}">Late</label>
                        </div>
                    </td>
                </tr>
            `;

            attendanceLogTableBody.insertAdjacentHTML('beforeend', tableRowHtml);
        }

        // --- NEW WORKER REGISTRATION LOGIC ---
        let capturedPasskeyCredential = null;

        const btnRegisterFingerprint = document.getElementById('btnRegisterWorkerFingerprint');
        const btnSaveWorker = document.getElementById('btnSaveWorkerRecord');
        const regStatusLabel = document.getElementById('registerFingerprintLabel');

        btnRegisterFingerprint?.addEventListener('click', async () => {
            try {
                // 1. Get registration options from server
                const response = await fetch('/passkeys/register/options', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    body: JSON.stringify({ 
                        first_name: document.getElementById('regFirstName').value,
                        last_name: document.getElementById('regLastName').value 
                    })
                });

                const options = await response.json();

                // 2. Trigger browser passkey registration
                capturedPasskeyCredential = await SimpleWebAuthnBrowser.startRegistration(options);

                // 3. Update UI
                regStatusLabel.innerText = "Biometric token captured successfully!";
                regStatusLabel.className = "d-block text-success small mt-1";
                btnSaveWorker.disabled = false;
            } catch (err) {
                console.error(err);
                regStatusLabel.innerText = "Registration failed. Please try again.";
            }
        });

        btnSaveWorker?.addEventListener('click', async () => {
            const workerData = {
                first_name: document.getElementById('regFirstName').value,
                last_name: document.getElementById('regLastName').value,
                trade: document.getElementById('regTrade').value,
                credential: capturedPasskeyCredential
            };

            const response = await fetch('/supervisor/workers/register-biometric', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                },
                body: JSON.stringify(workerData)
            });

            if (response.ok) {
                alert('Worker successfully registered!');
                window.location.reload();
            } else {
                alert('Failed to save worker.');
            }
        });
    });

    let currentPage = 1;

    async function loadWorkers(page = 1) {
        const tableBody = document.getElementById('allWorkersTableBody');
        const pageInfo = document.getElementById('pageInfo');
        const prevPageBtn = document.getElementById('prevPage');
        const nextPageBtn = document.getElementById('nextPage');

        tableBody.innerHTML = '<tr><td colspan="3" class="text-center py-4">Loading...</td></tr>';

        try {
            const res = await fetch(`/supervisor/workers/list?page=${page}`, {
                headers: { 
                    'Accept': 'application/json' 
                }
            });

            if (!res.ok) {
                throw new Error('Unable to load workers.');
            }

            const data = await res.json();
            const workers = data.data || [];

            if (!workers.length) {
                tableBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">No enrolled workers found.</td></tr>';
            } else {
                tableBody.innerHTML = workers.map(w => `
                    <tr>
                        <td class="ps-4">${w.first_name} ${w.last_name}</td>
                        <td>${w.trade || 'General'}</td>
                        <td>${w.created_at ? new Date(w.created_at).toLocaleDateString() : '-'}</td>
                    </tr>
                `).join('');
            }

            currentPage = data.current_page || page;
            pageInfo.innerText = `Page ${currentPage}`;
            prevPageBtn.disabled = currentPage <= 1;
            nextPageBtn.disabled = currentPage >= (data.last_page || currentPage);
        } catch (error) {
            console.error(error);
            tableBody.innerHTML = '<tr><td colspan="3" class="text-danger text-center py-4">Error loading data.</td></tr>';
        }
    }

    // Event Listeners for Pagination
    document.getElementById('prevPage')?.addEventListener('click', () => loadWorkers(currentPage - 1));
    document.getElementById('nextPage')?.addEventListener('click', () => loadWorkers(currentPage + 1));

    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        });
    });
</script>
@endpush