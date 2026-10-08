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
            width: min(960px, calc(100vw - 2rem)) !important;
            max-width: 960px !important;
            margin: auto !important;
        }

        #viewWorkersModal .modal-content {
            max-height: calc(100vh - 2rem);
            max-height: calc(100dvh - 2rem);
            border: 1px solid #dfece4;
            border-radius: 16px;
            overflow: hidden;
        }

        #viewWorkersModal .workers-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.15rem 1.35rem;
            border-bottom: 1px solid #e5eee6;
            background: linear-gradient(115deg, #f1f7f1 0%, #ffffff 76%);
        }

        #viewWorkersModal .workers-modal-heading {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 0.85rem;
        }

        #viewWorkersModal .workers-modal-icon {
            display: inline-flex;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #315c35;
            color: #fff;
            font-size: 1.15rem;
        }

        #viewWorkersModal .workers-modal-title {
            margin: 0;
            color: #203d26;
            font-size: 1.05rem;
            font-weight: 800;
        }

        #viewWorkersModal .workers-modal-subtitle {
            margin: 0.2rem 0 0;
            color: #718174;
            font-size: 0.8rem;
        }

        #viewWorkersModal .workers-count-pill {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            padding: 0.4rem 0.7rem;
            border: 1px solid #d8e7da;
            border-radius: 999px;
            background: #fff;
            color: #315c35;
            font-size: 0.75rem;
            font-weight: 800;
            white-space: nowrap;
        }

        #viewWorkersModal .modal-body {
            min-height: 0;
            max-height: min(68vh, 620px);
            overflow-y: auto;
        }

        #viewWorkersModal .workers-roster-table {
            min-width: 690px;
            font-size: 0.88rem;
        }

        #viewWorkersModal .workers-roster-table thead th {
            position: sticky;
            z-index: 1;
            top: 0;
            padding: 0.75rem 0.9rem;
            border-bottom: 1px solid #dfece4;
            background: #f4f8f4;
            color: #607263;
            font-size: 0.68rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        #supervisorAttendanceTable thead th {
            padding: 0.55rem 0.4rem !important;
            font-size: 0.66rem !important;
            line-height: 1.2;
            white-space: nowrap;
        }

        #viewWorkersModal .workers-roster-table tbody td {
            padding: 0.75rem 0.9rem;
            border-color: #edf2ed;
            vertical-align: middle;
        }

        #viewWorkersModal .workers-roster-table tbody tr:hover > * {
            background: #f8fbf8;
        }

        #viewWorkersModal .worker-list-info {
            gap: 0.7rem;
        }

        #viewWorkersModal .worker-list-avatar {
            width: 40px;
            height: 40px;
            min-width: 40px;
            max-width: 40px;
            border-radius: 50%;
        }

        #viewWorkersModal .worker-roster-name {
            color: #263a2b;
            font-weight: 700;
        }

        #viewWorkersModal .worker-trade-badge {
            display: inline-block;
            padding: 0.28rem 0.55rem;
            border: 1px solid #e2ebe3;
            border-radius: 999px;
            background: #f7faf7;
            color: #48634c;
            font-size: 0.75rem;
            font-weight: 700;
        }

        #viewWorkersModal .workers-enrolled-date {
            color: #68766a;
            white-space: nowrap;
        }

        #viewWorkersModal .workers-photo-action {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-color: #c9dccb;
            color: #315c35;
            font-weight: 700;
            white-space: nowrap;
        }

        #viewWorkersModal .workers-photo-action:hover,
        #viewWorkersModal .workers-photo-action:focus-within {
            border-color: #315c35;
            background: #315c35;
            color: #fff;
        }

        #viewWorkersModal .workers-modal-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.8rem 1.25rem;
            border-top: 1px solid #e5eee6;
            background: #f8fbf8;
        }

        #viewWorkersModal .workers-page-status {
            color: #637366;
            font-size: 0.8rem;
            font-weight: 700;
        }

        #viewWorkersModal .workers-page-btn {
            min-width: 96px;
            border-color: #c9dccb;
            color: #315c35;
            font-weight: 700;
        }

        #viewWorkersModal .workers-page-btn:hover:not(:disabled) {
            border-color: #315c35;
            background: #315c35;
            color: #fff;
        }

        #viewWorkersModal .workers-empty-state {
            padding: 2.5rem 1rem !important;
            color: #718174;
            text-align: center;
        }

        #viewWorkersModal .workers-row-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.4rem;
        }

        #viewWorkersModal .workers-row-action {
            display: inline-flex;
            width: 36px;
            height: 36px;
            align-items: center;
            justify-content: center;
            border: 1px solid #d8e7da;
            border-radius: 0.5rem;
            background: #fff;
            color: #315c35;
        }

        #viewWorkersModal .workers-row-action-edit:hover,
        #viewWorkersModal .workers-row-action-edit:focus-visible {
            border-color: #315c35;
            background: #315c35;
            color: #fff;
        }

        #viewWorkersModal .workers-row-action-view:hover,
        #viewWorkersModal .workers-row-action-view:focus-visible {
            border-color: #315c35;
            background: #eaf3eb;
            color: #244a29;
        }

        #viewWorkersModal .workers-row-action-delete {
            border-color: #f0d6d3;
            color: #a33a32;
        }

        #viewWorkersModal .workers-row-action-delete:hover,
        #viewWorkersModal .workers-row-action-delete:focus-visible {
            border-color: #a33a32;
            background: #a33a32;
            color: #fff;
        }

        #workerDetailsModal .modal-dialog {
            width: min(680px, calc(100vw - 1rem));
            max-width: 680px;
            margin: 0.5rem auto;
        }

        #workerDetailsModal .modal-content {
            max-height: calc(100vh - 1rem);
            max-height: calc(100dvh - 1rem);
            overflow: hidden;
            border: 1px solid #dfece4;
            border-radius: 16px;
        }

        #workerDetailsModal .modal-body {
            min-height: 0;
            overflow-y: auto;
            padding: 1.25rem;
        }

        #workerDetailsModal .worker-detail-hero {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            padding: 1.1rem;
            border: 1px solid #e3ece4;
            border-radius: 0.85rem;
            background: linear-gradient(115deg, #f1f7f1 0%, #ffffff 76%);
        }

        #workerDetailsModal .worker-detail-photo-button,
        #workerDetailsModal .worker-detail-photo-static {
            display: flex;
            width: 156px;
            height: 156px;
            flex: 0 0 156px;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 4px;
            border: 3px solid #fff;
            border-radius: 50%;
            background: #e8f0e9;
            box-shadow: 0 0 0 1px #d8e7da, 0 8px 18px rgba(49, 92, 53, 0.12);
        }

        #workerDetailsModal .worker-detail-photo-button {
            cursor: zoom-in;
            transition: transform 150ms ease, box-shadow 150ms ease;
        }

        #workerDetailsModal .worker-detail-photo-button:hover,
        #workerDetailsModal .worker-detail-photo-button:focus-visible {
            transform: scale(1.03);
            box-shadow: 0 0 0 2px #315c35, 0 10px 22px rgba(49, 92, 53, 0.2);
            outline: none;
        }

        #workerDetailsModal .worker-detail-photo-static {
            cursor: default;
        }

        #workerDetailsModal .worker-detail-photo-button .worker-avatar-wrap,
        #workerDetailsModal .worker-detail-photo-static .worker-avatar-wrap {
            display: block;
            width: 100%;
            height: 100%;
            flex: 0 0 100%;
        }

        #workerDetailsModal .worker-detail-avatar {
            display: flex;
            width: 100%;
            height: 100%;
            min-width: 100%;
            max-width: 100%;
            border: 0;
            border-radius: 50%;
            object-fit: cover;
        }

        #workerDetailsModal .worker-detail-copy {
            min-width: 0;
        }

        #workerDetailsModal .worker-detail-name {
            margin: 0 0 0.5rem;
            color: #203d26;
            font-size: 1.35rem;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        #workerDetailsModal .worker-detail-trade {
            display: inline-block;
            margin-bottom: 0.5rem;
            padding: 0.3rem 0.65rem;
            border: 1px solid #d8e7da;
            border-radius: 999px;
            background: #fff;
            color: #315c35;
            font-size: 0.78rem;
            font-weight: 800;
        }

        #workerDetailsModal .worker-detail-photo-hint {
            margin: 0;
            color: #718174;
            font-size: 0.78rem;
        }

        #workerDetailsModal .worker-detail-section-title {
            margin: 1.25rem 0 0.5rem;
            color: #315c35;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        #workerDetailsModal .worker-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0 1rem;
        }

        #workerDetailsModal .worker-detail-item {
            min-width: 0;
            padding: 0.8rem 0;
            border-bottom: 1px solid #e8efe9;
        }

        #workerDetailsModal .worker-detail-label {
            display: block;
            margin-bottom: 0.2rem;
            color: #718174;
            font-size: 0.74rem;
            font-weight: 600;
        }

        #workerDetailsModal .worker-detail-value {
            display: block;
            color: #263a2b;
            font-size: 0.9rem;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        #workerPhotoLightbox[hidden] {
            display: none !important;
        }

        #workerPhotoLightbox {
            position: fixed;
            z-index: 1090;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4.5rem 1.25rem 2rem;
            background: rgba(15, 28, 18, 0.94);
        }

        #workerPhotoLightbox .worker-photo-lightbox-close {
            position: absolute;
            top: max(1rem, env(safe-area-inset-top));
            right: max(1rem, env(safe-area-inset-right));
            display: inline-flex;
            width: 44px;
            height: 44px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.75);
            border-radius: 50%;
            background: #fff;
            color: #315c35;
            font-size: 1.1rem;
        }

        #workerPhotoLightbox .worker-photo-lightbox-close:hover,
        #workerPhotoLightbox .worker-photo-lightbox-close:focus-visible {
            background: #eaf3eb;
            outline: 2px solid #fff;
            outline-offset: 3px;
        }

        #workerPhotoLightbox .worker-photo-lightbox-content {
            display: flex;
            max-width: 100%;
            max-height: 100%;
            flex-direction: column;
            align-items: center;
            gap: 0.8rem;
        }

        #workerPhotoLightbox .worker-photo-lightbox-image {
            display: block;
            width: auto;
            height: auto;
            max-width: min(100%, 1100px);
            max-height: calc(100dvh - 8rem);
            border: 4px solid rgba(255, 255, 255, 0.9);
            border-radius: 0.75rem;
            background: #f3f7f3;
            object-fit: contain;
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.35);
        }

        #workerPhotoLightbox .worker-photo-lightbox-caption {
            margin: 0;
            color: #fff;
            font-size: 0.9rem;
            font-weight: 700;
            text-align: center;
        }

        #editWorkerModal .modal-dialog {
            width: min(560px, calc(100vw - 1rem));
            max-width: 560px;
            margin: 0.5rem auto;
        }

        #editWorkerModal .modal-content {
            max-height: calc(100vh - 1rem);
            max-height: calc(100dvh - 1rem);
            overflow: hidden;
            border: 1px solid #dfece4;
            border-radius: 16px;
        }

        #editWorkerModal .modal-body {
            min-height: 0;
            overflow-y: auto;
            padding: 1.25rem;
        }

        #editWorkerModal .worker-edit-photo-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid #e3ece4;
            border-radius: 0.75rem;
            background: #f8fbf8;
        }

        #editWorkerModal .worker-edit-avatar-preview {
            display: flex;
            width: 88px;
            height: 88px;
            flex: 0 0 88px;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 3px solid #fff;
            border-radius: 50%;
            background: #e8f0e9;
            box-shadow: 0 0 0 1px #d8e7da;
        }

        #editWorkerModal .worker-edit-avatar-preview .worker-avatar-wrap {
            display: block;
            width: 100%;
            height: 100%;
            flex: 0 0 100%;
        }

        #editWorkerModal .worker-edit-avatar {
            width: 100%;
            height: 100%;
            min-width: 100%;
            max-width: 100%;
            border: 0;
            border-radius: 50%;
            object-fit: contain;
            padding: 4px;
        }

        #editWorkerModal .worker-edit-photo-controls {
            min-width: 0;
            flex: 1;
        }

        #editWorkerModal .modal-footer {
            flex: 0 0 auto;
            border-top: 1px solid #e5eee6;
            background: #f8fbf8;
        }

        @media (max-width: 575.98px) {
            #workerDetailsModal .modal-content {
                border-radius: 12px;
            }

            #workerDetailsModal .modal-body {
                padding: 1rem;
            }

            #workerDetailsModal .worker-detail-hero {
                flex-direction: column;
                gap: 0.8rem;
                padding: 1rem;
                text-align: center;
            }

            #workerDetailsModal .worker-detail-photo-button,
            #workerDetailsModal .worker-detail-photo-static {
                width: 132px;
                height: 132px;
                flex-basis: 132px;
            }

            #workerDetailsModal .worker-detail-name {
                font-size: 1.15rem;
            }

            #workerDetailsModal .worker-detail-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            #workerPhotoLightbox {
                padding: 4rem 0.75rem max(1rem, env(safe-area-inset-bottom));
            }

            #workerPhotoLightbox .worker-photo-lightbox-image {
                max-width: 100%;
                max-height: calc(100dvh - 7rem);
            }
        }
            #editWorkerModal .modal-content {
                border-radius: 12px;
            }

            #editWorkerModal .modal-body {
                padding: 1rem;
            }

            #editWorkerModal .worker-edit-photo-row {
                align-items: flex-start;
                padding: 0.75rem;
                gap: 0.75rem;
            }

            #editWorkerModal .worker-edit-avatar-preview {
                width: 68px;
                height: 68px;
                flex-basis: 68px;
            }
        }

        @media (max-width: 575.98px) {
            .workers-modal-dialog {
                width: calc(100vw - 1rem) !important;
                max-width: none !important;
            }

            #viewWorkersModal .modal-content {
                max-height: calc(100vh - 1rem);
                max-height: calc(100dvh - 1rem);
                border-radius: 12px;
            }

            #viewWorkersModal .workers-modal-header {
                gap: 0.6rem;
                padding: 0.9rem 1rem;
            }

            #viewWorkersModal .workers-modal-icon {
                width: 38px;
                height: 38px;
                flex-basis: 38px;
                border-radius: 10px;
                font-size: 1rem;
            }

            #viewWorkersModal .workers-modal-title {
                font-size: 0.95rem;
            }

            #viewWorkersModal .workers-modal-subtitle {
                font-size: 0.72rem;
            }

            #viewWorkersModal .workers-count-pill {
                padding: 0.3rem 0.5rem;
                font-size: 0.68rem;
            }

            #viewWorkersModal .modal-body {
                max-height: 64vh;
            }

            #viewWorkersModal .workers-roster-table {
                min-width: 0;
                font-size: 0.8rem;
            }

            #viewWorkersModal .workers-date-column {
                display: none;
            }

            #viewWorkersModal .workers-roster-table thead th,
            #viewWorkersModal .workers-roster-table tbody td {
                padding: 0.65rem 0.55rem;
            }

            #viewWorkersModal .worker-list-avatar {
                width: 34px;
                height: 34px;
                min-width: 34px;
                max-width: 34px;
            }

            #viewWorkersModal .worker-list-info {
                gap: 0.45rem;
            }

            #viewWorkersModal .workers-photo-action {
                gap: 0.2rem;
                padding: 0.3rem 0.4rem;
                font-size: 0.7rem;
            }

            #viewWorkersModal .workers-modal-footer {
                padding: 0.7rem 0.85rem;
            }

            #viewWorkersModal .workers-page-btn {
                min-width: 40px;
                padding: 0.35rem 0.55rem;
            }

            #viewWorkersModal .workers-page-btn .workers-page-label {
                display: none;
            }
        }

        #registerWorkerModal .modal-dialog {
            width: min(620px, calc(100vw - 1rem));
            max-width: 620px;
            margin: 0.5rem auto;
        }

        #registerWorkerModal .modal-content {
            max-height: calc(100vh - 1rem);
            max-height: calc(100dvh - 1rem);
            overflow: hidden;
            border: 0;
            border-radius: 12px;
        }

        #registerWorkerModal .modal-body {
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding: 1rem 1.25rem;
        }

        #registerWorkerModal .modal-header,
        #registerWorkerModal .modal-footer {
            flex: 0 0 auto;
            padding: 0.9rem 1.25rem;
        }

        #registerWorkerModal .enrollment-modal-header {
            align-items: center;
            border-bottom: 1px solid #e5eee6 !important;
            background: linear-gradient(115deg, #f1f7f1 0%, #ffffff 76%);
        }

        #registerWorkerModal .enrollment-heading {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        #registerWorkerModal .enrollment-modal-icon {
            display: inline-flex;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #315c35;
            color: #fff;
            font-size: 1.05rem;
        }

        #registerWorkerModal .enrollment-modal-title {
            margin: 0;
            color: #203d26;
            font-size: 1.05rem;
            font-weight: 800;
        }

        #registerWorkerModal .enrollment-modal-subtitle {
            margin: 0.2rem 0 0;
            color: #718174;
            font-size: 0.78rem;
        }

        #registerWorkerModal .enrollment-section-heading {
            margin: 0 0 0.75rem;
            color: #315c35;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        #registerWorkerModal .enrollment-section-help {
            margin: -0.35rem 0 0.8rem;
            color: #718174;
            font-size: 0.78rem;
        }

        #registerWorkerModal .enrollment-schedule-panel,
        #registerWorkerModal .enrollment-biometric-panel {
            padding: 0.9rem;
            border: 1px solid #e3ece4;
            border-radius: 0.75rem;
            background: #f8fbf8;
        }

        #registerWorkerModal .enrollment-biometric-panel {
            display: flex;
            align-items: center;
            gap: 0.9rem;
        }

        #registerWorkerModal .enrollment-biometric-icon {
            display: inline-flex;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #e6f1e7;
            color: #315c35;
            font-size: 1.15rem;
        }

        #registerWorkerModal .enrollment-biometric-copy {
            min-width: 0;
            flex: 1;
        }

        #registerWorkerModal .enrollment-biometric-title {
            margin: 0;
            color: #263a2b;
            font-size: 0.88rem;
            font-weight: 800;
        }

        #registerWorkerModal .enrollment-biometric-help {
            margin: 0.15rem 0 0;
            color: #718174;
            font-size: 0.75rem;
        }

        #registerWorkerModal .modal-footer {
            border-top: 1px solid #e5eee6;
            background: #f8fbf8;
        }

        #registerWorkerModal .worker-photo-preview {
            display: block !important;
            width: 72px !important;
            height: 72px !important;
            min-width: 72px;
            max-width: 72px !important;
            margin-top: 0.5rem;
            border: 2px solid #eef5ef;
            border-radius: 50%;
            object-fit: cover;
        }

        #registerWorkerModal #regProfileImagePreview[hidden] {
            display: none !important;
        }

        .manual-worker-picker {
            position: relative;
        }

        #manualWorkerPickerToggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 44px;
            text-align: left;
        }

        #manualWorkerPickerToggle:disabled {
            background-color: #f8f9fa;
        }

        #manualWorkerPickerOptions {
            position: absolute;
            z-index: 5;
            top: calc(100% + 4px);
            right: 0;
            left: 0;
            max-height: 240px;
            overflow-y: auto;
            padding: 0.25rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            background: #fff;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.12);
        }

        .manual-worker-option {
            display: flex;
            width: 100%;
            align-items: center;
            gap: 0.75rem;
            padding: 0.55rem 0.65rem;
            border: 0;
            border-radius: 0.35rem;
            background: #fff;
            color: #212529;
            text-align: left;
        }

        .manual-worker-option:hover,
        .manual-worker-option:focus,
        .manual-worker-option[aria-selected="true"] {
            background: #e9f7ef;
            outline: none;
        }

        .manual-worker-option .worker-avatar-img,
        .manual-worker-option .worker-avatar-fallback,
        #manualWorkerPickerToggle .worker-avatar-img,
        #manualWorkerPickerToggle .worker-avatar-fallback {
            width: 36px;
            height: 36px;
            min-width: 36px;
            max-width: 36px;
            border-radius: 50%;
            object-fit: cover;
        }

        .btn-attendance-primary,
        .btn-attendance-primary:hover,
        .btn-attendance-primary:focus {
            border-color: #315c35;
            background-color: #315c35;
            color: #fff;
        }

        .btn-attendance-primary:hover,
        .btn-attendance-primary:focus {
            border-color: #315c35;
            background-color: #315c35;
        }

        .btn-attendance-primary:disabled {
            border-color: #315c35;
            background-color: #315c35;
            color: #fff;
            opacity: 0.65;
        }

        .swal2-container {
            z-index: 2000 !important;
        }

        @media (max-width: 575.98px) {
            #registerWorkerModal .modal-dialog {
                width: calc(100vw - 1rem);
                max-width: none;
            }

            #registerWorkerModal .modal-content {
                max-height: calc(100vh - 1rem);
                max-height: calc(100dvh - 1rem);
            }

            #registerWorkerModal .modal-body {
                padding: 0.85rem 1rem;
            }

            #registerWorkerModal .modal-header,
            #registerWorkerModal .modal-footer {
                padding-right: 1rem;
                padding-left: 1rem;
            }

            #registerWorkerModal .enrollment-biometric-panel {
                align-items: flex-start;
                flex-wrap: wrap;
                gap: 0.65rem;
            }

            #registerWorkerModal #btnRegisterWorkerFingerprint {
                width: 100%;
            }
        }

        .scan-pulse-container {
            border: 2px dashed #dee2e6;
            border-radius: 16px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .scan-pulse-container:hover {
            border-color: #315c35;
            background-color: #f1f8f2;
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
            box-shadow: 0 6px 20px rgba(49, 92, 53, 0.28);
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

        .status-log-stack {
            display: flex;
            width: 100%;
            min-width: 150px;
            flex-direction: column;
            align-items: center;
            gap: 0.65rem;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 92px;
            padding: 0.42rem 0.85rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.01em;
            line-height: 1;
            border: 1px solid transparent;
        }

        .status-pill-present {
            background: #dcfce7;
            color: #166534;
            border-color: #bbf7d0;
        }

        .status-pill-late {
            background: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
        }

        .status-pill-absent {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .status-pill-default {
            background: #f1f5f9;
            color: #475569;
            border-color: #e2e8f0;
        }

        .status-action-grid {
            display: grid;
            width: 100%;
            max-width: 220px;
            gap: 0.5rem;
        }

        .status-action-btn {
            width: 100%;
            min-height: 38px;
            border-radius: 12px !important;
            font-size: 0.84rem;
            font-weight: 800;
            line-height: 1.1;
            padding: 0.55rem 0.75rem !important;
            box-shadow: none !important;
        }

        .status-action-btn i {
            font-size: 0.92rem;
        }

        .status-action-break-out {
            color: #1d4ed8 !important;
            border-color: #bfdbfe !important;
            background: #eff6ff !important;
        }

        .status-action-break-out:hover {
            color: #ffffff !important;
            border-color: #1d4ed8 !important;
            background: #1d4ed8 !important;
        }

        .status-action-break-in {
            color: #b45309 !important;
            border-color: #fde68a !important;
            background: #fffbeb !important;
        }

        .status-action-break-in:hover {
            color: #ffffff !important;
            border-color: #b45309 !important;
            background: #b45309 !important;
        }

        .status-action-time-out {
            color: #111827 !important;
            border-color: #d1d5db !important;
            background: #f9fafb !important;
        }

        .status-action-time-out:hover {
            color: #ffffff !important;
            border-color: #111827 !important;
            background: #111827 !important;
        }

        .status-action-disabled {
            cursor: not-allowed !important;
            color: #94a3b8 !important;
            border-color: #e2e8f0 !important;
            background: #f8fafc !important;
            opacity: 1 !important;
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

            #attendanceLogTableBody td:last-child .status-log-stack {
                width: 100%;
                align-items: stretch !important;
                gap: 0.75rem;
            }

            #attendanceLogTableBody td:last-child .status-pill {
                width: fit-content;
                min-width: 110px;
            }

            #attendanceLogTableBody td:last-child .status-action-grid {
                width: 100%;
                max-width: none;
                grid-template-columns: 1fr;
                gap: 0.55rem;
            }

            #attendanceLogTableBody td:last-child .status-action-btn {
                width: 100%;
                min-height: 44px;
                font-size: 0.92rem;
                justify-content: center;
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
    
        /* ======================================================================
           SUPERVISOR ATTENDANCE MOBILE POLISH
           Better action placement, tighter cards, and cleaner scan/log panels.
           ====================================================================== */
        .attendance-control-card,
        .attendance-biometric-card,
        .attendance-log-card {
            border: 1px solid #e4ece6 !important;
            border-radius: 22px !important;
            background: #ffffff !important;
            box-shadow: 0 14px 34px rgba(15, 32, 21, 0.06) !important;
            overflow: hidden !important;
        }

        .attendance-page,
        .attendance-main-grid,
        .attendance-main-grid > [class*="col-"],
        .attendance-control-layout > * {
            min-width: 0 !important;
        }

        .attendance-control-card .card-body,
        .attendance-biometric-card .card-body,
        .attendance-log-card .card-body {
            padding: 1.25rem !important;
        }

        .attendance-control-layout {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) minmax(360px, 0.9fr) !important;
            align-items: stretch !important;
            gap: 1rem !important;
        }

        .attendance-control-copy {
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            min-width: 0 !important;
        }

        .attendance-control-title {
            display: flex !important;
            align-items: center !important;
            gap: 0.65rem !important;
            margin: 0 0 0.45rem !important;
            color: #101827 !important;
            font-size: 1.35rem !important;
            font-weight: 800 !important;
            line-height: 1.2 !important;
        }

        .attendance-control-title-icon,
        .attendance-section-icon {
            width: 34px !important;
            height: 34px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 12px !important;
            background: #eff8f0 !important;
            color: #315c35 !important;
            flex: 0 0 auto !important;
        }

        .attendance-control-subtitle {
            max-width: 570px !important;
            margin: 0 !important;
            color: #64748b !important;
            font-size: 0.92rem !important;
            line-height: 1.55 !important;
        }

        .attendance-action-panel {
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 0.75rem !important;
            min-width: 0 !important;
        }

        .attendance-date-field {
            padding: 0.8rem !important;
            border: 1px solid #e4ece6 !important;
            border-radius: 16px !important;
            background: linear-gradient(135deg, #f8fff9 0%, #ffffff 100%) !important;
        }

        .attendance-date-label {
            display: flex !important;
            align-items: center !important;
            gap: 0.45rem !important;
            margin-bottom: 0.45rem !important;
            color: #4f6258 !important;
            font-size: 0.76rem !important;
            font-weight: 900 !important;
            letter-spacing: 0.065em !important;
            text-transform: uppercase !important;
        }

        .attendance-date-field .form-control {
            min-height: 42px !important;
            border-radius: 13px !important;
            border-color: #dce7df !important;
            font-size: 16px !important;
        }

        .attendance-action-grid {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: 0.65rem !important;
        }

        .attendance-quick-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.45rem !important;
            min-height: 46px !important;
            padding: 0.65rem 0.75rem !important;
            border-radius: 14px !important;
            font-size: 0.86rem !important;
            font-weight: 800 !important;
            line-height: 1.2 !important;
            white-space: normal !important;
            border: 1px solid #dfece4 !important;
            background: #ffffff !important;
            color: #1f3529 !important;
            box-shadow: 0 7px 16px rgba(15, 32, 21, 0.04) !important;
        }

        .attendance-btn-workers {
            background: #f7fbf8 !important;
        }

        .attendance-btn-manual {
            border-color: #f7d9a8 !important;
            background: #fffaf0 !important;
            color: #995c0a !important;
        }

        .attendance-btn-register {
            border-color: #315c35 !important;
            background: #315c35 !important;
            color: #ffffff !important;
        }

        .attendance-main-grid {
            align-items: stretch !important;
        }

        .attendance-section-heading {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 0.75rem !important;
            margin-bottom: 1rem !important;
        }

        .attendance-section-heading h5 {
            display: flex !important;
            align-items: center !important;
            gap: 0.6rem !important;
            margin: 0 !important;
            color: #101827 !important;
            font-size: 1.15rem !important;
            font-weight: 800 !important;
            line-height: 1.25 !important;
        }

        .attendance-log-count {
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.35rem !important;
            padding: 0.5rem 0.75rem !important;
            border-radius: 999px !important;
            background: #eef5ef !important;
            color: #315c35 !important;
            font-weight: 900 !important;
            line-height: 1 !important;
        }

        .scan-pulse-container {
            border-color: #dfe7e1 !important;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%) !important;
            box-shadow: inset 0 0 0 1px rgba(22, 101, 52, 0.02) !important;
        }

        .fingerprint-trigger-btn {
            background: linear-gradient(135deg, #4b6b46 0%, #315c35 100%) !important;
            border-color: transparent !important;
        }

        #globalScanStatus {
            border-radius: 13px !important;
        }

        @media (max-width: 992px) {
            .attendance-control-layout {
                grid-template-columns: 1fr !important;
            }

            .attendance-action-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }

            .attendance-main-grid {
                gap: 1rem !important;
            }
        }

        @media (min-width: 993px) and (max-width: 1199px) {
            .attendance-control-layout {
                grid-template-columns: minmax(0, 1.1fr) minmax(300px, 0.9fr) !important;
            }

            .attendance-action-grid {
                grid-template-columns: 1fr !important;
            }

            .attendance-quick-btn {
                min-height: 42px !important;
                font-size: 0.78rem !important;
            }

            .attendance-main-grid {
                display: grid !important;
                grid-template-columns: minmax(0, 1fr) !important;
                gap: 1rem !important;
            }

            .attendance-main-grid > [class*="col-"] {
                width: 100% !important;
                max-width: none !important;
            }

            .attendance-log-card .table-responsive {
                max-width: 100%;
                overflow-x: auto;
            }
        }

        @media (max-width: 576px) {
            .attendance-page {
                padding: 0 !important;
            }

            .attendance-control-card,
            .attendance-biometric-card,
            .attendance-log-card {
                border-radius: 18px !important;
                box-shadow: 0 10px 24px rgba(15, 32, 21, 0.05) !important;
            }

            .attendance-control-card .card-body,
            .attendance-biometric-card .card-body,
            .attendance-log-card .card-body {
                padding: 1rem !important;
            }

            .attendance-control-title {
                font-size: 1.12rem !important;
            }

            .attendance-control-title-icon {
                width: 32px !important;
                height: 32px !important;
            }

            .attendance-control-subtitle {
                font-size: 0.83rem !important;
                line-height: 1.5 !important;
            }

            .attendance-action-grid {
                grid-template-columns: 1fr 1fr !important;
                gap: 0.55rem !important;
            }

            .attendance-btn-register {
                grid-column: 1 / -1 !important;
            }

            .attendance-quick-btn {
                min-height: 44px !important;
                padding: 0.6rem 0.55rem !important;
                font-size: 0.78rem !important;
            }

            .attendance-date-field {
                padding: 0.7rem !important;
            }

            .attendance-section-heading {
                align-items: flex-start !important;
                flex-direction: column !important;
                margin-bottom: 0.9rem !important;
            }

            .attendance-section-heading.justify-content-center {
                align-items: center !important;
                text-align: center !important;
            }

            .attendance-section-heading h5 {
                font-size: 1.05rem !important;
            }

            .attendance-log-count {
                width: 100% !important;
                justify-content: center !important;
            }

            .scan-pulse-container {
                padding: 1.5rem 1rem !important;
                border-radius: 18px !important;
            }

            .fingerprint-trigger-btn {
                width: 94px !important;
                height: 94px !important;
                min-width: 94px !important;
                min-height: 94px !important;
            }

            #emptyRowPlaceholder td {
                padding: 2.25rem 1rem !important;
            }

            #attendanceLogTableBody tr {
                border-radius: 18px !important;
                padding: 1rem !important;
                box-shadow: 0 10px 22px rgba(15, 32, 21, 0.045) !important;
            }

            .status-log-stack {
                align-items: stretch !important;
            }

            .status-action-grid,
            #attendanceLogTableBody td:last-child .d-flex {
                width: 100% !important;
            }

            #attendanceLogTableBody td:last-child .d-flex {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 0.55rem !important;
            }
        }

        .attendance-worker-info {
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.75rem !important;
            flex-wrap: nowrap !important;
        }

        .attendance-worker-avatar {
            width: 56px !important;
            height: 56px !important;
            min-width: 56px !important;
            max-width: 56px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            flex-shrink: 0 !important;
        }

        @media (max-width: 576px) {
            .attendance-worker-avatar {
                width: 40px !important;
                height: 40px !important;
                min-width: 40px !important;
                max-width: 40px !important;
            }
        }
    </style>
@endpush

@section('content')
<div class="container-fluid p-0 attendance-page">
    <!-- Top Action Header -->
    <div class="card attendance-control-card border-0 mb-4">
        <div class="card-body">
            <div class="attendance-control-layout">
                <div class="attendance-control-copy">
                    <h4 class="attendance-control-title">
                        <span class="attendance-control-title-icon"><i class="bi bi-calendar2-check-fill"></i></span>
                        Daily Workforce Attendance
                    </h4>
                    <p class="attendance-control-subtitle">
                        Scan a worker’s fingerprint to identify them automatically. Use manual attendance only when the biometric reader is unavailable.
                    </p>
                </div>

                <div class="attendance-action-panel">
                    <div class="attendance-date-field">
                        <label class="attendance-date-label" for="attendanceDateInput">
                            <i class="bi bi-calendar-event"></i> Attendance Date
                        </label>
                        <input id="attendanceDateInput" type="date" name="attendance_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="attendance-action-grid">
                        <button type="button" class="btn attendance-quick-btn attendance-btn-workers"
                                data-bs-toggle="modal" data-bs-target="#viewWorkersModal">
                            <i class="bi bi-people-fill"></i> Enrolled Workers
                        </button>

                        <button type="button" class="btn attendance-quick-btn attendance-btn-manual"
                                data-bs-toggle="modal" data-bs-target="#manualAttendanceModal">
                            <i class="bi bi-pencil-square"></i> Manual Log
                        </button>

                        <button type="button" class="btn attendance-quick-btn attendance-btn-register"
                                data-bs-toggle="modal" data-bs-target="#registerWorkerModal">
                            <i class="bi bi-person-plus-fill"></i> Register Worker
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 attendance-main-grid">
        <!-- Center Scanning Interface Platform -->
        <div class="col-12 col-lg-4">
            <div class="card attendance-biometric-card border-0 h-100">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <div class="attendance-section-heading justify-content-center">
                        <h5>
                            <span class="attendance-section-icon"><i class="bi bi-fingerprint"></i></span>
                            Biometric Identification
                        </h5>
                    </div>

                    <div class="scan-pulse-container p-5 mb-3">
                        <button type="button" id="btnGlobalScan" class="btn btn-primary fingerprint-trigger-btn mb-3">
                            <i class="bi bi-fingerprint"></i>
                        </button>

                        <p class="fw-semibold text-dark mb-1">Ready to Identify</p>
                        <span class="text-muted small">Tap the fingerprint button to scan using Face ID, Touch ID, or fingerprint reader.</span>
                    </div>

                    <div id="globalScanStatus" class="alert alert-light border text-muted small py-2 mb-0">
                        <i class="bi bi-info-circle-fill text-primary"></i> Tap the fingerprint button to start biometric verification.
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Attendance Session Log -->
        <div class="col-12 col-lg-8">
            <form action="{{ route('supervisor.attendance.save') }}" method="POST" id="attendanceMainForm">
                @csrf

                <div class="card attendance-log-card border-0 h-100">
                    <div class="card-body">
                        <div class="attendance-section-heading">
                            <h5>
                                <span class="attendance-section-icon"><i class="bi bi-list-check"></i></span>
                                Scanned Personnel Log
                            </h5>
                            <span class="badge attendance-log-count" id="scannedCount">
                                <i class="bi bi-activity"></i> 0 Active Logs
                            </span>
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
                                        <th class="pb-3 border-0 text-center">OT</th>
                                        <th class="pb-3 border-0 text-center">Status Log</th>
                                    </tr>
                                </thead>

                                <tbody id="attendanceLogTableBody">
                                    <tr id="emptyRowPlaceholder">
                                        <td colspan="8" class="text-center py-5 text-muted fst-italic">
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
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header enrollment-modal-header">
                <div class="enrollment-heading">
                    <span class="enrollment-modal-icon" aria-hidden="true"><i class="bi bi-person-badge"></i></span>
                    <div>
                        <h5 class="enrollment-modal-title" id="registerWorkerModalLabel">Fast Worker Enrollment</h5>
                        <p class="enrollment-modal-subtitle">Add worker details, schedule, photo, and biometric profile.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="fastWorkerForm">
                    <section class="mb-4" aria-labelledby="enrollmentDetailsHeading">
                        <h6 class="enrollment-section-heading" id="enrollmentDetailsHeading">Worker Details</h6>
                        <div class="row g-3">
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
                        <p class="enrollment-section-help">Optional time overrides for this worker.</p>
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="manualTimeInInput" class="form-label small fw-semibold">Time in</label>
                                <input type="time" id="manualTimeInInput" class="form-control" step="1">
                            </div>
                            <div class="col-6">
                                <label for="manualTimeOutInput" class="form-label small fw-semibold">Time out</label>
                                <input type="time" id="manualTimeOutInput" class="form-control" step="1">
                            </div>
                            <div class="col-6">
                                <label for="manualBreakOutInput" class="form-label small fw-semibold">Break out</label>
                                <input type="time" id="manualBreakOutInput" class="form-control" step="1">
                            </div>
                            <div class="col-6">
                                <label for="manualBreakInInput" class="form-label small fw-semibold">Break in</label>
                                <input type="time" id="manualBreakInInput" class="form-control" step="1">
                            </div>
                        </div>
                    </section>

                    <section class="mb-4" aria-labelledby="enrollmentPhotoHeading">
                        <label class="form-label small fw-semibold" for="regProfileImage" id="enrollmentPhotoHeading">Profile photo</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="file" id="regProfileImage" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <img id="regProfileImagePreview" class="worker-photo-preview mt-0 flex-shrink-0" alt="Selected worker profile photo" hidden>
                        </div>
                        <div class="form-text">Optional. JPG, PNG, or WebP, up to 2 MB.</div>
                    </section>

                    <section class="enrollment-biometric-panel" aria-labelledby="enrollmentBiometricHeading">
                        <span class="enrollment-biometric-icon" aria-hidden="true"><i class="bi bi-fingerprint"></i></span>
                        <div class="enrollment-biometric-copy">
                            <h6 class="enrollment-biometric-title" id="enrollmentBiometricHeading">Biometric authentication <span class="text-danger">*</span></h6>
                            <p class="enrollment-biometric-help">Capture a fingerprint or device passkey to complete enrollment.</p>
                            <span class="d-block text-muted small mt-2" id="registerFingerprintLabel">Biometrics not captured yet.</span>
                        </div>
                        <button type="button" class="btn btn-attendance-primary btn-sm fw-semibold text-nowrap" id="btnRegisterWorkerFingerprint">
                            <i class="bi bi-shield-plus me-1"></i> Initialize fingerprint
                        </button>
                    </section>
                </form>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light fw-medium btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-attendance-primary fw-semibold btn-sm" id="btnSaveWorkerRecord" disabled>
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
            <div class="modal-header workers-modal-header">
                <div class="workers-modal-heading">
                    <span class="workers-modal-icon" aria-hidden="true"><i class="bi bi-people-fill"></i></span>
                    <div>
                        <h5 class="workers-modal-title">All Enrolled Workers</h5>
                        <p class="workers-modal-subtitle">Manage worker profiles and photos</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span id="workersCountLabel" class="workers-count-pill">Loading...</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 workers-roster-table">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Trade</th>
                                <th class="workers-date-column">Enrolled</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>

                        <tbody id="allWorkersTableBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer workers-modal-footer">
                <span id="pageInfo" class="workers-page-status">Page 1</span>
                <div class="d-flex align-items-center gap-2">
                    <button id="prevPage" type="button" class="btn btn-sm workers-page-btn" aria-label="Previous page" disabled>
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>
                        <span class="workers-page-label">Previous</span>
                    </button>

                    <button id="nextPage" type="button" class="btn btn-sm workers-page-btn" aria-label="Next page">
                        <span class="workers-page-label">Next</span>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- EDIT WORKER MODAL -->
<div class="modal fade" id="editWorkerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg">
            <div class="modal-header workers-modal-header">
                <div class="workers-modal-heading">
                    <span class="workers-modal-icon" aria-hidden="true"><i class="bi bi-person-gear"></i></span>
                    <div>
                        <h5 class="workers-modal-title">Edit Worker</h5>
                        <p class="workers-modal-subtitle">Update worker details and profile photo</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="editWorkerForm" novalidate>
                <input type="hidden" id="editWorkerId" name="worker_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="editWorkerFirstName" class="form-label small fw-semibold">First name</label>
                            <input type="text" id="editWorkerFirstName" name="first_name" class="form-control" maxlength="100" autocomplete="given-name" required>
                        </div>
                        <div class="col-sm-6">
                            <label for="editWorkerLastName" class="form-label small fw-semibold">Last name</label>
                            <input type="text" id="editWorkerLastName" name="last_name" class="form-control" maxlength="100" autocomplete="family-name" required>
                        </div>
                        <div class="col-sm-6">
                            <label for="editWorkerTrade" class="form-label small fw-semibold">Trade / specialty</label>
                            <input type="text" id="editWorkerTrade" name="trade" class="form-control" maxlength="100" placeholder="e.g. Carpenter">
                        </div>
                        <div class="col-sm-6">
                            <label for="editWorkerContact" class="form-label small fw-semibold">Contact number</label>
                            <input type="tel" id="editWorkerContact" name="contact_number" class="form-control" maxlength="20" autocomplete="tel" placeholder="Optional">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold" for="editWorkerProfileImage">Profile photo</label>
                            <div class="worker-edit-photo-row">
                                <div id="editWorkerAvatarPreview" class="worker-edit-avatar-preview" aria-live="polite">
                                    <span class="worker-avatar-fallback worker-edit-avatar">W</span>
                                </div>
                                <div class="worker-edit-photo-controls">
                                    <input type="file" id="editWorkerProfileImage" name="profile_image" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                                    <div class="form-text">JPG, PNG, or WebP, up to 2 MB. Leave empty to keep the current photo.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light fw-medium btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-attendance-primary fw-semibold btn-sm" id="btnSaveEditedWorker">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- WORKER DETAILS MODAL -->
<div class="modal fade" id="workerDetailsModal" tabindex="-1" aria-labelledby="workerDetailsTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg">
            <div class="modal-header workers-modal-header">
                <div class="workers-modal-heading">
                    <span class="workers-modal-icon" aria-hidden="true"><i class="bi bi-person-vcard"></i></span>
                    <div>
                        <h5 class="workers-modal-title" id="workerDetailsTitle">Worker Details</h5>
                        <p class="workers-modal-subtitle">Profile and enrollment information</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close worker details"></button>
            </div>

            <div class="modal-body">
                <section class="worker-detail-hero" aria-labelledby="workerDetailName">
                    <div id="workerDetailsPhotoContainer" aria-live="polite"></div>
                    <div class="worker-detail-copy">
                        <h2 id="workerDetailName" class="worker-detail-name"></h2>
                        <span id="workerDetailTrade" class="worker-detail-trade"></span>
                        <p id="workerDetailPhotoHint" class="worker-detail-photo-hint" hidden>
                            <i class="bi bi-arrows-fullscreen me-1" aria-hidden="true"></i>Click photo to enlarge
                        </p>
                    </div>
                </section>

                <h6 class="worker-detail-section-title">Worker Information</h6>
                <div class="worker-detail-grid">
                    <div class="worker-detail-item">
                        <span class="worker-detail-label">Worker ID</span>
                        <span id="workerDetailId" class="worker-detail-value"></span>
                    </div>
                    <div class="worker-detail-item">
                        <span class="worker-detail-label">Contact number</span>
                        <span id="workerDetailContact" class="worker-detail-value"></span>
                    </div>
                    <div class="worker-detail-item">
                        <span class="worker-detail-label">Date enrolled</span>
                        <span id="workerDetailEnrolled" class="worker-detail-value"></span>
                    </div>
                </div>
            </div>

            <div class="modal-footer workers-modal-footer">
                <button type="button" class="btn workers-page-btn" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Back to workers
                </button>
            </div>
        </div>
    </div>
</div>

<div id="workerPhotoLightbox" role="dialog" aria-modal="true" aria-label="Worker profile photo viewer" hidden>
    <button type="button" id="workerPhotoLightboxClose" class="worker-photo-lightbox-close" aria-label="Back to worker details" title="Back to worker details">
        <i class="bi bi-x-lg" aria-hidden="true"></i>
    </button>
    <div class="worker-photo-lightbox-content">
        <img id="workerPhotoLightboxImage" class="worker-photo-lightbox-image" alt="">
        <p id="workerPhotoLightboxCaption" class="worker-photo-lightbox-caption"></p>
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
                    <div class="manual-worker-picker" id="manualWorkerPicker">
                        <input type="hidden" id="manualWorkerSelect" value="">
                        <button type="button" class="form-control" id="manualWorkerPickerToggle" aria-haspopup="listbox" aria-expanded="false" aria-controls="manualWorkerPickerOptions" disabled>
                            <span id="manualWorkerPickerValue">Loading workers...</span>
                            <i class="bi bi-chevron-down ms-2" aria-hidden="true"></i>
                        </button>
                        <div id="manualWorkerPickerOptions" role="listbox" aria-label="Enrolled workers" hidden></div>
                    </div>
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

                <button type="button" class="btn btn-attendance-primary fw-semibold btn-sm" id="btnSaveManualAttendance" disabled>
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
        const supervisorRoutes = {
            attendanceToday: @json(route('supervisor.attendance.today')),
            attendanceLogWorker: @json(route('supervisor.attendance.logWorker')),
            workersList: @json(route('supervisor.workers.list')),
            workerRegister: @json(route('supervisor.workers.register_biometric')),
            workerProfileBase: @json(url('/supervisor/workers')),
            passkeyLoginOptions: @json(route('passkeys.login.options')),
            passkeyLogin: @json(route('passkeys.login')),
            passkeyRegisterOptions: @json(route('passkeys.register.options'))
        };

        const btnGlobalScan = document.getElementById('btnGlobalScan');
        const globalScanStatus = document.getElementById('globalScanStatus');
        const attendanceLogTableBody = document.getElementById('attendanceLogTableBody');
        const formSubmitContainer = document.getElementById('formSubmitContainer');
        const scannedCountBadge = document.getElementById('scannedCount');
        const viewWorkersModal = document.getElementById('viewWorkersModal');
        const workerDetailsModalElement = document.getElementById('workerDetailsModal');
        const workerDetailsPhotoContainer = document.getElementById('workerDetailsPhotoContainer');
        const workerDetailPhotoHint = document.getElementById('workerDetailPhotoHint');
        const workerPhotoLightbox = document.getElementById('workerPhotoLightbox');
        const workerPhotoLightboxImage = document.getElementById('workerPhotoLightboxImage');
        const workerPhotoLightboxCaption = document.getElementById('workerPhotoLightboxCaption');
        const workerPhotoLightboxClose = document.getElementById('workerPhotoLightboxClose');
        const editWorkerModalElement = document.getElementById('editWorkerModal');
        const editWorkerForm = document.getElementById('editWorkerForm');
        const editWorkerIdInput = document.getElementById('editWorkerId');
        const editWorkerFirstNameInput = document.getElementById('editWorkerFirstName');
        const editWorkerLastNameInput = document.getElementById('editWorkerLastName');
        const editWorkerTradeInput = document.getElementById('editWorkerTrade');
        const editWorkerContactInput = document.getElementById('editWorkerContact');
        const editWorkerProfileImageInput = document.getElementById('editWorkerProfileImage');
        const editWorkerAvatarPreview = document.getElementById('editWorkerAvatarPreview');
        const btnSaveEditedWorker = document.getElementById('btnSaveEditedWorker');
        const manualAttendanceModal = document.getElementById('manualAttendanceModal');
        const manualWorkerSelect = document.getElementById('manualWorkerSelect');
        const manualWorkerPicker = document.getElementById('manualWorkerPicker');
        const manualWorkerPickerToggle = document.getElementById('manualWorkerPickerToggle');
        const manualWorkerPickerValue = document.getElementById('manualWorkerPickerValue');
        const manualWorkerPickerOptions = document.getElementById('manualWorkerPickerOptions');
        const manualActionSelect = document.getElementById('manualActionSelect');
        const manualTimeInInput = document.getElementById('manualTimeInInput');
        const manualTimeOutInput = document.getElementById('manualTimeOutInput');
        const manualBreakOutInput = document.getElementById('manualBreakOutInput');
        const manualBreakInInput = document.getElementById('manualBreakInInput');
        const manualReasonInput = document.getElementById('manualReasonInput');
        const manualAttendanceStatus = document.getElementById('manualAttendanceStatus');
        const btnSaveManualAttendance = document.getElementById('btnSaveManualAttendance');

        const btnRegisterFingerprint = document.getElementById('btnRegisterWorkerFingerprint');
        const btnSaveWorker = document.getElementById('btnSaveWorkerRecord');
        const regStatusLabel = document.getElementById('registerFingerprintLabel');

        const prevPageBtn = document.getElementById('prevPage');
        const nextPageBtn = document.getElementById('nextPage');
        const attendanceDateInput = document.querySelector('input[name="attendance_date"]');
        const regProfileImageInput = document.getElementById('regProfileImage');
        const regProfileImagePreview = document.getElementById('regProfileImagePreview');
        const regContactNumberInput = document.getElementById('regContactNumber');

        let capturedPasskeyCredential = null;
        let currentPage = 1;
        let scannedWorkerIds = new Set();
        let manualWorkersCache = [];
        let rosterWorkers = [];
        let returnToRosterAfterEdit = false;
        let returnToRosterAfterDetails = false;
        let photoLightboxReturnFocus = null;

        // ========================================================================
        // WEBAUTHN BROWSER SUPPORT CHECK
        // ========================================================================
        const isWebAuthnSupported = () => {
            return (
                typeof window !== 'undefined' &&
                typeof window.PublicKeyCredential !== 'undefined' &&
                typeof window.PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable === 'function'
            );
        };

        const webAuthnAvailable = isWebAuthnSupported();

        // Initialize biometric button state based on WebAuthn support
        if (btnGlobalScan) {
            if (!webAuthnAvailable) {
                btnGlobalScan.disabled = true;
                btnGlobalScan.style.opacity = '0.5';
                btnGlobalScan.style.cursor = 'not-allowed';
                
                globalScanStatus.className = 'alert alert-danger border text-danger small py-2 mb-0';
                globalScanStatus.innerHTML = `
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    WebAuthn is not supported in this browser. Please use the 
                    <strong>Manual Log</strong> option or try a different browser (Chrome, Firefox, Safari).
                `;
            }
        }

        if (btnRegisterFingerprint && !webAuthnAvailable) {
            btnRegisterFingerprint.disabled = true;
            btnRegisterFingerprint.style.opacity = '0.5';
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function showAttendanceAlert(icon, title, text) {
            if (window.Swal?.fire) {
                return Swal.fire({
                    icon,
                    title,
                    text,
                    confirmButtonColor: '#315c35'
                });
            }

            window.alert(text || title);
            return Promise.resolve();
        }

        function showAttendanceSuccessToast(title, text) {
            if (!window.Swal?.fire) {
                return Promise.resolve();
            }

            return Swal.fire({
                toast: true,
                icon: 'success',
                title,
                text,
                position: 'top-end',
                showConfirmButton: false,
                allowOutsideClick: true,
                timer: 5000,
                timerProgressBar: true
            });
        }

        function isValidContactNumber(value) {
            return !value || (value.length <= 20 && /^[0-9+().\-\s]+$/.test(value));
        }

        function workerAvatarMarkup(photoUrl, name, sizeClass = '') {
            const initials = String(name || 'W').replace(/\s+/g, '').slice(0, 2).toUpperCase() || 'W';
            const image = photoUrl
                ? `<img class="worker-avatar-img ${sizeClass}" data-worker-avatar-image src="${escapeHtml(photoUrl)}" alt="${escapeHtml(name || 'Worker')} profile photo">`
                : '';
            const fallback = `<span class="worker-avatar-fallback ${sizeClass}" ${photoUrl ? 'hidden' : ''}>${escapeHtml(initials)}</span>`;

            return `<span class="worker-avatar-wrap">${image}${fallback}</span>`;
        }

        function bindWorkerAvatarFallbacks(container) {
            container?.querySelectorAll('[data-worker-avatar-image]').forEach(image => {
                const showFallback = () => {
                    image.hidden = true;
                    if (image.nextElementSibling) {
                        image.nextElementSibling.hidden = false;
                    }
                };

                image.addEventListener('error', showFallback, { once: true });

                if (image.complete && image.naturalWidth === 0) {
                    showFallback();
                }
            });
        }

        async function uploadWorkerProfileImage(workerId, file) {
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
                throw new Error('Choose a JPG, PNG, or WebP image no larger than 2 MB.');
            }

            const formData = new FormData();
            formData.append('profile_image', file);

            const response = await fetch(`${supervisorRoutes.workerProfileBase}/${encodeURIComponent(workerId)}/profile-image`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });
            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(result.message || 'Unable to upload worker photo.');
            }

            return result;
        }

        regProfileImageInput?.addEventListener('change', function () {
            const file = regProfileImageInput.files?.[0];

            if (!file) {
                regProfileImagePreview.hidden = true;
                regProfileImagePreview.removeAttribute('src');
                return;
            }

            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
                showAttendanceAlert('warning', 'Invalid profile photo', 'Choose a JPG, PNG, or WebP image no larger than 2 MB.');
                regProfileImageInput.value = '';
                regProfileImagePreview.hidden = true;
                regProfileImagePreview.removeAttribute('src');
                return;
            }

            const reader = new FileReader();
            reader.onload = () => {
                regProfileImagePreview.src = reader.result;
                regProfileImagePreview.hidden = false;
            };
            reader.readAsDataURL(file);
        });

        function normalizeWorker(rawWorker, fallback = {}) {
            const worker = rawWorker || {};

            return {
                worker_id: worker.worker_id || worker.id || fallback.worker_id || fallback.id || null,
                first_name: worker.first_name || fallback.first_name || '',
                last_name: worker.last_name || fallback.last_name || '',
                trade: worker.trade || fallback.trade || 'General',
                contact_number: worker.contact_number ?? fallback.contact_number ?? '',
                profile_image_url: worker.profile_image_url || fallback.profile_image_url || null,
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
        function formatOvertimeLabel(minutes) {
            const totalMinutes = Number(minutes || 0);

            if (totalMinutes <= 0) {
                return '—';
            }

            const hours = Math.floor(totalMinutes / 60);
            const remainingMinutes = totalMinutes % 60;
            const parts = [];

            if (hours > 0) {
                parts.push(`${hours}h`);
            }

            if (remainingMinutes > 0 || parts.length === 0) {
                parts.push(`${remainingMinutes}m`);
            }

            return `OT ${parts.join(' ')}`;
        }

        function localTodayDateString() {
            const now = new Date();
            const timezoneOffset = now.getTimezoneOffset() * 60000;
            return new Date(now.getTime() - timezoneOffset).toISOString().slice(0, 10);
        }

        function selectedDateValue() {
            return attendanceDateInput?.value || localTodayDateString();
        }

        function isFivePmOrLaterForSelectedDate() {
            const selected = selectedDateValue();
            const today = localTodayDateString();

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
                    <td colspan="8" class="text-center py-5 text-muted fst-italic">
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
                return '<span class="status-pill status-pill-present"><i class="bi bi-check-circle me-1"></i>Present</span>';
            }

            if (value === 'late') {
                return '<span class="status-pill status-pill-late"><i class="bi bi-clock-history me-1"></i>Late</span>';
            }

            if (value === 'absent') {
                return '<span class="status-pill status-pill-absent"><i class="bi bi-x-circle me-1"></i>Absent</span>';
            }

            return `<span class="status-pill status-pill-default">${escapeHtml(value || 'Not Logged')}</span>`;
        }

        function getActionButtons(record) {
            if (!record || record.status === 'absent' || record.status === 'not_logged') {
                return '';
            }

            let buttons = '';

            if (record.time_in && !record.break_out) {
                buttons += `
                    <button type="button"
                            class="btn btn-sm status-action-btn status-action-break-out attendance-action-btn"
                            data-worker-id="${escapeHtml(record.worker_id)}"
                            data-action="break_out">
                        <i class="bi bi-cup-hot me-1"></i> Break Out
                    </button>
                `;
            }

            if (record.break_out && !record.break_in) {
                buttons += `
                    <button type="button"
                            class="btn btn-sm status-action-btn status-action-break-in attendance-action-btn"
                            data-worker-id="${escapeHtml(record.worker_id)}"
                            data-action="break_in">
                        <i class="bi bi-arrow-return-left me-1"></i> Break In
                    </button>
                `;
            }

            if (record.time_in && !record.time_out) {
                if (isFivePmOrLaterForSelectedDate()) {
                    buttons += `
                        <button type="button"
                                class="btn btn-sm status-action-btn status-action-time-out attendance-action-btn"
                                data-worker-id="${escapeHtml(record.worker_id)}"
                                data-action="time_out">
                            <i class="bi bi-box-arrow-right me-1"></i> Time Out
                        </button>
                    `;
                } else {
                    buttons += `
                        <button type="button"
                                class="btn btn-sm status-action-btn status-action-disabled"
                                disabled>
                            <i class="bi bi-lock me-1"></i> Time Out 5PM
                        </button>
                    `;
                }
            }

            return buttons;
        }

        function buildAttendanceRow(record) {
            const workerKey = String(record.worker_id);
            const rawFullName = `${record.first_name || ''} ${record.last_name || ''}`.trim() || 'Worker';
            const fullName = escapeHtml(rawFullName);
            const trade = escapeHtml(record.trade || 'General');

            return `
                <tr class="border-bottom attendance-row-fade"
                    id="row-worker-${workerKey}"
                    data-worker-id="${workerKey}"
                    data-active-log="1">
                    <td class="py-3" data-label="Personnel Name">
                        <div class="attendance-worker-info fw-semibold text-dark">
                            ${workerAvatarMarkup(record.profile_image_url, rawFullName, 'attendance-worker-avatar')}
                            <span>${fullName}</span>
                        </div>
                        <input type="hidden" name="biometric_verified[${workerKey}]" value="${record.biometric_matched === false || record.biometric_matched === 0 || String(record.biometric_matched) === '0' ? '0' : '1'}">
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
                    <td class="py-3 text-center" data-label="OT">
                        <span class="status-pill status-pill-default">
                            ${escapeHtml(record.overtime_label || formatOvertimeLabel(record.overtime_minutes))}
                        </span>
                    </td>

                    <td class="py-3 text-center" data-label="Status Log">
                        <div class="status-log-stack">
                            ${getStatusBadge(record.status)}

                            <div class="status-action-grid">
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
                bindWorkerAvatarFallbacks(updatedRow);

                if (animate && updatedRow) {
                    updatedRow.classList.add('attendance-row-updated');

                    setTimeout(() => {
                        updatedRow.classList.remove('attendance-row-updated');
                    }, 900);
                }
            } else {
                attendanceLogTableBody.insertAdjacentHTML('beforeend', newRowHtml);

                const insertedRow = document.getElementById(`row-worker-${workerKey}`);
                bindWorkerAvatarFallbacks(insertedRow);

                if (animate && insertedRow) {
                    insertedRow.classList.add('attendance-row-new');

                    setTimeout(() => {
                        insertedRow.classList.remove('attendance-row-new');
                    }, 900);
                }
            }

            updateActiveLogCount();
        }

        function normalizeAttendanceRecord(rawRecord) {
            const record = rawRecord || {};
            const worker = record.worker || record.display_worker || record.displayWorker || record.personnel || record.employee || {};
            const deploymentWorker = record.deployment && record.deployment.worker ? record.deployment.worker : {};

            const resolvedWorkerId = record.worker_id
                || record.workerId
                || worker.worker_id
                || worker.id
                || deploymentWorker.worker_id
                || deploymentWorker.id
                || null;

            return {
                worker_id: resolvedWorkerId,
                first_name: record.first_name || worker.first_name || deploymentWorker.first_name || '',
                last_name: record.last_name || worker.last_name || deploymentWorker.last_name || '',
                trade: record.trade || worker.trade || worker.position || worker.job_title || deploymentWorker.trade || 'General',
                profile_image_url: record.profile_image_url || worker.profile_image_url || deploymentWorker.profile_image_url || null,
                time_in: record.time_in || null,
                break_out: record.break_out || null,
                break_in: record.break_in || null,
                time_out: record.time_out || null,
                status: record.status || 'present',
                biometric_matched: record.biometric_matched ?? record.biometric_verified ?? true,
                remarks: record.remarks || ''
            };
        }

        function extractAttendanceRecords(payload) {
            if (Array.isArray(payload)) {
                return payload.map(normalizeAttendanceRecord).filter(record => record.worker_id);
            }

            const possibleRecords = payload?.data
                || payload?.records
                || payload?.logs
                || payload?.attendance_logs
                || [];

            if (!Array.isArray(possibleRecords)) {
                return [];
            }

            return possibleRecords
                .map(normalizeAttendanceRecord)
                .filter(record => record.worker_id);
        }

        function removeMissingRows(latestRecords, options = {}) {
            const allowClear = options.allowClear ?? false;
            const validRecords = Array.isArray(latestRecords)
                ? latestRecords.filter(record => record && record.worker_id)
                : [];

            const latestIds = new Set(
                validRecords.map(record => String(record.worker_id))
            );

            if (latestIds.size === 0) {
                if (allowClear) {
                    scannedWorkerIds.clear();
                    renderEmptyAttendanceRow();
                }

                updateActiveLogCount();
                return;
            }

            attendanceLogTableBody
                .querySelectorAll('tr[data-worker-id]')
                .forEach(row => {
                    if (!latestIds.has(String(row.dataset.workerId))) {
                        row.remove();
                    }
                });

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
                const response = await fetch(`${supervisorRoutes.attendanceToday}?date=${encodeURIComponent(selectedDateValue())}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    cache: 'no-store'
                });

                const result = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(result.message || 'Failed to load attendance.');
                }

                const records = extractAttendanceRecords(result);
                const hasExistingRows = attendanceLogTableBody.querySelectorAll('tr[data-worker-id]').length > 0;

                /*
                    Important fix:
                    During silent polling, do not wipe the table when the endpoint returns
                    an empty or malformed response. This prevents scanned/manual attendance
                    from disappearing while the page is checking for updates.
                */
                if (records.length === 0) {
                    if (!silent || !hasExistingRows) {
                        scannedWorkerIds.clear();
                        renderEmptyAttendanceRow();
                    }

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

                removeMissingRows(records, {
                    allowClear: !silent
                });

                isFirstAttendanceLoad = false;
            } catch (error) {
                console.error(error);

                if (!silent) {
                    attendanceLogTableBody.innerHTML = `
                        <tr>
                            <td colspan="8" class="text-danger text-center py-5">
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
            const response = await fetch(supervisorRoutes.attendanceLogWorker, {
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
            const response = await fetch(supervisorRoutes.attendanceLogWorker, {
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
                showAttendanceAlert('error', 'Attendance update failed', result.message || 'Failed to update attendance.');
                return;
            }

            upsertAttendanceRecord(result.attendance, true);

            const actionLabel = {
                time_in: 'Time In',
                break_out: 'Break Out',
                break_in: 'Break In',
                time_out: 'Time Out'
            }[action] || 'Attendance';

            await showAttendanceSuccessToast(`${actionLabel} recorded`, `${actionLabel} was recorded successfully.`);
        }

        async function fetchWorkersPage(page = 1) {
            const response = await fetch(`${supervisorRoutes.workersList}?page=${page}`, {
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

            manualWorkerSelect.value = '';
            manualWorkerPickerToggle.disabled = true;
            manualWorkerPickerValue.textContent = 'Loading workers...';
            manualWorkerPickerOptions.hidden = true;
            manualWorkerPickerOptions.innerHTML = '';
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
                    manualWorkerPickerValue.textContent = 'No enrolled workers found';

                    manualAttendanceStatus.className = 'alert alert-warning border text-dark small py-2 mb-3';
                    manualAttendanceStatus.innerHTML = `
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        No enrolled workers available for manual attendance.
                    `;

                    return;
                }

                manualWorkerPickerValue.textContent = 'Select worker...';
                manualWorkerPickerOptions.innerHTML = manualWorkersCache.map(worker => {
                    const rawFullName = `${worker.first_name} ${worker.last_name}`.trim() || 'Worker';
                    const fullName = escapeHtml(rawFullName);
                    const trade = escapeHtml(worker.trade || 'General');

                    return `
                        <button type="button" class="manual-worker-option" role="option"
                                aria-selected="false" data-worker-id="${escapeHtml(worker.worker_id)}">
                            ${workerAvatarMarkup(worker.profile_image_url, rawFullName, 'manual-worker-avatar')}
                            <span class="d-flex flex-column">
                                <span class="fw-semibold">${fullName}</span>
                                <span class="small text-muted">${trade}</span>
                            </span>
                        </button>
                    `;
                }).join('');
                bindWorkerAvatarFallbacks(manualWorkerPickerOptions);
                manualWorkerPickerToggle.disabled = false;

                manualAttendanceStatus.className = 'alert alert-light border text-muted small py-2 mb-3';
                manualAttendanceStatus.innerHTML = `
                    <i class="bi bi-info-circle-fill text-primary"></i>
                    Select a worker and attendance action.
                `;
            } catch (error) {
                console.error(error);

                manualWorkerPickerValue.textContent = 'Unable to load workers';
                await showAttendanceAlert('error', 'Workers could not be loaded', error.message || 'Unable to load workers.');

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
                await showAttendanceAlert('warning', 'Select a worker', 'Please select a worker first.');

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
                const response = await fetch(supervisorRoutes.attendanceLogWorker, {
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
                        remarks: reason,
                        time_in: manualTimeInInput?.value || null,
                        break_out: manualBreakOutInput?.value || null,
                        break_in: manualBreakInInput?.value || null,
                        time_out: manualTimeOutInput?.value || null
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

                await showAttendanceAlert('success', 'Attendance saved', 'Manual attendance was saved successfully.');

                const selectedWorker = manualWorkersCache.find(worker => String(worker.worker_id) === String(workerId));

                globalScanStatus.className = 'alert alert-success border text-success small py-2 mb-0';
                globalScanStatus.innerHTML = `
                    <i class="bi bi-check-circle-fill"></i>
                    Manual ${escapeHtml(action.replace('_', ' '))} saved
                    ${selectedWorker ? `for <strong>${escapeHtml(selectedWorker.first_name)} ${escapeHtml(selectedWorker.last_name)}</strong>` : ''}.
                `;

                manualActionSelect.value = 'time_in';
                if (manualTimeInInput) {
                    manualTimeInInput.value = '';
                }
                if (manualTimeOutInput) {
                    manualTimeOutInput.value = '';
                }
                if (manualBreakOutInput) {
                    manualBreakOutInput.value = '';
                }
                if (manualBreakInInput) {
                    manualBreakInInput.value = '';
                }
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
                await showAttendanceAlert('error', 'Attendance could not be saved', error.message || 'Failed to save manual attendance.');
            } finally {
                btnSaveManualAttendance.disabled = !manualWorkerSelect.value;
                btnSaveManualAttendance.innerHTML = `
                    <i class="bi bi-save"></i> Save Manual Log
                `;
            }
        }

        async function loadWorkers(page = 1) {
            const tableBody = document.getElementById('allWorkersTableBody');
            const pageInfo = document.getElementById('pageInfo');
            const workersCountLabel = document.getElementById('workersCountLabel');

            workersCountLabel.textContent = 'Loading...';

            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="workers-empty-state">
                        <span class="spinner-border spinner-border-sm me-1"></span>
                        Loading workers...
                    </td>
                </tr>
            `;

            try {
                const res = await fetch(`${supervisorRoutes.workersList}?page=${page}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    throw new Error(data.message || 'Unable to load workers.');
                }

                const lastPage = Number(data.last_page || 1);

                if (!Array.isArray(data) && page > lastPage) {
                    await loadWorkers(lastPage);
                    return;
                }

                const workers = Array.isArray(data)
                    ? data
                    : (data.data || data.workers || []);
                rosterWorkers = workers.map(item => normalizeWorker(item));
                const totalWorkers = Number(data.total ?? workers.length);

                workersCountLabel.textContent = `${totalWorkers} ${totalWorkers === 1 ? 'worker' : 'workers'}`;

                if (!workers.length) {
                    rosterWorkers = [];
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="4" class="workers-empty-state">
                                <i class="bi bi-person-lines-fill d-block mb-2" aria-hidden="true"></i>
                                No enrolled workers found.
                            </td>
                        </tr>
                    `;
                } else {
                    tableBody.innerHTML = rosterWorkers.map(worker => {
                        const rawFullName = `${worker.first_name} ${worker.last_name}`.trim() || 'Worker';
                        const fullName = escapeHtml(rawFullName);
                        const trade = escapeHtml(worker.trade || 'General');
                        const enrolledDate = worker.created_at
                            ? new Date(worker.created_at).toLocaleDateString()
                            : '-';

                        return `
                            <tr>
                                <td class="ps-4">
                                    <div class="worker-list-info">
                                        ${workerAvatarMarkup(worker.profile_image_url, rawFullName, 'worker-list-avatar')}
                                        <span class="worker-roster-name">${fullName}</span>
                                    </div>
                                </td>
                                <td><span class="worker-trade-badge">${trade}</span></td>
                                <td class="workers-date-column"><span class="workers-enrolled-date"><i class="bi bi-calendar3 me-1" aria-hidden="true"></i>${enrolledDate}</span></td>
                                <td class="pe-4">
                                    <div class="workers-row-actions">
                                        <button type="button" class="workers-row-action workers-row-action-view" data-worker-view-id="${escapeHtml(worker.worker_id)}" aria-label="View ${fullName}" title="View worker details">
                                            <i class="bi bi-eye" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="workers-row-action workers-row-action-edit" data-worker-edit-id="${escapeHtml(worker.worker_id)}" aria-label="Edit ${fullName}" title="Edit worker">
                                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="workers-row-action workers-row-action-delete" data-worker-delete-id="${escapeHtml(worker.worker_id)}" aria-label="Delete ${fullName}" title="Delete worker">
                                            <i class="bi bi-trash3" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }).join('');

                    bindWorkerAvatarFallbacks(tableBody);
                }

                currentPage = data.current_page || page;
                pageInfo.innerText = `Page ${currentPage}`;

                prevPageBtn.disabled = currentPage <= 1;
                nextPageBtn.disabled = currentPage >= (data.last_page || currentPage);
            } catch (error) {
                console.error(error);

                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="workers-empty-state text-danger">
                            ${escapeHtml(error.message || 'Error loading worker data.')}
                        </td>
                    </tr>
                `;
                rosterWorkers = [];
                workersCountLabel.textContent = 'Unavailable';
            }
        }

        viewWorkersModal?.addEventListener('show.bs.modal', function () {
            viewWorkersModal.dataset.modalReady = 'false';
            loadWorkers(currentPage);
        });

        viewWorkersModal?.addEventListener('shown.bs.modal', function () {
            viewWorkersModal.dataset.modalReady = 'true';
        });

        viewWorkersModal?.addEventListener('hidden.bs.modal', function () {
            delete viewWorkersModal.dataset.modalReady;
        });

        function openWorkerDetailsModal(worker) {
            const fullName = `${worker.first_name} ${worker.last_name}`.trim() || 'Worker';
            const trade = worker.trade || 'General';
            const enrolledDate = worker.created_at
                ? new Date(worker.created_at).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
                : 'Not available';

            document.getElementById('workerDetailName').textContent = fullName;
            document.getElementById('workerDetailTrade').textContent = trade;
            document.getElementById('workerDetailId').textContent = worker.worker_id;
            document.getElementById('workerDetailContact').textContent = worker.contact_number || 'Not provided';
            document.getElementById('workerDetailEnrolled').textContent = enrolledDate;

            const avatarMarkup = workerAvatarMarkup(worker.profile_image_url, fullName, 'worker-detail-avatar');

            if (worker.profile_image_url) {
                workerDetailsPhotoContainer.innerHTML = `
                    <button type="button" class="worker-detail-photo-button" aria-label="Enlarge ${escapeHtml(fullName)} profile photo" title="Enlarge profile photo">
                        ${avatarMarkup}
                    </button>
                `;
                workerDetailPhotoHint.hidden = false;
            } else {
                workerDetailsPhotoContainer.innerHTML = `<div class="worker-detail-photo-static" aria-label="No profile photo">${avatarMarkup}</div>`;
                workerDetailPhotoHint.hidden = true;
            }

            bindWorkerAvatarFallbacks(workerDetailsPhotoContainer);

            const showDetails = () => bootstrap.Modal.getOrCreateInstance(workerDetailsModalElement).show();

            if (viewWorkersModal.classList.contains('show')) {
                returnToRosterAfterDetails = true;
                const hideRoster = () => {
                    viewWorkersModal.addEventListener('hidden.bs.modal', showDetails, { once: true });
                    (bootstrap.Modal.getInstance(viewWorkersModal)
                        || bootstrap.Modal.getOrCreateInstance(viewWorkersModal)).hide();
                };

                if (viewWorkersModal.dataset.modalReady === 'false') {
                    viewWorkersModal.addEventListener('shown.bs.modal', hideRoster, { once: true });
                } else {
                    hideRoster();
                }
            } else {
                showDetails();
            }
        }

        workerDetailsModalElement?.addEventListener('hidden.bs.modal', function () {
            if (returnToRosterAfterDetails) {
                returnToRosterAfterDetails = false;
                bootstrap.Modal.getOrCreateInstance(viewWorkersModal).show();
            }
        });

        function openWorkerPhotoLightbox(button, photoUrl, name) {
            if (!photoUrl) {
                return;
            }

            photoLightboxReturnFocus = button;
            workerPhotoLightboxImage.src = photoUrl;
            workerPhotoLightboxImage.alt = `${name} profile photo`;
            workerPhotoLightboxCaption.textContent = name;
            workerPhotoLightbox.hidden = false;
            workerPhotoLightboxClose.focus();
        }

        function closeWorkerPhotoLightbox() {
            workerPhotoLightbox.hidden = true;
            workerPhotoLightboxImage.removeAttribute('src');
            workerPhotoLightboxCaption.textContent = '';
            photoLightboxReturnFocus?.focus();
            photoLightboxReturnFocus = null;
        }

        workerDetailsPhotoContainer?.addEventListener('click', function (event) {
            const photoButton = event.target.closest('.worker-detail-photo-button');

            if (!photoButton) {
                return;
            }

            const image = photoButton.querySelector('.worker-avatar-img');
            const name = document.getElementById('workerDetailName').textContent;
            openWorkerPhotoLightbox(photoButton, image?.src, name);
        });

        workerPhotoLightboxClose?.addEventListener('click', closeWorkerPhotoLightbox);
        workerPhotoLightbox?.addEventListener('click', function (event) {
            if (event.target === workerPhotoLightbox) {
                closeWorkerPhotoLightbox();
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !workerPhotoLightbox.hidden) {
                event.preventDefault();
                event.stopImmediatePropagation();
                closeWorkerPhotoLightbox();
            }
        }, true);

        function updateEditWorkerPhotoPreview(photoUrl, name) {
            editWorkerAvatarPreview.innerHTML = workerAvatarMarkup(photoUrl, name, 'worker-edit-avatar');
            bindWorkerAvatarFallbacks(editWorkerAvatarPreview);
        }

        function openEditWorkerModal(worker) {
            editWorkerForm.reset();
            editWorkerIdInput.value = worker.worker_id;
            editWorkerFirstNameInput.value = worker.first_name;
            editWorkerLastNameInput.value = worker.last_name;
            editWorkerTradeInput.value = worker.trade === 'General' ? '' : worker.trade;
            editWorkerContactInput.value = worker.contact_number || '';
            editWorkerProfileImageInput.value = '';

            const workerName = `${worker.first_name} ${worker.last_name}`.trim() || 'Worker';
            updateEditWorkerPhotoPreview(worker.profile_image_url, workerName);

            const showEditor = () => {
                bootstrap.Modal.getOrCreateInstance(editWorkerModalElement).show();
            };

            if (viewWorkersModal.classList.contains('show')) {
                returnToRosterAfterEdit = true;
                const hideRoster = () => {
                    viewWorkersModal.addEventListener('hidden.bs.modal', showEditor, { once: true });
                    (bootstrap.Modal.getInstance(viewWorkersModal)
                        || bootstrap.Modal.getOrCreateInstance(viewWorkersModal)).hide();
                };

                if (viewWorkersModal.dataset.modalReady === 'false') {
                    viewWorkersModal.addEventListener('shown.bs.modal', hideRoster, { once: true });
                } else {
                    hideRoster();
                }
            } else {
                showEditor();
            }
        }

        editWorkerModalElement?.addEventListener('hidden.bs.modal', function () {
            if (returnToRosterAfterEdit) {
                returnToRosterAfterEdit = false;
                bootstrap.Modal.getOrCreateInstance(viewWorkersModal).show();
            }
        });

        editWorkerProfileImageInput?.addEventListener('change', function () {
            const file = editWorkerProfileImageInput.files?.[0];

            if (!file) {
                return;
            }

            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
                showAttendanceAlert('warning', 'Invalid profile photo', 'Choose a JPG, PNG, or WebP image no larger than 2 MB.');
                editWorkerProfileImageInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = () => {
                const workerName = `${editWorkerFirstNameInput.value} ${editWorkerLastNameInput.value}`.trim() || 'Worker';
                updateEditWorkerPhotoPreview(reader.result, workerName);
            };
            reader.readAsDataURL(file);
        });

        editWorkerForm?.addEventListener('submit', async function (event) {
            event.preventDefault();

            const firstName = editWorkerFirstNameInput.value.trim();
            const lastName = editWorkerLastNameInput.value.trim();
            const profileImageFile = editWorkerProfileImageInput.files?.[0];

            if (!firstName || !lastName) {
                await showAttendanceAlert('warning', 'Worker name required', 'Enter both a first name and a last name.');
                return;
            }

            if (profileImageFile && (!['image/jpeg', 'image/png', 'image/webp'].includes(profileImageFile.type) || profileImageFile.size > 2 * 1024 * 1024)) {
                await showAttendanceAlert('warning', 'Invalid profile photo', 'Choose a JPG, PNG, or WebP image no larger than 2 MB.');
                return;
            }

            const workerId = editWorkerIdInput.value;
            const formData = new FormData(editWorkerForm);
            formData.set('_method', 'PUT');
            btnSaveEditedWorker.disabled = true;
            btnSaveEditedWorker.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

            try {
                const response = await fetch(`${supervisorRoutes.workerProfileBase}/${encodeURIComponent(workerId)}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });
                const result = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const validationMessage = Object.values(result.errors || {}).flat()[0];
                    throw new Error(validationMessage || result.message || 'Unable to update worker information.');
                }

                await loadWorkers(currentPage);
                await loadTodayAttendance({ silent: true });
                await showAttendanceAlert('success', 'Worker updated', result.message || 'Worker information was updated successfully.');
                bootstrap.Modal.getInstance(editWorkerModalElement)?.hide();
            } catch (error) {
                console.error(error);
                await showAttendanceAlert('error', 'Worker could not be updated', error.message || 'Unable to update worker information.');
            } finally {
                btnSaveEditedWorker.disabled = false;
                btnSaveEditedWorker.innerHTML = '<i class="bi bi-check-lg"></i> Save Changes';
            }
        });

        async function deleteWorker(worker, button) {
            const workerName = `${worker.first_name} ${worker.last_name}`.trim() || 'this worker';
            const confirmation = window.Swal?.fire
                ? await Swal.fire({
                    icon: 'warning',
                    title: 'Delete worker?',
                    text: `${workerName} will be removed from active workers. Historical attendance and tool records will be preserved.`,
                    showCancelButton: true,
                    confirmButtonText: 'Delete worker',
                    cancelButtonText: 'Keep worker',
                    confirmButtonColor: '#a33a32',
                    cancelButtonColor: '#647366',
                    reverseButtons: true
                })
                : { isConfirmed: window.confirm(`Remove ${workerName} from active workers? Historical records will be preserved.`) };

            if (!confirmation.isConfirmed) {
                return;
            }

            button.disabled = true;

            try {
                const response = await fetch(`${supervisorRoutes.workerProfileBase}/${encodeURIComponent(worker.worker_id)}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const result = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(result.message || 'Unable to delete worker.');
                }

                await loadWorkers(currentPage);
                await loadTodayAttendance({ silent: true });
                await showAttendanceAlert('success', 'Worker removed', result.message || 'Worker was removed from the active roster.');
            } catch (error) {
                console.error(error);
                button.disabled = false;
                await showAttendanceAlert('error', 'Worker could not be deleted', error.message || 'Unable to delete worker.');
            }
        }

        document.getElementById('allWorkersTableBody')?.addEventListener('click', function (event) {
            const viewButton = event.target.closest('[data-worker-view-id]');
            const editButton = event.target.closest('[data-worker-edit-id]');
            const deleteButton = event.target.closest('[data-worker-delete-id]');
            const actionButton = viewButton || editButton || deleteButton;

            if (!actionButton) {
                return;
            }

            const workerId = actionButton.dataset.workerViewId
                || actionButton.dataset.workerEditId
                || actionButton.dataset.workerDeleteId;
            const worker = rosterWorkers.find(item => String(item.worker_id) === String(workerId));

            if (!worker) {
                return;
            }

            if (viewButton) {
                openWorkerDetailsModal(worker);
            } else if (editButton) {
                openEditWorkerModal(worker);
            } else {
                deleteWorker(worker, deleteButton);
            }
        });

        manualAttendanceModal?.addEventListener('show.bs.modal', function () {
            loadManualWorkers();
        });

        manualWorkerPickerToggle?.addEventListener('click', function () {
            const isExpanded = manualWorkerPickerToggle.getAttribute('aria-expanded') === 'true';
            manualWorkerPickerToggle.setAttribute('aria-expanded', String(!isExpanded));
            manualWorkerPickerOptions.hidden = isExpanded;

            if (!isExpanded) {
                manualWorkerPickerOptions.querySelector('[aria-selected="true"]')?.focus();
            }
        });

        manualWorkerPickerOptions?.addEventListener('click', function (event) {
            const option = event.target.closest('.manual-worker-option');

            if (!option) {
                return;
            }

            const worker = manualWorkersCache.find(item => String(item.worker_id) === String(option.dataset.workerId));

            if (!worker) {
                return;
            }

            const fullName = `${worker.first_name} ${worker.last_name}`.trim() || 'Worker';
            manualWorkerSelect.value = worker.worker_id;
            manualWorkerPickerValue.innerHTML = `
                <span class="d-inline-flex align-items-center gap-2">
                    ${workerAvatarMarkup(worker.profile_image_url, fullName, 'manual-worker-avatar')}
                    <span>${escapeHtml(fullName)}</span>
                </span>
            `;
            manualWorkerPickerOptions.querySelectorAll('.manual-worker-option').forEach(item => {
                item.setAttribute('aria-selected', String(item === option));
            });
            bindWorkerAvatarFallbacks(manualWorkerPickerValue);
            manualWorkerPickerOptions.hidden = true;
            manualWorkerPickerToggle.setAttribute('aria-expanded', 'false');
            btnSaveManualAttendance.disabled = false;
        });

        manualWorkerPickerOptions?.addEventListener('keydown', function (event) {
            const options = [...manualWorkerPickerOptions.querySelectorAll('.manual-worker-option')];
            const currentIndex = options.indexOf(document.activeElement);

            if (event.key === 'Escape') {
                manualWorkerPickerOptions.hidden = true;
                manualWorkerPickerToggle.setAttribute('aria-expanded', 'false');
                manualWorkerPickerToggle.focus();
                return;
            }

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                const direction = event.key === 'ArrowDown' ? 1 : -1;
                const nextIndex = Math.max(0, Math.min(options.length - 1, currentIndex + direction));
                options[nextIndex]?.focus();
            }
        });

        document.addEventListener('click', function (event) {
            if (manualWorkerPicker && !manualWorkerPicker.contains(event.target)) {
                manualWorkerPickerOptions.hidden = true;
                manualWorkerPickerToggle.setAttribute('aria-expanded', 'false');
            }
        });

        manualWorkerPickerToggle?.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                manualWorkerPickerOptions.hidden = true;
                manualWorkerPickerToggle.setAttribute('aria-expanded', 'false');
            }
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
            // Check biometric support before proceeding
            if (!webAuthnAvailable) {
                globalScanStatus.className = 'alert alert-danger border text-danger small py-2 mb-0';
                globalScanStatus.innerHTML = `
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Biometric authentication is not supported in this browser. Please use the 
                    <strong>Manual Log</strong> option or try a different browser (Chrome, Firefox, Safari).
                `;
                return;
            }

            if (btnGlobalScan.disabled) {
                return;
            }

            btnGlobalScan.disabled = true;

            globalScanStatus.className = 'alert alert-warning border text-dark small py-2 mb-0';
            globalScanStatus.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2"></span>
                Opening biometric scanner...
            `;

            try {
                const response = await fetch(supervisorRoutes.passkeyLoginOptions, {
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

                const submitResponse = await fetch(supervisorRoutes.passkeyLogin, {
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
                    await showAttendanceSuccessToast(
                        'Time In recorded',
                        `${worker.first_name} ${worker.last_name} has been timed in successfully.`
                    );
                } else {
                    throw new Error(result.message || 'Authentication failed: Worker not recognized.');
                }
            } catch (err) {
                console.error(err);

                let errorMessage = err.message || 'Unknown error occurred';
                
                // Provide user-friendly error messages
                if (errorMessage.includes('NotAllowedError') || errorMessage.includes('NotSupported')) {
                    errorMessage = 'Biometric verification was cancelled or not supported. Please try the Manual Log option.';
                } else if (errorMessage.includes('NotSupportedError')) {
                    errorMessage = 'Biometric authentication is not supported on this device. Please use Manual Log instead.';
                } else if (errorMessage.includes('timeout') || errorMessage.includes('Timeout')) {
                    errorMessage = 'Biometric verification timed out. Please try again.';
                }

                globalScanStatus.className = 'alert alert-danger border text-danger small py-2 mb-0';
                globalScanStatus.innerHTML = `
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    ${escapeHtml(errorMessage)}
                `;
            } finally {
                btnGlobalScan.disabled = false;
            }
        });

        btnRegisterFingerprint?.addEventListener('click', async function () {
            // Check WebAuthn support before proceeding
            if (!webAuthnAvailable) {
                regStatusLabel.innerText = 'Biometric authentication is not supported in this browser. Please use Chrome, Firefox, or Safari.';
                regStatusLabel.className = 'd-block text-danger small mt-1';
                return;
            }

            const firstName = document.getElementById('regFirstName').value.trim();
            const lastName = document.getElementById('regLastName').value.trim();
            const contactNumber = regContactNumberInput.value.trim();

            if (!firstName || !lastName) {
                showAttendanceAlert('warning', 'Worker name required', 'Please enter the worker first name and last name before capturing biometrics.');
                return;
            }

            if (!isValidContactNumber(contactNumber)) {
                showAttendanceAlert('warning', 'Invalid contact number', 'Use up to 20 digits or common phone-number characters such as +, spaces, parentheses, periods, or hyphens.');
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
                const response = await fetch(supervisorRoutes.passkeyRegisterOptions, {
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

                let errorMessage = err.message || 'Registration failed. Please try again.';
                
                // Provide user-friendly error messages
                if (errorMessage.includes('NotAllowedError') || errorMessage.includes('NotSupported')) {
                    errorMessage = 'Biometric capture cancelled or not supported. Please try with a different browser.';
                } else if (errorMessage.includes('NotSupportedError')) {
                    errorMessage = 'Biometric authentication is not supported on this device.';
                }

                regStatusLabel.innerText = errorMessage;
                regStatusLabel.className = 'd-block text-danger small mt-1';

                btnSaveWorker.disabled = true;
            } finally {
                btnRegisterFingerprint.disabled = false;
                btnRegisterFingerprint.innerHTML = `
                    <i class="bi bi-shield-plus"></i>
                    Initialize fingerprint
                `;
            }
        });

        btnSaveWorker?.addEventListener('click', async function () {
            const firstName = document.getElementById('regFirstName').value.trim();
            const lastName = document.getElementById('regLastName').value.trim();
            const contactNumber = regContactNumberInput.value.trim();
            const trade = document.getElementById('regTrade').value.trim() || 'General';
            const profileImageFile = regProfileImageInput.files?.[0];

            if (!firstName || !lastName) {
                showAttendanceAlert('warning', 'Worker name required', 'Please enter the worker first name and last name.');
                return;
            }

            if (!capturedPasskeyCredential) {
                showAttendanceAlert('warning', 'Fingerprint required', 'Please capture the worker fingerprint/passkey first.');
                return;
            }

            if (!isValidContactNumber(contactNumber)) {
                showAttendanceAlert('warning', 'Invalid contact number', 'Use up to 20 digits or common phone-number characters such as +, spaces, parentheses, periods, or hyphens.');
                return;
            }

            if (profileImageFile && (!['image/jpeg', 'image/png', 'image/webp'].includes(profileImageFile.type) || profileImageFile.size > 2 * 1024 * 1024)) {
                showAttendanceAlert('warning', 'Invalid profile photo', 'Choose a JPG, PNG, or WebP image no larger than 2 MB.');
                return;
            }

            btnSaveWorker.disabled = true;
            btnSaveWorker.innerHTML = `
                <span class="spinner-border spinner-border-sm me-1"></span>
                Saving...
            `;

            try {
                const response = await fetch(supervisorRoutes.workerRegister, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        first_name: firstName,
                        last_name: lastName,
                        contact_number: contactNumber,
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
                    contact_number: contactNumber,
                    trade: trade
                });

                if (!savedWorker.worker_id) {
                    throw new Error('Worker was saved, but the server did not return worker_id.');
                }

                let profileImageUploadFailed = false;

                if (profileImageFile) {
                    try {
                        const imageResult = await uploadWorkerProfileImage(savedWorker.worker_id, profileImageFile);
                        savedWorker.profile_image_url = imageResult.profile_image_url;
                    } catch (imageError) {
                        profileImageUploadFailed = true;
                        console.error(imageError);
                    }
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
                regProfileImagePreview.hidden = true;
                regProfileImagePreview.removeAttribute('src');

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
                    ${profileImageUploadFailed ? '<br><span>Photo upload failed. Use Enrolled Workers to retry.</span>' : ''}
                `;
                await showAttendanceAlert(
                    profileImageUploadFailed ? 'warning' : 'success',
                    profileImageUploadFailed ? 'Worker saved, photo not uploaded' : 'Worker enrolled',
                    profileImageUploadFailed
                        ? 'The worker and attendance were saved, but the profile photo could not be uploaded. Retry it from Enrolled Workers.'
                        : `${savedWorker.first_name} ${savedWorker.last_name} was enrolled and timed in successfully.`
                );
            } catch (error) {
                console.error(error);

                await showAttendanceAlert('error', 'Worker could not be saved', error.message || 'Failed to save worker.');

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