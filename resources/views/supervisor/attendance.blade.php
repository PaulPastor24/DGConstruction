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
            width: 112px !important;
            height: 112px !important;
            min-width: 112px !important;
            min-height: 112px !important;
            border-radius: 50% !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 3.2rem !important;
            line-height: 1 !important;
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.28);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .fingerprint-trigger-btn i {
            font-size: 3.2rem !important;
            line-height: 1 !important;
        }

        .fingerprint-trigger-btn:active {
            transform: scale(0.95);
        }

        .attendance-row-new {
            animation: attendanceFadeIn 0.45s ease;
        }

        .attendance-row-updated {
            animation: attendancePulse 0.8s ease;
        }

        @keyframes attendanceFadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
                background-color: #e7f5ff;
            }

            to {
                opacity: 1;
                transform: translateY(0);
                background-color: transparent;
            }
        }

        @keyframes attendancePulse {
            0% {
                background-color: #fff3cd;
            }

            100% {
                background-color: transparent;
            }
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

            .fingerprint-trigger-btn {
                width: 96px !important;
                height: 96px !important;
                min-width: 96px !important;
                min-height: 96px !important;
                font-size: 2.8rem !important;
            }

            .fingerprint-trigger-btn i {
                font-size: 2.8rem !important;
            }

            .scan-pulse-container {
                padding: 2rem !important;
            }
        }

        /* Phone */
        @media (max-width: 576px) {
            #supervisorAttendanceTable {
                min-width: 0 !important;
                width: 100% !important;
            }

            #supervisorAttendanceTable thead {
                display: none;
            }

            #attendanceLogTableBody tr {
                display: block;
                margin-bottom: 1rem;
                padding: 1rem;
                border: 1px solid #e5e7eb !important;
                border-radius: 16px;
                background: #ffffff;
                box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
            }

            #attendanceLogTableBody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                width: 100%;
                border: 0 !important;
                padding: 0.45rem 0 !important;
                font-size: 0.95rem;
                text-align: right !important;
            }

            #attendanceLogTableBody td::before {
                content: attr(data-label);
                font-weight: 700;
                color: #111827;
                text-align: left;
                padding-right: 1rem;
            }

            #attendanceLogTableBody td:first-child {
                display: block;
                text-align: left !important;
                padding-bottom: 0.75rem !important;
                border-bottom: 1px solid #e5e7eb !important;
                margin-bottom: 0.5rem;
            }

            #attendanceLogTableBody td:first-child::before {
                content: '';
                display: none;
            }

            #attendanceLogTableBody td:first-child .fw-semibold {
                font-size: 1.15rem;
                line-height: 1.2;
            }

            #attendanceLogTableBody td:nth-child(2) {
                display: block;
                text-align: left !important;
                color: #6b7280 !important;
                padding-top: 0 !important;
                margin-bottom: 0.5rem;
            }

            #attendanceLogTableBody td:nth-child(2)::before {
                content: '';
                display: none;
            }

            #attendanceLogTableBody td:last-child {
                display: block;
                text-align: left !important;
                padding-top: 0.75rem !important;
                border-top: 1px solid #e5e7eb !important;
                margin-top: 0.5rem;
            }

            #attendanceLogTableBody td:last-child::before {
                content: 'Status Log';
                display: block;
                font-weight: 700;
                margin-bottom: 0.5rem;
            }

            #attendanceLogTableBody td:last-child .d-flex {
                align-items: flex-start !important;
            }

            #attendanceLogTableBody .attendance-action-btn {
                width: 100%;
                margin-top: 0.25rem;
            }

            #attendanceLogTableBody .badge {
                font-size: 0.8rem;
                padding: 0.4rem 0.7rem;
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

                    <button type="button" class="btn btn-outline-dark fw-semibold btn-sm px-3 py-2"
                            data-bs-toggle="modal" data-bs-target="#manualAttendanceModal">
                        <i class="bi bi-pencil-square"></i> Manual Attendance
                    </button>

                    <button type="button" class="btn btn-primary fw-semibold btn-sm px-3 py-2"
                            data-bs-toggle="modal" data-bs-target="#registerWorkerModal">
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
                                        <th class="pb-3 border-0">Personnel Name</th>
                                        <th class="pb-3 border-0">Trade / Designation</th>
                                        <th class="pb-3 border-0 text-center">Time In</th>
                                        <th class="pb-3 border-0 text-center">Break Out</th>
                                        <th class="pb-3 border-0 text-center">Break In</th>
                                        <th class="pb-3 border-0 text-center">Time Out</th>
                                        <th class="pb-3 border-0 text-center">Status Log</th>
                                    </tr>
                                </thead>

                                <tbody id="attendanceLogTableBody">
                                    <tr id="emptyRowPlaceholder">
                                        <td colspan="7" class="text-center py-5 text-muted fst-italic">
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
                <h5 class="modal-title fw-bold" id="registerWorkerModalLabel">
                    <i class="bi bi-person-badge text-primary"></i> Fast Worker Enrollment
                </h5>

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
                            <label class="d-block small fw-bold text-dark mb-2">
                                <i class="bi bi-fingerprint"></i> Device Passkey Association Layer
                            </label>

                            <button type="button" class="btn btn-dark btn-sm fw-semibold" id="btnRegisterWorkerFingerprint">
                                <i class="bi bi-shield-plus"></i> Initialize Fingerprint Capture
                            </button>

                            <span class="d-block text-muted small mt-1" id="registerFingerprintLabel">
                                Biometrics not captured yet.
                            </span>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light fw-medium btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary fw-semibold btn-sm" id="btnSaveWorkerRecord" disabled>
                    Save Worker Record
                </button>
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

<!-- MANUAL ATTENDANCE MODAL -->
<div class="modal fade" id="manualAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square text-dark"></i> Manual Attendance Log
                    </h5>
                    <p class="text-muted small mb-0">
                        Use this only when the biometric reader is unavailable or not working.
                    </p>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body py-3">
                <div id="manualAttendanceStatus" class="alert alert-light border text-muted small py-2 mb-3">
                    <i class="bi bi-info-circle-fill text-primary"></i>
                    Select a worker and attendance action.
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Worker</label>
                    <select id="manualWorkerSelect" class="form-select" required>
                        <option value="">Loading workers...</option>
                    </select>
                    <div class="form-text">
                        The worker must already be enrolled in the system.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Attendance Action</label>
                    <select id="manualActionSelect" class="form-select" required>
                        <option value="time_in">Time In</option>
                        <option value="break_out">Break Out</option>
                        <option value="break_in">Break In</option>
                        <option value="time_out">Time Out</option>
                    </select>
                </div>

                <div class="mb-0">
                    <label class="form-label small fw-semibold text-muted">Reason / Remarks</label>
                    <textarea id="manualReasonInput" class="form-control" rows="3"
                              placeholder="Example: Biometric reader not working / fingerprint scan failed."></textarea>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light fw-medium btn-sm" data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button" class="btn btn-dark fw-semibold btn-sm" id="btnSaveManualAttendance">
                    <i class="bi bi-save"></i> Save Manual Log
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
        const csrfToken = '{{ csrf_token() }}';

        const btnGlobalScan = document.getElementById('btnGlobalScan');
        const globalScanStatus = document.getElementById('globalScanStatus');
        const attendanceLogTableBody = document.getElementById('attendanceLogTableBody');
        const formSubmitContainer = document.getElementById('formSubmitContainer');
        const scannedCountBadge = document.getElementById('scannedCount');
        const viewWorkersModal = document.getElementById('viewWorkersModal');
        const manualAttendanceModal = document.getElementById('manualAttendanceModal');
        const manualWorkerSelect = document.getElementById('manualWorkerSelect');
        const manualActionSelect = document.getElementById('manualActionSelect');
        const manualReasonInput = document.getElementById('manualReasonInput');
        const manualAttendanceStatus = document.getElementById('manualAttendanceStatus');
        const btnSaveManualAttendance = document.getElementById('btnSaveManualAttendance');

        const btnRegisterFingerprint = document.getElementById('btnRegisterWorkerFingerprint');
        const btnSaveWorker = document.getElementById('btnSaveWorkerRecord');
        const regStatusLabel = document.getElementById('registerFingerprintLabel');

        const prevPageBtn = document.getElementById('prevPage');
        const nextPageBtn = document.getElementById('nextPage');
        const attendanceDateInput = document.querySelector('input[name="attendance_date"]');

        let capturedPasskeyCredential = null;
        let currentPage = 1;
        let scannedWorkerIds = new Set();
        let manualWorkersCache = [];

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function normalizeWorker(rawWorker, fallback = {}) {
            const worker = rawWorker || {};

            return {
                worker_id: worker.worker_id || worker.id || fallback.worker_id || fallback.id || null,
                first_name: worker.first_name || fallback.first_name || '',
                last_name: worker.last_name || fallback.last_name || '',
                trade: worker.trade || fallback.trade || 'General',
                created_at: worker.created_at || fallback.created_at || new Date().toISOString()
            };
        }

        function formatTime(timeValue) {
            if (!timeValue) {
                return '-';
            }

            const [hour, minute] = String(timeValue).split(':');

            let h = parseInt(hour, 10);
            const ampm = h >= 12 ? 'PM' : 'AM';

            h = h % 12;
            h = h ? h : 12;

            return `${h}:${minute} ${ampm}`;
        }

        function selectedDateValue() {
            return attendanceDateInput?.value || new Date().toISOString().slice(0, 10);
        }

        function isFivePmOrLaterForSelectedDate() {
            const selected = selectedDateValue();
            const today = new Date().toISOString().slice(0, 10);

            if (selected < today) {
                return true;
            }

            if (selected > today) {
                return false;
            }

            const now = new Date();

            return now.getHours() >= 17;
        }

        function renderEmptyAttendanceRow() {
            attendanceLogTableBody.innerHTML = `
                <tr id="emptyRowPlaceholder">
                    <td colspan="7" class="text-center py-5 text-muted fst-italic">
                        <i class="bi bi-person-bounding-box d-block fs-2 mb-2 text-secondary"></i>
                        No personnel checked in yet during this session.
                    </td>
                </tr>
            `;
        }

        function updateActiveLogCount() {
            const activeRows = attendanceLogTableBody.querySelectorAll('tr[data-active-log="1"]').length;

            scannedCountBadge.innerText = `${activeRows} Active Logs`;

            if (activeRows > 0) {
                formSubmitContainer.classList.remove('d-none');
            } else {
                formSubmitContainer.classList.add('d-none');
            }
        }

        function getStatusBadge(status) {
            const value = String(status || 'present').toLowerCase();

            if (value === 'present') {
                return '<span class="badge bg-success rounded-pill">Present</span>';
            }

            if (value === 'late') {
                return '<span class="badge bg-warning text-dark rounded-pill">Late</span>';
            }

            if (value === 'absent') {
                return '<span class="badge bg-danger rounded-pill">Absent</span>';
            }

            return `<span class="badge bg-secondary rounded-pill">${escapeHtml(value || 'Not Logged')}</span>`;
        }

        function getActionButtons(record) {
            if (!record || record.status === 'absent' || record.status === 'not_logged') {
                return '';
            }

            let buttons = '';

            if (record.time_in && !record.break_out) {
                buttons += `
                    <button type="button"
                            class="btn btn-sm btn-outline-primary attendance-action-btn"
                            data-worker-id="${escapeHtml(record.worker_id)}"
                            data-action="break_out">
                        Break Out
                    </button>
                `;
            }

            if (record.break_out && !record.break_in) {
                buttons += `
                    <button type="button"
                            class="btn btn-sm btn-outline-warning attendance-action-btn"
                            data-worker-id="${escapeHtml(record.worker_id)}"
                            data-action="break_in">
                        Break In
                    </button>
                `;
            }

            if (record.time_in && !record.time_out) {
                if (isFivePmOrLaterForSelectedDate()) {
                    buttons += `
                        <button type="button"
                                class="btn btn-sm btn-outline-dark attendance-action-btn"
                                data-worker-id="${escapeHtml(record.worker_id)}"
                                data-action="time_out">
                            Time Out
                        </button>
                    `;
                } else {
                    buttons += `
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary"
                                disabled>
                            Time Out 5PM
                        </button>
                    `;
                }
            }

            return buttons;
        }

        function buildAttendanceRow(record) {
            const workerKey = String(record.worker_id);
            const fullName = `${escapeHtml(record.first_name)} ${escapeHtml(record.last_name)}`.trim();
            const trade = escapeHtml(record.trade || 'General');

            return `
                <tr class="border-bottom attendance-row-fade"
                    id="row-worker-${workerKey}"
                    data-worker-id="${workerKey}"
                    data-active-log="1">
                    <td class="py-3" data-label="Personnel Name">
                        <div class="fw-semibold text-dark">${fullName}</div>
                        <input type="hidden" name="biometric_verified[${workerKey}]" value="1">
                    </td>

                    <td class="py-3 text-muted" data-label="Trade">
                        ${trade}
                    </td>

                    <td class="py-3 text-center" data-label="Time In">
                        ${formatTime(record.time_in)}
                    </td>

                    <td class="py-3 text-center" data-label="Break Out">
                        ${formatTime(record.break_out)}
                    </td>

                    <td class="py-3 text-center" data-label="Break In">
                        ${formatTime(record.break_in)}
                    </td>

                    <td class="py-3 text-center" data-label="Time Out">
                        ${formatTime(record.time_out)}
                    </td>

                    <td class="py-3 text-center" data-label="Status Log">
                        <div class="d-flex flex-column align-items-center gap-2">
                            ${getStatusBadge(record.status)}

                            <div class="d-flex justify-content-center flex-wrap gap-1">
                                ${getActionButtons(record)}
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }

        function upsertAttendanceRecord(record, animate = true) {
            if (!record || !record.worker_id) {
                return;
            }

            const emptyRowPlaceholder = document.getElementById('emptyRowPlaceholder');

            if (emptyRowPlaceholder) {
                emptyRowPlaceholder.remove();
            }

            const workerKey = String(record.worker_id);
            const existingRow = document.getElementById(`row-worker-${workerKey}`);
            const newRowHtml = buildAttendanceRow(record);

            scannedWorkerIds.add(workerKey);

            if (existingRow) {
                existingRow.outerHTML = newRowHtml;

                const updatedRow = document.getElementById(`row-worker-${workerKey}`);

                if (animate && updatedRow) {
                    updatedRow.classList.add('attendance-row-updated');

                    setTimeout(() => {
                        updatedRow.classList.remove('attendance-row-updated');
                    }, 900);
                }
            } else {
                attendanceLogTableBody.insertAdjacentHTML('beforeend', newRowHtml);

                const insertedRow = document.getElementById(`row-worker-${workerKey}`);

                if (animate && insertedRow) {
                    insertedRow.classList.add('attendance-row-new');

                    setTimeout(() => {
                        insertedRow.classList.remove('attendance-row-new');
                    }, 900);
                }
            }

            updateActiveLogCount();
        }

        function removeMissingRows(latestRecords) {
            const latestIds = new Set(
                latestRecords.map(record => String(record.worker_id))
            );

            attendanceLogTableBody
                .querySelectorAll('tr[data-worker-id]')
                .forEach(row => {
                    if (!latestIds.has(String(row.dataset.workerId))) {
                        row.remove();
                    }
                });

            if (latestIds.size === 0) {
                scannedWorkerIds.clear();
                renderEmptyAttendanceRow();
            }

            updateActiveLogCount();
        }

        let isAttendanceFetching = false;
        let isFirstAttendanceLoad = true;

        async function loadTodayAttendance(options = {}) {
            const silent = options.silent ?? false;

            if (isAttendanceFetching) {
                return;
            }

            isAttendanceFetching = true;

            try {
                const response = await fetch(`/supervisor/attendance/today?date=${selectedDateValue()}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(result.message || 'Failed to load attendance.');
                }

                const records = result.data || [];

                if (records.length === 0) {
                    scannedWorkerIds.clear();
                    renderEmptyAttendanceRow();
                    updateActiveLogCount();
                    isFirstAttendanceLoad = false;
                    return;
                }

                if (isFirstAttendanceLoad && !silent) {
                    attendanceLogTableBody.innerHTML = '';
                }

                records.forEach(record => {
                    upsertAttendanceRecord(record, !isFirstAttendanceLoad);
                });

                removeMissingRows(records);

                isFirstAttendanceLoad = false;
            } catch (error) {
                console.error(error);

                if (!silent) {
                    attendanceLogTableBody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-danger text-center py-5">
                                ${escapeHtml(error.message || 'Error loading attendance.')}
                            </td>
                        </tr>
                    `;
                }
            } finally {
                isAttendanceFetching = false;
            }
        }

        async function saveScannedWorkerAttendance(worker) {
            const response = await fetch('/supervisor/attendance/log-worker', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    worker_id: worker.worker_id,
                    log_date: selectedDateValue(),
                    action: 'time_in'
                })
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(result.message || 'Failed to save attendance.');
            }

            upsertAttendanceRecord(result.attendance, true);
        }

        async function updateAttendanceAction(workerId, action) {
            const response = await fetch('/supervisor/attendance/log-worker', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    worker_id: workerId,
                    log_date: selectedDateValue(),
                    action: action
                })
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                alert(result.message || 'Failed to update attendance.');
                return;
            }

            upsertAttendanceRecord(result.attendance, true);
        }

        async function fetchWorkersPage(page = 1) {
            const response = await fetch(`/supervisor/workers/list?page=${page}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(data.message || 'Unable to load workers.');
            }

            return data;
        }

        async function loadManualWorkers() {
            if (!manualWorkerSelect) {
                return;
            }

            manualWorkerSelect.innerHTML = '<option value="">Loading workers...</option>';
            btnSaveManualAttendance.disabled = true;

            manualAttendanceStatus.className = 'alert alert-light border text-muted small py-2 mb-3';
            manualAttendanceStatus.innerHTML = `
                <span class="spinner-border spinner-border-sm me-1"></span>
                Loading enrolled workers...
            `;

            try {
                const firstPage = await fetchWorkersPage(1);

                const allWorkers = [];
                const firstPageWorkers = Array.isArray(firstPage)
                    ? firstPage
                    : (firstPage.data || firstPage.workers || []);

                firstPageWorkers.forEach(item => allWorkers.push(normalizeWorker(item)));

                const lastPage = Number(firstPage.last_page || 1);

                if (!Array.isArray(firstPage) && lastPage > 1) {
                    for (let page = 2; page <= lastPage; page++) {
                        const nextPage = await fetchWorkersPage(page);
                        const nextWorkers = nextPage.data || nextPage.workers || [];
                        nextWorkers.forEach(item => allWorkers.push(normalizeWorker(item)));
                    }
                }

                manualWorkersCache = allWorkers.filter(worker => worker.worker_id);

                if (!manualWorkersCache.length) {
                    manualWorkerSelect.innerHTML = '<option value="">No enrolled workers found</option>';

                    manualAttendanceStatus.className = 'alert alert-warning border text-dark small py-2 mb-3';
                    manualAttendanceStatus.innerHTML = `
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        No enrolled workers available for manual attendance.
                    `;

                    return;
                }

                manualWorkerSelect.innerHTML = `
                    <option value="">Select worker...</option>
                    ${manualWorkersCache.map(worker => {
                        const fullName = `${escapeHtml(worker.first_name)} ${escapeHtml(worker.last_name)}`.trim();
                        const trade = escapeHtml(worker.trade || 'General');

                        return `
                            <option value="${escapeHtml(worker.worker_id)}">
                                ${fullName} — ${trade}
                            </option>
                        `;
                    }).join('')}
                `;

                btnSaveManualAttendance.disabled = false;

                manualAttendanceStatus.className = 'alert alert-light border text-muted small py-2 mb-3';
                manualAttendanceStatus.innerHTML = `
                    <i class="bi bi-info-circle-fill text-primary"></i>
                    Select a worker and attendance action.
                `;
            } catch (error) {
                console.error(error);

                manualWorkerSelect.innerHTML = '<option value="">Unable to load workers</option>';

                manualAttendanceStatus.className = 'alert alert-danger border text-danger small py-2 mb-3';
                manualAttendanceStatus.innerHTML = `
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    ${escapeHtml(error.message || 'Unable to load workers.')}
                `;
            }
        }

        async function saveManualAttendance() {
            if (!manualWorkerSelect || !manualActionSelect) {
                return;
            }

            const workerId = manualWorkerSelect.value;
            const action = manualActionSelect.value || 'time_in';
            const reason = manualReasonInput?.value.trim()
                || 'Manual attendance log because biometric reader is unavailable.';

            if (!workerId) {
                manualAttendanceStatus.className = 'alert alert-warning border text-dark small py-2 mb-3';
                manualAttendanceStatus.innerHTML = `
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Please select a worker first.
                `;

                return;
            }

            btnSaveManualAttendance.disabled = true;
            btnSaveManualAttendance.innerHTML = `
                <span class="spinner-border spinner-border-sm me-1"></span>
                Saving...
            `;

            manualAttendanceStatus.className = 'alert alert-warning border text-dark small py-2 mb-3';
            manualAttendanceStatus.innerHTML = `
                <span class="spinner-border spinner-border-sm me-1"></span>
                Saving manual attendance...
            `;

            try {
                const response = await fetch('/supervisor/attendance/log-worker', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        worker_id: workerId,
                        log_date: selectedDateValue(),
                        action: action,
                        manual: true,
                        biometric_matched: false,
                        remarks: reason
                    })
                });

                const result = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(result.message || 'Failed to save manual attendance.');
                }

                if (result.attendance) {
                    upsertAttendanceRecord(result.attendance, true);
                } else {
                    await loadTodayAttendance();
                }

                manualAttendanceStatus.className = 'alert alert-success border text-success small py-2 mb-3';
                manualAttendanceStatus.innerHTML = `
                    <i class="bi bi-check-circle-fill"></i>
                    Manual attendance saved successfully.
                `;

                const selectedWorker = manualWorkersCache.find(worker => String(worker.worker_id) === String(workerId));

                globalScanStatus.className = 'alert alert-success border text-success small py-2 mb-0';
                globalScanStatus.innerHTML = `
                    <i class="bi bi-check-circle-fill"></i>
                    Manual ${escapeHtml(action.replace('_', ' '))} saved
                    ${selectedWorker ? `for <strong>${escapeHtml(selectedWorker.first_name)} ${escapeHtml(selectedWorker.last_name)}</strong>` : ''}.
                `;

                manualActionSelect.value = 'time_in';
                if (manualReasonInput) {
                    manualReasonInput.value = '';
                }

                setTimeout(() => {
                    const modalInstance = bootstrap.Modal.getInstance(manualAttendanceModal)
                        || bootstrap.Modal.getOrCreateInstance(manualAttendanceModal);

                    modalInstance.hide();
                }, 700);
            } catch (error) {
                console.error(error);

                manualAttendanceStatus.className = 'alert alert-danger border text-danger small py-2 mb-3';
                manualAttendanceStatus.innerHTML = `
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    ${escapeHtml(error.message || 'Failed to save manual attendance.')}
                `;
            } finally {
                btnSaveManualAttendance.disabled = false;
                btnSaveManualAttendance.innerHTML = `
                    <i class="bi bi-save"></i> Save Manual Log
                `;
            }
        }

        async function loadWorkers(page = 1) {
            const tableBody = document.getElementById('allWorkersTableBody');
            const pageInfo = document.getElementById('pageInfo');

            tableBody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center py-4">
                        <span class="spinner-border spinner-border-sm me-1"></span>
                        Loading workers...
                    </td>
                </tr>
            `;

            try {
                const res = await fetch(`/supervisor/workers/list?page=${page}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(data.message || 'Unable to load workers.');
                }

                const workers = Array.isArray(data)
                    ? data
                    : (data.data || data.workers || []);

                if (!workers.length) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                No enrolled workers found.
                            </td>
                        </tr>
                    `;
                } else {
                    tableBody.innerHTML = workers.map(item => {
                        const worker = normalizeWorker(item);

                        const fullName = `${escapeHtml(worker.first_name)} ${escapeHtml(worker.last_name)}`.trim();
                        const trade = escapeHtml(worker.trade || 'General');
                        const enrolledDate = worker.created_at
                            ? new Date(worker.created_at).toLocaleDateString()
                            : '-';

                        return `
                            <tr>
                                <td class="ps-4">${fullName}</td>
                                <td>${trade}</td>
                                <td>${enrolledDate}</td>
                            </tr>
                        `;
                    }).join('');
                }

                currentPage = data.current_page || page;
                pageInfo.innerText = `Page ${currentPage}`;

                prevPageBtn.disabled = currentPage <= 1;
                nextPageBtn.disabled = currentPage >= (data.last_page || currentPage);
            } catch (error) {
                console.error(error);

                tableBody.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-danger text-center py-4">
                            ${escapeHtml(error.message || 'Error loading worker data.')}
                        </td>
                    </tr>
                `;
            }
        }

        viewWorkersModal?.addEventListener('show.bs.modal', function () {
            loadWorkers(1);
        });

        manualAttendanceModal?.addEventListener('show.bs.modal', function () {
            loadManualWorkers();
        });

        btnSaveManualAttendance?.addEventListener('click', saveManualAttendance);

        prevPageBtn?.addEventListener('click', function () {
            if (currentPage > 1) {
                loadWorkers(currentPage - 1);
            }
        });

        nextPageBtn?.addEventListener('click', function () {
            loadWorkers(currentPage + 1);
        });

        attendanceLogTableBody?.addEventListener('click', function (event) {
            const button = event.target.closest('.attendance-action-btn');

            if (!button) {
                return;
            }

            updateAttendanceAction(button.dataset.workerId, button.dataset.action);
        });

        attendanceDateInput?.addEventListener('change', function () {
            isFirstAttendanceLoad = true;
            scannedWorkerIds.clear();
            renderEmptyAttendanceRow();
            loadTodayAttendance();
        });

        btnGlobalScan?.addEventListener('click', async function () {
            globalScanStatus.className = 'alert alert-warning border text-dark small py-2 mb-0';
            globalScanStatus.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2"></span>
                Polling hardware credential layer...
            `;

            try {
                const response = await fetch('/passkeys/login/options', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const options = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(options.message || 'Failed to get login options.');
                }

                const credential = await SimpleWebAuthnBrowser.startAuthentication(options);

                const submitResponse = await fetch('/passkeys/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(credential)
                });

                const result = await submitResponse.json().catch(() => ({}));

                if (submitResponse.ok && result.worker) {
                    const worker = normalizeWorker(result.worker);

                    globalScanStatus.className = 'alert alert-success border text-success small py-2 mb-0';
                    globalScanStatus.innerHTML = `
                        <i class="bi bi-check-circle-fill"></i>
                        Verified:
                        <strong>${escapeHtml(worker.first_name)} ${escapeHtml(worker.last_name)}</strong>
                    `;

                    await saveScannedWorkerAttendance(worker);
                } else {
                    throw new Error(result.message || 'Authentication failed: Worker not recognized.');
                }
            } catch (err) {
                console.error(err);

                globalScanStatus.className = 'alert alert-danger border text-danger small py-2 mb-0';
                globalScanStatus.innerHTML = `
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    ${escapeHtml(err.message)}
                `;
            }
        });

        btnRegisterFingerprint?.addEventListener('click', async function () {
            const firstName = document.getElementById('regFirstName').value.trim();
            const lastName = document.getElementById('regLastName').value.trim();

            if (!firstName || !lastName) {
                alert('Please enter the worker first name and last name before capturing fingerprint.');
                return;
            }

            btnRegisterFingerprint.disabled = true;
            btnRegisterFingerprint.innerHTML = `
                <span class="spinner-border spinner-border-sm me-1"></span>
                Capturing...
            `;

            regStatusLabel.innerText = 'Starting biometric registration...';
            regStatusLabel.className = 'd-block text-muted small mt-1';

            try {
                const response = await fetch('/passkeys/register/options', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        first_name: firstName,
                        last_name: lastName
                    })
                });

                const options = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(options.message || 'Failed to get registration options.');
                }

                capturedPasskeyCredential = await SimpleWebAuthnBrowser.startRegistration(options);

                regStatusLabel.innerText = 'Biometric token captured successfully!';
                regStatusLabel.className = 'd-block text-success small mt-1';

                btnSaveWorker.disabled = false;
            } catch (err) {
                console.error(err);

                capturedPasskeyCredential = null;

                regStatusLabel.innerText = err.message || 'Registration failed. Please try again.';
                regStatusLabel.className = 'd-block text-danger small mt-1';

                btnSaveWorker.disabled = true;
            } finally {
                btnRegisterFingerprint.disabled = false;
                btnRegisterFingerprint.innerHTML = `
                    <i class="bi bi-shield-plus"></i>
                    Initialize Fingerprint Capture
                `;
            }
        });

        btnSaveWorker?.addEventListener('click', async function () {
            const firstName = document.getElementById('regFirstName').value.trim();
            const lastName = document.getElementById('regLastName').value.trim();
            const trade = document.getElementById('regTrade').value.trim() || 'General';

            if (!firstName || !lastName) {
                alert('Please enter the worker first name and last name.');
                return;
            }

            if (!capturedPasskeyCredential) {
                alert('Please capture the worker fingerprint/passkey first.');
                return;
            }

            btnSaveWorker.disabled = true;
            btnSaveWorker.innerHTML = `
                <span class="spinner-border spinner-border-sm me-1"></span>
                Saving...
            `;

            try {
                const response = await fetch('/supervisor/workers/register-biometric', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        first_name: firstName,
                        last_name: lastName,
                        trade: trade,
                        credential: capturedPasskeyCredential
                    })
                });

                const result = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(result.message || 'Failed to save worker.');
                }

                const savedWorker = normalizeWorker(result.worker || result.data || result, {
                    first_name: firstName,
                    last_name: lastName,
                    trade: trade
                });

                if (!savedWorker.worker_id) {
                    throw new Error('Worker was saved, but the server did not return worker_id.');
                }

                await saveScannedWorkerAttendance(savedWorker);
                await loadWorkers(1);

                const registerModalElement = document.getElementById('registerWorkerModal');

                if (window.bootstrap && registerModalElement) {
                    const registerModal = bootstrap.Modal.getInstance(registerModalElement)
                        || bootstrap.Modal.getOrCreateInstance(registerModalElement);

                    registerModal.hide();
                }

                document.getElementById('fastWorkerForm')?.reset();

                capturedPasskeyCredential = null;

                regStatusLabel.innerText = 'Biometrics not captured yet.';
                regStatusLabel.className = 'd-block text-muted small mt-1';

                btnSaveWorker.disabled = true;
                btnSaveWorker.innerHTML = 'Save Worker Record';

                globalScanStatus.className = 'alert alert-success border text-success small py-2 mb-0';
                globalScanStatus.innerHTML = `
                    <i class="bi bi-check-circle-fill"></i>
                    New worker added and timed in:
                    <strong>${escapeHtml(savedWorker.first_name)} ${escapeHtml(savedWorker.last_name)}</strong>
                `;
            } catch (error) {
                console.error(error);

                alert(error.message || 'Failed to save worker.');

                btnSaveWorker.disabled = false;
                btnSaveWorker.innerHTML = 'Save Worker Record';
            }
        });

        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.modal-backdrop').forEach(function (el) {
                    el.remove();
                });
            });
        });

        /*
            OPTION A: SMOOTH SILENT POLLING
            This checks attendance every 4 seconds without clearing the table.
            New and updated rows appear smoothly without an obvious reload.
        */
        let attendanceRefreshTimer = null;

        function startSilentAttendancePolling() {
            if (attendanceRefreshTimer) {
                clearInterval(attendanceRefreshTimer);
            }

            attendanceRefreshTimer = setInterval(function () {
                const registerModalOpen = document.getElementById('registerWorkerModal')?.classList.contains('show');
                const workersModalOpen = document.getElementById('viewWorkersModal')?.classList.contains('show');
                const manualModalOpen = document.getElementById('manualAttendanceModal')?.classList.contains('show');

                if (!registerModalOpen && !workersModalOpen && !manualModalOpen) {
                    loadTodayAttendance({ silent: true });
                }
            }, 4000);
        }

        loadTodayAttendance();
        startSilentAttendancePolling();
    });
</script>
@endpush