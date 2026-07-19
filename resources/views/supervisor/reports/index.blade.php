@extends('layouts.supervisor')

@section('title', 'Accomplishment Reports - Supervisor Workspace')
@section('page_title', 'Accomplishment Reports')

@push('styles')
    <style>
        :root {
            --cms-green-dark: #2a4028;
            --cms-green-light: #e8efe0;
            --cms-green-muted: rgba(42, 64, 40, 0.12);
            --cms-text-muted: #64748B;
        }

        .report-filter-card, .metric-card, .main-report-card {
            border-radius: 12px;
            border: 1px solid var(--cms-green-muted);
            background: #fff;
            box-shadow: 0 4px 12px rgba(9, 96, 86, 0.03);
        }

        .metric-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .table-report th {
            background-color: var(--cms-green-light) !important;
            color: var(--cms-green-dark);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 16px;
        }

        .table-report td {
            padding: 16px;
            vertical-align: middle;
            font-size: 0.88rem;
        }

        .avatar-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .status-pill {
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            display: inline-block;
        }
        .status-pill-approved {
            background: #DCFCE7;
            color: #15803D;
        }
        .status-pill-pending {
            background: #F1F5F9;
            color: #64748B;
        }
        .status-pill-warning {
            background: #FEF3C7;
            color: #D97706;
        }
        .status-pill-error {
            background: #FEE2E2;
            color: #DC2626;
        }

        /* Report Details Modal Layout */
        .report-details-modal .modal-dialog {
            max-width: 1080px;
        }

        .report-detail-card,
        .report-detail-sidebar {
            border-radius: 16px;
            border: 1px solid rgba(9, 96, 86, 0.12);
            background: #ffffff;
            box-shadow: 0 18px 42px rgba(9, 96, 86, 0.06);
        }

        .report-detail-card {
            padding: 2rem;
        }

        .report-detail-sidebar {
            background: #F8FAFC;
            border-color: rgba(22, 101, 52, 0.12);
        }

        .report-detail-sidebar .img-thumbnail-grid {
            width: 100%;
            max-width: 108px;
            height: 88px;
            min-width: 88px;
        }

        .report-detail-sidebar .more-images-badge {
            width: auto;
            min-width: 108px;
            background: #F1F5F9;
            color: #166534;
        }

        .drawer-section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--cms-green-dark);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--cms-green-muted);
            padding-bottom: 0.5rem;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .img-thumbnail-grid {
            width: 65px;
            height: 65px;
            object-fit: cover;
            border-radius: 6px;
        }

        .more-images-badge {
            width: 65px;
            height: 65px;
            background: #f0f0f0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: bold;
            color: #555;
        }

        /* Progress Steps Timeline */
        .timeline-container {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-top: 1rem;
            padding: 0 0.75rem;
        }
        .timeline-container::before {
            content: '';
            position: absolute;
            top: 18px;
            left: 20%;
            right: 20%;
            height: 2px;
            background: #d9e5dd;
            z-index: 1;
        }
        .timeline-step {
            text-align: center;
            position: relative;
            z-index: 2;
            flex: 1;
            min-width: 0;
        }
        .timeline-step:first-child {
            text-align: left;
        }
        .timeline-step:last-child {
            text-align: right;
        }
        .timeline-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid #d9e5dd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-size: 0.9rem;
        }
        .timeline-step.active .timeline-icon {
            border-color: #166534;
            background: #166534;
            color: #fff;
        }
        .timeline-step.current .timeline-icon {
            border-color: #ffc107;
            background: #fff;
            color: #ffc107;
        }

        /* ========================================== */
        /* MODAL REDESIGN CLASSES FROM UI IMAGE SPEC  */
        /* ========================================== */
        .cms-modal .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }

        .cms-modal .modal-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 20px 24px;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .cms-modal .modal-title {
            color: #1e293b;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .cms-modal .modal-subtitle {
            color: #64748b;
            font-size: 0.85rem;
            margin-top: 2px;
        }

        .cms-modal .modal-body {
            padding: 24px;
            background-color: #ffffff;
        }

        .cms-form-section-header {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--cms-green-dark);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 14px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cms-form-section-header::after {
            content: '';
            flex-grow: 1;
            height: 1px;
            background-color: #f1f5f9;
        }

        .cms-form-group {
            margin-bottom: 18px;
        }

        .cms-form-label {
            display: block;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
            font-size: 0.85rem;
        }

        .cms-form-control {
            width: 100%;
            padding: 9px 12px;
            font-size: 0.9rem;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            color: #1e293b;
            transition: all 0.15s ease;
        }

        .cms-form-control:focus {
            outline: none;
            border-color: var(--cms-green-dark);
            box-shadow: 0 0 0 3px rgba(9, 96, 86, 0.12);
        }

        .cms-form-control:disabled {
            background-color: #f8fafc;
            color: #94a3b8;
            cursor: not-allowed;
            border-color: #e2e8f0;
        }

        #modal_phase_id,
        #modal_project_id {
            pointer-events: auto !important;
            cursor: pointer !important;
        }

        .cms-form-control::placeholder {
            color: #94a3b8;
        }

        /* Drag & Drop Area from Image spec */
        .cms-file-upload-zone {
            border: 2px dashed #cbd5e1;
            background-color: #f8fafc;
            border-radius: 14px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 220px;
            display: grid;
            place-items: center;
        }

        .cms-file-upload-zone:hover,
        .cms-file-upload-zone.dragover {
            border-color: var(--cms-green-dark);
            background-color: var(--cms-green-light);
        }

        .cms-file-upload-icon {
            font-size: 1.9rem;
            color: #64748b;
            margin-bottom: 10px;
        }

        #uploadPromptText {
            width: 100%;
        }

        .cms-file-upload-zone.has-images #uploadPromptText {
            display: none;
        }

        .cms-file-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 12px;
            width: 100%;
            margin-top: 18px;
        }

        .cms-file-preview-thumb {
            position: relative;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(15, 66, 42, 0.08);
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(15, 66, 42, 0.06);
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cms-file-preview-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .cms-file-preview-thumb .preview-label {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 8px 10px;
            background: rgba(15, 66, 42, 0.75);
            color: #f8fafc;
            font-size: 0.72rem;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cms-modal .modal-footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 16px 24px;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .btn-cms-secondary {
            background-color: #ffffff;
            color: #475569;
            border: 1px solid #cbd5e1;
            font-weight: 600;
            padding: 9px 18px;
            border-radius: 6px;
            font-size: 0.88rem;
            transition: all 0.15s;
        }

        .btn-cms-secondary:hover {
            background-color: #f1f5f9;
            color: #1e293b;
        }

        .btn-cms-primary {
            background-color: var(--cms-green-dark);
            color: #ffffff;
            border: none;
            font-weight: 600;
            padding: 9px 20px;
            border-radius: 6px;
            font-size: 0.88rem;
            transition: all 0.15s;
        }

        .btn-cms-primary:hover {
            background-color: #074740;
            color: #ffffff;
        }

        .preview-file-chip-new {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            font-size: 0.8rem;
            padding: 4px 10px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #475569;
        }


        /* ===== MOBILE FIX: hide desktop table and show clean report cards ===== */
        .supervisor-report-mobile-list {
            display: none;
        }

        @media (max-width: 767.98px) {
            .report-filter-card {
                margin-bottom: 0.9rem !important;
                padding: 0.85rem !important;
                border-radius: 16px !important;
                box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04) !important;
            }

            .report-filter-card .row {
                --bs-gutter-x: 0.75rem;
                --bs-gutter-y: 0.7rem;
            }

            .report-filter-card .form-label {
                margin-bottom: 0.35rem !important;
                color: #475569 !important;
                font-size: 0.68rem !important;
                letter-spacing: 0.03em !important;
                text-transform: uppercase;
            }

            .report-filter-card .form-select,
            .report-filter-card .form-control {
                min-height: 42px !important;
                border-radius: 12px !important;
                border-color: #dce7df !important;
                font-size: 0.84rem !important;
                box-shadow: none !important;
            }

            .metric-card {
                min-height: 104px !important;
                padding: 0.85rem !important;
                border-radius: 16px !important;
                align-items: flex-start !important;
                gap: 0.72rem !important;
                box-shadow: 0 8px 20px rgba(15, 23, 42, 0.045) !important;
            }

            .metric-icon-wrapper {
                width: 40px !important;
                height: 40px !important;
                flex: 0 0 40px !important;
                font-size: 0.98rem !important;
            }

            .metric-card .text-muted.small.fw-bold {
                font-size: 0.62rem !important;
                line-height: 1.2 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.04em !important;
            }

            .metric-card h4 {
                font-size: 1.18rem !important;
                line-height: 1.05 !important;
                margin-top: 0.2rem !important;
            }

            .metric-card span {
                display: block;
                font-size: 0.66rem !important;
                line-height: 1.25 !important;
                margin-top: 0.18rem !important;
            }

            .main-report-card {
                border-radius: 18px !important;
                overflow: hidden !important;
                border: 1px solid rgba(42, 64, 40, 0.10) !important;
                box-shadow: 0 10px 26px rgba(15, 23, 42, 0.05) !important;
            }

            .main-report-card > .border-bottom {
                padding: 0.95rem !important;
                gap: 0.8rem !important;
                align-items: center !important;
            }

            .main-report-card > .border-bottom h5 {
                font-size: 1.02rem !important;
                line-height: 1.2 !important;
            }

            .main-report-card > .border-bottom .btn {
                min-width: 106px !important;
                min-height: 42px !important;
                border-radius: 12px !important;
                font-size: 0.78rem !important;
                font-weight: 800 !important;
                white-space: nowrap !important;
            }

            .supervisor-report-table-wrap,
            .main-report-card .table-responsive.supervisor-report-table-wrap {
                display: none !important;
            }

            .supervisor-report-mobile-list {
                display: grid !important;
                gap: 0.85rem !important;
                padding: 0.9rem !important;
                background: linear-gradient(180deg, #fbfdfb 0%, #ffffff 100%) !important;
            }

            .supervisor-report-mobile-card {
                border: 1px solid rgba(42, 64, 40, 0.12) !important;
                border-radius: 18px !important;
                background: #ffffff !important;
                box-shadow: 0 9px 23px rgba(15, 23, 42, 0.05) !important;
                padding: 0.95rem !important;
                overflow: hidden !important;
            }

            .report-mobile-card-top {
                display: flex !important;
                align-items: flex-start !important;
                justify-content: space-between !important;
                gap: 0.75rem !important;
                padding-bottom: 0.75rem !important;
                margin-bottom: 0.8rem !important;
                border-bottom: 1px solid rgba(42, 64, 40, 0.08) !important;
            }

            .report-mobile-card-top > div {
                display: grid !important;
                gap: 0.12rem !important;
                min-width: 0 !important;
            }

            .report-mobile-label,
            .report-mobile-detail span {
                display: block !important;
                color: #64748b !important;
                font-size: 0.63rem !important;
                font-weight: 900 !important;
                line-height: 1.2 !important;
                letter-spacing: 0.075em !important;
                text-transform: uppercase !important;
            }

            .report-mobile-card-top strong {
                color: #111827 !important;
                font-size: 0.96rem !important;
                font-weight: 900 !important;
                line-height: 1.2 !important;
            }

            .report-mobile-card-top small,
            .report-mobile-detail small {
                color: #64748b !important;
                font-size: 0.74rem !important;
                line-height: 1.25 !important;
            }

            .report-mobile-card-top .status-pill,
            .report-mobile-card-top [class*="status-pill"] {
                flex-shrink: 0 !important;
                max-width: 126px !important;
                text-align: center !important;
                white-space: normal !important;
                line-height: 1.15 !important;
                padding: 0.42rem 0.66rem !important;
                border-radius: 999px !important;
                font-size: 0.66rem !important;
            }

            .report-mobile-title {
                margin: 0 0 0.85rem !important;
                color: #172033 !important;
                font-family: 'DM Sans', sans-serif !important;
                font-size: 1rem !important;
                font-weight: 800 !important;
                line-height: 1.28 !important;
                word-break: normal !important;
                overflow-wrap: anywhere !important;
            }

            .report-mobile-detail-grid {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 0.65rem !important;
            }

            .report-mobile-detail {
                display: grid !important;
                gap: 0.22rem !important;
                min-width: 0 !important;
                padding: 0.76rem 0.82rem !important;
                border: 1px solid rgba(42, 64, 40, 0.08) !important;
                border-radius: 14px !important;
                background: #fbfdfb !important;
            }

            .report-mobile-detail strong {
                color: #111827 !important;
                font-size: 0.86rem !important;
                font-weight: 800 !important;
                line-height: 1.35 !important;
                word-break: normal !important;
                overflow-wrap: anywhere !important;
            }

            .report-mobile-person {
                display: flex !important;
                align-items: center !important;
                gap: 0.55rem !important;
                min-width: 0 !important;
            }

            .report-mobile-person .avatar-img {
                flex: 0 0 32px !important;
                width: 32px !important;
                height: 32px !important;
            }

            .report-mobile-person > div:last-child {
                display: grid !important;
                gap: 0.08rem !important;
                min-width: 0 !important;
            }

            .report-mobile-actions {
                display: grid !important;
                grid-template-columns: minmax(0, 1fr) 78px !important;
                gap: 0.6rem !important;
                margin-top: 0.9rem !important;
                padding-top: 0.82rem !important;
                border-top: 1px solid rgba(42, 64, 40, 0.08) !important;
            }

            .report-mobile-action-btn {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 0.42rem !important;
                min-height: 42px !important;
                border-radius: 12px !important;
                border: 1px solid rgba(42, 64, 40, 0.16) !important;
                background: #2a4028 !important;
                color: #ffffff !important;
                font-size: 0.8rem !important;
                font-weight: 800 !important;
                text-decoration: none !important;
            }

            .report-mobile-action-btn:hover,
            .report-mobile-action-btn:focus {
                background: #365233 !important;
                color: #ffffff !important;
            }

            .report-mobile-action-secondary {
                min-width: 78px !important;
                background: #f8fafc !important;
                color: #2a4028 !important;
            }

            .report-mobile-action-secondary:hover,
            .report-mobile-action-secondary:focus {
                background: #e8efe0 !important;
                color: #2a4028 !important;
            }

            .supervisor-report-empty {
                display: grid !important;
                place-items: center !important;
                gap: 0.5rem !important;
                min-height: 150px !important;
                padding: 1.5rem !important;
                color: #64748b !important;
                border: 1px dashed rgba(42, 64, 40, 0.16) !important;
                border-radius: 16px !important;
                background: #ffffff !important;
                text-align: center !important;
                font-size: 0.9rem !important;
            }

            .supervisor-report-empty i {
                font-size: 1.7rem !important;
                color: #94a3b8 !important;
            }

            .main-report-card > .bg-light.border-top {
                padding: 0.85rem !important;
                background: #ffffff !important;
            }

            .main-report-card .pagination {
                margin: 0.5rem 0 0 !important;
            }

            .report-details-modal .modal-dialog,
            .cms-modal .modal-dialog {
                margin: 0.5rem !important;
                max-width: calc(100vw - 1rem) !important;
                width: auto !important;
                z-index: 9999 !important;
                transform: none !important;
                opacity: 1 !important;
                visibility: visible !important;
            }

            .report-details-modal.show .modal-dialog,
            .cms-modal.show .modal-dialog {
                transform: none !important;
                opacity: 1 !important;
                visibility: visible !important;
            }

            .report-details-modal,
            .cms-modal {
                z-index: 9998 !important;
            }

            .report-details-modal.show,
            .cms-modal.show {
                display: flex !important;
                opacity: 1 !important;
                visibility: visible !important;
            }

            .report-details-modal .modal-backdrop,
            .cms-modal .modal-backdrop {
                z-index: 9997 !important;
            }

            .report-details-modal .modal-content,
            .cms-modal .modal-content {
                border-radius: 18px !important;
                max-height: calc(100vh - 1rem) !important;
                display: flex !important;
                flex-direction: column !important;
                background-color: #ffffff !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: none !important;
            }

            .report-details-modal.show .modal-content,
            .cms-modal.show .modal-content {
                opacity: 1 !important;
                visibility: visible !important;
                transform: none !important;
            }

            .report-details-modal .modal-body,
            .cms-modal .modal-body {
                max-height: calc(100vh - 120px) !important;
                overflow-y: auto !important;
                padding: 0.85rem !important;
                flex: 1 1 auto !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: none !important;
            }

            .report-details-modal .modal-header,
            .cms-modal .modal-header {
                padding: 0.85rem 0.85rem !important;
                flex-shrink: 0 !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: none !important;
            }

            .report-details-modal .modal-header h5,
            .cms-modal .modal-header h5 {
                font-size: 1rem !important;
            }

            .report-details-modal .modal-footer,
            .cms-modal .modal-footer {
                flex-shrink: 0 !important;
            }

            .report-detail-card,
            .report-detail-sidebar {
                padding: 0.85rem !important;
                border-radius: 15px !important;
            }

            .report-detail-card .row.g-3 > [class*="col-"] {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            .report-detail-sidebar .img-thumbnail-grid {
                max-width: 80px !important;
                height: 64px !important;
                min-width: 64px !important;
            }

            .report-detail-sidebar .more-images-badge {
                min-width: 80px !important;
            }
        }

        @media (min-width: 768px) and (max-width: 1024px) {
            .report-details-modal .modal-dialog,
            .cms-modal .modal-dialog {
                max-width: calc(100vw - 2rem) !important;
                margin: 1rem auto !important;
            }

            .report-details-modal .modal-body,
            .cms-modal .modal-body {
                max-height: calc(100vh - 140px) !important;
                padding: 1rem !important;
            }

            .report-detail-card,
            .report-detail-sidebar {
                padding: 1rem !important;
            }
        }

        @media (max-width: 390px) {
            .supervisor-report-mobile-card {
                padding: 0.85rem !important;
            }

            .report-mobile-actions {
                grid-template-columns: 1fr !important;
            }

            .report-mobile-action-secondary {
                width: 100% !important;
            }
        }


        /* Image Lightbox */
        .image-lightbox {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .image-lightbox.is-open {
            display: flex;
        }

        .image-lightbox img {
            max-width: 90%;
            max-height: 85vh;
            border-radius: 8px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
        }

        .image-lightbox-close {
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: #fff;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .image-lightbox-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
@endpush

@section('content')
@php
    $totalCount = $reports->total() ?? 0;
    $pendingCount = $reports->where('approval_status', 'pending')->count(); 
    $approvedCount = $reports->where('approval_status', 'approved')->count();
    $rejectedCount = $reports->where('approval_status', 'rejected')->count();
@endphp

<section class="report-filter-card p-3 mb-4">
    <form id="filterForm" method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-muted">Project</label>
            <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="" {{ request('project_id') === null || request('project_id') === '' ? 'selected' : '' }}>All Projects</option>
                @foreach($assignedProjects as $project)
                    <option value="{{ $project->project_id }}" {{ request('project_id') == $project->project_id ? 'selected' : '' }}>{{ $project->project_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-muted">Construction Phase</label>
            <select name="phase_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Phases</option>
                @foreach($filterPhases as $phase)
                    <option value="{{ $phase->phase_id }}" {{ request('phase_id') == $phase->phase_id ? 'selected' : '' }}>{{ $phase->phase_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2">
            <label class="form-label small fw-bold text-muted">Approval Status</label>
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Returned</option>
            </select>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label small fw-bold text-muted">Report Date</label>
            <input type="date" name="report_date" value="{{ request('report_date') }}" class="form-control form-control-sm" onchange="this.form.submit()" />
        </div>
    </form>
</section>

<div class="row g-3 mb-4">
    <div class="col-6 col-sm-6 col-xl-3">
        <div class="metric-card p-3 d-flex align-items-center gap-3">
            <div class="metric-icon-wrapper bg-success-subtle text-success">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold">Total Reports</div>
                <h4 class="mb-0 fw-bold">{{ $totalCount }}</h4>
                <span class="text-muted" style="font-size: 0.75rem;">All time</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-xl-3">
        <div class="metric-card p-3 d-flex align-items-center gap-3">
            <div class="metric-icon-wrapper bg-warning-subtle text-warning">
                <i class="bi bi-clock"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold">Pending Review</div>
                <h4 class="mb-0 fw-bold">{{ $pendingCount }}</h4>
                <span class="text-muted" style="font-size: 0.75rem;">{{ $totalCount > 0 ? round(($pendingCount/$totalCount)*100, 2) : 0 }}% of total</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-xl-3">
        <div class="metric-card p-3 d-flex align-items-center gap-3">
            <div class="metric-icon-wrapper bg-success-subtle text-success">
                <i class="bi bi-check-circle"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold">Approved Reports</div>
                <h4 class="mb-0 fw-bold">{{ $approvedCount }}</h4>
                <span class="text-muted" style="font-size: 0.75rem;">{{ $totalCount > 0 ? round(($approvedCount/$totalCount)*100, 2) : 0 }}% of total</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-xl-3">
        <div class="metric-card p-3 d-flex align-items-center gap-3">
            <div class="metric-icon-wrapper bg-danger-subtle text-danger">
                <i class="bi bi-x-circle"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold">Rejected Reports</div>
                <h4 class="mb-0 fw-bold">{{ $rejectedCount }}</h4>
                <span class="text-muted" style="font-size: 0.75rem;">{{ $totalCount > 0 ? round(($rejectedCount/$totalCount)*100, 2) : 0 }}% of total</span>
            </div>
        </div>
    </div>
</div>

<section class="main-report-card p-0 overflow-hidden mb-4">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0" style="color: var(--cms-green-dark) !important;">Accomplishment Reports</h5>
        <button class="btn btn-sm btn-success" style="background-color: var(--cms-green-dark); border: none;" data-bs-toggle="modal" data-bs-target="#createReportModal" {{ $assignedProjects->isEmpty() ? 'disabled' : '' }}>
            + New Report
        </button>
    </div>

    <div class="table-responsive supervisor-report-table-wrap">
        <table class="table table-report table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Report Date</th>
                    <th>Project</th>
                    <th>Phase</th>
                    <th>Submitted By</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($reports->isEmpty())
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No configuration records matched your parameters.</td>
                    </tr>
                @else
                    @foreach($reports as $report)
                        @php
                            $status = $report->approval_status ?? 'pending';
                            $pillClass = match ($status) {
                                'approved' => 'status-pill status-pill-approved',
                                'rejected' => 'status-pill status-pill-error',
                                default => 'status-pill status-pill-pending',
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ optional($report->report_date)->format('M d, Y') ?? 'N/A' }}</div>
                                <div class="text-muted small">{{ optional($report->report_date)->format('h:i A') ?? '' }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ optional($report->project)->project_name ?? 'Unknown' }}</div>
                                <div class="text-muted small">Building Construction</div>
                            </td>
                            <td>
                                <span class="text-dark fw-semibold">{{ optional($report->phase)->phase_name ?? 'General Phase' }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-img bg-secondary text-white d-flex align-items-center justify-content-center fw-bold small">
                                        {{ strtoupper(substr(optional($report->submittedBy)->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:0.85rem;">{{ optional($report->submittedBy)->name ?? 'Supervisor' }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Supervisor</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-pill {{ $pillClass }}">{{ $status }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#reportDetailsModal-{{ $report->report_id }}" style="background: white; color: var(--cms-green-dark); transition: all 0.2s ease;" onmouseover="this.style.color='var(--cms-green-dark)'; this.style.transform='scale(1.2)';" onmouseout="this.style.color='var(--cms-green-dark)'; this.style.transform='scale(1)';">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm download-report-btn" data-report-id="{{ $report->report_id }}" style="background: white; color: var(--cms-green-dark); transition: all 0.2s ease;" onmouseover="this.style.color='var(--cms-green-dark)'; this.style.transform='scale(1.2)';" onmouseout="this.style.color='var(--cms-green-dark)'; this.style.transform='scale(1)';">
                                        <i class="bi bi-download"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        @php
                            $siteImages = is_array($report->site_images) ? $report->site_images : [];
                            $siteImageUrls = collect($siteImages)
                                ->map(function ($path) {
                                    if (!$path) {
                                        return null;
                                    }
                                    return asset('storage/' . ltrim($path, '/'));
                                })
                                ->filter()
                                ->values();
                            $timelineStatus = $status === 'approved' ? 'active' : ($status === 'rejected' ? 'active' : 'current');
                        @endphp
                        <div class="modal fade report-details-modal" id="reportDetailsModal-{{ $report->report_id }}" tabindex="-1" aria-labelledby="reportDetailsModalLabel-{{ $report->report_id }}" aria-hidden="true">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header" style="background: #ffffff; border-bottom: 2px solid var(--cms-green-dark);">
                                        <div>
                                            <h5 class="modal-title fw-bold" id="reportDetailsModalLabel-{{ $report->report_id }}" style="color: var(--cms-green-dark);">Report Details</h5>
                                            <div class="text-muted small">A complete summary of the selected accomplishment report.</div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body py-4">
                                        <div class="row gx-4 gy-4">
                                            <div class="col-12 col-xl-7">
                                                <div class="report-detail-card p-4">
                                                    <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 mb-4 p-3 rounded-3" style="background: #fff;">
                                                        <div>
                                                            <div class="small text-uppercase text-muted" style="font-weight: 600;">Report ID</div>
                                                            <div class="fw-bold text-dark" style="font-size: 1.1rem;">RPT-2026-{{ str_pad($report->report_id, 4, '0', STR_PAD_LEFT) }}</div>
                                                        </div>
                                                        <div class="text-sm-end">
                                                            <div class="small text-uppercase text-muted" style="font-weight: 600;">Approval Status</div>
                                                            <span class="status-pill {{ $pillClass }} p-2 mt-1 d-inline-block">{{ $status }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="row g-3 mb-4 small">
                                                        <div class="col-12 col-sm-6 p-3 rounded" style="background: #f9fafb;">
                                                            <div class="fw-semibold text-muted mb-1">Project</div>
                                                            <div class="text-dark">{{ optional($report->project)->project_name ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-12 col-sm-6 p-3 rounded" style="background: #f9fafb;">
                                                            <div class="fw-semibold text-muted mb-1">Construction Phase</div>
                                                            <div class="text-dark">{{ optional($report->phase)->phase_name ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-12 col-sm-6 p-3 rounded" style="background: #f9fafb;">
                                                            <div class="fw-semibold text-muted mb-1">Report Date</div>
                                                            <div class="text-dark">{{ optional($report->report_date)->format('M d, Y h:i A') ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-12 col-sm-6 p-3 rounded" style="background: #f9fafb;">
                                                            <div class="fw-semibold text-muted mb-1">Submitted By</div>
                                                            <div class="text-dark">{{ optional($report->submittedBy)->name ?? 'Supervisor' }}</div>
                                                        </div>
                                                    </div>

                                                    <div class="p-4 rounded-3 mb-4" style="white-space: pre-line; line-height: 1.7; background: #f9fafb;">
                                                        <div class="fw-bold mb-2" style="color: var(--cms-green-dark);">Construction Accomplishment</div>
                                                        <p class="mb-0 text-dark small">{{ $report->report_text ?? 'No description logs reported.' }}</p>
                                                    </div>

                                                    <div class="row g-3 mb-3">
                                                        <div class="col-12 col-md-6">
                                                            <div class="p-3 rounded-3" style="background: #f9fafb;">
                                                                <div class="fw-semibold text-muted mb-1">Reviewed By</div>
                                                                <div class="text-dark">{{ optional($report->reviewedBy)->name ?? 'Pending review' }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <div class="p-3 rounded-3" style="background: #f9fafb;">
                                                                <div class="fw-semibold text-muted mb-1">Approved By</div>
                                                                <div class="text-dark">{{ optional($report->approvedBy)->name ?? 'Pending approval' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="p-3 rounded-3" style="background: #f9fafb;">
                                                        <div class="fw-semibold text-muted mb-1">Approval Remarks</div>
                                                        <div class="text-dark small">{{ $report->approval_remarks ?? 'No remarks' }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 col-xl-5">
                                                <div class="report-detail-sidebar p-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <div class="fw-bold" style="color: var(--cms-green-dark);">Site Images</div>
                                                        <div class="small text-muted">{{ $siteImageUrls->count() }} uploaded</div>
                                                    </div>
                                                    @if($siteImageUrls->isEmpty())
                                                        <div class="text-muted small border rounded-3 p-3" style="background: #f9fafb;">No site images were attached to this report.</div>
                                                    @else
                                                        <div class="d-flex flex-wrap gap-2 mb-4">
                                                            @foreach($siteImageUrls->take(4) as $imageUrl)
                                                                <button type="button" class="img-thumbnail-grid d-flex align-items-center justify-content-center overflow-hidden p-0 lightbox-trigger" style="background: #f9fafb; border: 2px solid #e5e7eb; width: 72px; height: 72px;" data-full-image="{{ $imageUrl }}" aria-label="Preview site image">
                                                                    <img src="{{ $imageUrl }}" alt="Site image" class="w-100 h-100 object-fit-cover">
                                                                </button>
                                                            @endforeach
                                                            @if($siteImageUrls->count() > 4)
                                                                <div class="more-images-badge d-flex align-items-center justify-content-center" style="background: #f9fafb; border: 2px solid #e5e7eb; color: #6b7280;">+{{ $siteImageUrls->count() - 4 }} more</div>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    <div class="fw-bold mb-3" style="color: var(--cms-green-dark);">Approval Timeline</div>
                                                    <div class="timeline-container small px-1">
                                                        <div class="timeline-step active">
                                                            <div class="timeline-icon"><i class="bi bi-check"></i></div>
                                                            <div class="fw-bold" style="font-size:0.75rem;">Submitted</div>
                                                        </div>
                                                        <div class="timeline-step {{ $status !== 'pending' ? 'active' : 'current' }}">
                                                            <div class="timeline-icon"><i class="bi bi-clock"></i></div>
                                                            <div class="fw-bold" style="font-size:0.75rem;">Under Review</div>
                                                        </div>
                                                        <div class="timeline-step {{ $status === 'approved' ? 'active' : '' }}">
                                                            <div class="timeline-icon"><i class="bi bi-circle"></i></div>
                                                            <div class="fw-bold" style="font-size:0.75rem;">Approved</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-center" style="padding-top: 2rem; margin-top: 2rem; border-top: 2px solid var(--cms-green-muted);">
                                                    <button class="btn btn-cms-primary download-report-btn" data-report-id="{{ $report->report_id }}">
                                                        <i class="bi bi-download me-2"></i> Download PDF
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <div class="supervisor-report-mobile-list">
        @if($reports->isEmpty())
            <div class="supervisor-report-empty">
                <i class="bi bi-file-earmark-x"></i>
                <div>No reports match the current filters.</div>
            </div>
        @else
            @foreach($reports as $report)
                @php
                    $status = $report->approval_status ?? 'pending';
                    $pillClass = match ($status) {
                        'approved' => 'status-pill status-pill-approved',
                        'rejected' => 'status-pill status-pill-error',
                        default => 'status-pill status-pill-pending',
                    };
                    $reportDate = optional($report->report_date)->format('M d, Y') ?? 'N/A';
                    $reportTime = optional($report->report_date)->format('h:i A') ?? '';
                    $projectName = optional($report->project)->project_name ?? 'Unknown';
                    $phaseName = optional($report->phase)->phase_name ?? 'General Phase';
                    $submittedName = optional($report->submittedBy)->name ?? 'Supervisor';
                @endphp

                <article class="supervisor-report-mobile-card">
                    <div class="report-mobile-card-top">
                        <div>
                            <span class="report-mobile-label">Report Date</span>
                            <strong>{{ $reportDate }}</strong>
                            @if($reportTime)
                                <small>{{ $reportTime }}</small>
                            @endif
                        </div>
                        <span class="{{ $pillClass }}">{{ ucfirst($status) }}</span>
                    </div>

                    <div class="report-mobile-title">
                        {{ $projectName }}
                    </div>

                    <div class="report-mobile-detail-grid">
                        <div class="report-mobile-detail">
                            <span>Project</span>
                            <strong>{{ $projectName }}</strong>
                            <small>Building Construction</small>
                        </div>

                        <div class="report-mobile-detail">
                            <span>Phase</span>
                            <strong>{{ $phaseName }}</strong>
                        </div>

                        <div class="report-mobile-detail">
                            <span>Submitted By</span>
                            <div class="report-mobile-person">
                                <div class="avatar-img bg-secondary text-white d-flex align-items-center justify-content-center fw-bold small">
                                    {{ strtoupper(substr($submittedName, 0, 1)) }}
                                </div>
                                <div>
                                    <strong>{{ $submittedName }}</strong>
                                    <small>Supervisor</small>
                                </div>
                            </div>
                        </div>

                        <div class="report-mobile-detail">
                            <span>Report ID</span>
                            <strong>RPT-2026-{{ str_pad($report->report_id, 4, '0', STR_PAD_LEFT) }}</strong>
                        </div>
                    </div>

                    <div class="report-mobile-actions">
                        <button class="btn report-mobile-action-btn"
                                type="button"
                                data-bs-toggle="modal"
                                data-bs-target="#reportDetailsModal-{{ $report->report_id }}">
                            <i class="bi bi-eye"></i>
                            View Details
                        </button>
                        <button class="btn report-mobile-action-btn report-mobile-action-secondary download-report-btn"
                                type="button"
                                data-report-id="{{ $report->report_id }}">
                            <i class="bi bi-download"></i>
                            PDF
                        </button>
                    </div>
                </article>
            @endforeach
        @endif
    </div>


    <div class="p-3 bg-light d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 border-top">
        <div class="small text-muted">
            Showing {{ $reports->firstItem() ?? 0 }} to {{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }} reports
        </div>
        <div>
            @if($reports->hasPages())
                {{ $reports->appends(request()->only(['project_id', 'phase_id', 'status', 'report_date', 'report_date_from', 'report_date_to', 'search']))->links('pagination::bootstrap-5') }}
            @else
                <nav aria-label="Report pagination" class="pagination">
                    <span class="page-item active"><span class="page-link">1</span></span>
                </nav>
            @endif
        </div>
    </div>
</section>

<div class="modal fade cms-modal" id="createReportModal" tabindex="-1" aria-labelledby="createReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="createReportModalLabel">Create Accomplishment Report</h5>
                    <p class="cms-modal-subtitle modal-subtitle">Fill out the form below to document and submit daily construction progress.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="createReportForm" action="{{ route('supervisor.reports.submit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    
                    <div class="cms-form-section-header">Project Context</div>
                    <div class="row">
                                <div class="col-12 col-md-6 cms-form-group">
                            <label for="modal_project_id" class="cms-form-label">Project Assignment <span class="text-danger">*</span></label>
                            <select name="project_id" id="modal_project_id" class="form-select cms-form-control" required>
                                @if($assignedProjects->isEmpty())
                                    <option value="" selected disabled>No assigned projects available</option>
                                @else
                                    <option value="" disabled>Select assigned project...</option>
                                    @foreach($assignedProjects as $project)
                                        <option value="{{ $project->project_id }}" {{ optional($modalProject ?? $selectedProject)->project_id == $project->project_id ? 'selected' : '' }}>{{ $project->project_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-12 col-md-6 cms-form-group">
                            <label for="modal_phase_id" class="cms-form-label">Construction Phase <span class="text-danger">*</span></label>
                            <select name="phase_id" id="modal_phase_id" class="form-select cms-form-control" required>
                                <option value="" selected disabled>{{ $projectPhases->isEmpty() ? 'Select project first...' : 'Select construction phase...' }}</option>
                                @foreach($projectPhases as $phase)
                                    <option value="{{ $phase->phase_id }}" data-completion-percentage="{{ $phase->completion_percentage ?? 0 }}">{{ $phase->phase_name }}</option>
                                @endforeach
                            </select>
                            @if($projectPhases->isEmpty())
                                <div class="text-muted small mt-1">No phases are available for the selected project.</div>
                            @endif
                        </div>
                        <div class="col-12 col-md-6 cms-form-group">
                            <label for="modal_report_date" class="cms-form-label">Report Date <span class="text-danger">*</span></label>
                            <input type="date" name="report_date" id="modal_report_date" class="cms-form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-md-6 cms-form-group">
                            <label for="modal_report_text" class="cms-form-label">Accomplishment Summary <span class="text-danger">*</span></label>
                            <textarea name="report_text" id="modal_report_text" rows="4" class="cms-form-control" placeholder="Enter the accomplishment report text for this project and phase." required></textarea>
                        </div>
                    </div>

                    <div class="cms-form-section-header">Upload Site Images <span class="text-muted">(Optional)</span></div>
                    <div class="row">
                        <div class="col-12 cms-form-group">
                            <div id="imageUploadZone" class="cms-file-upload-zone" onclick="document.getElementById('modal_report_images').click()">
                                <div id="uploadPromptText">
                                    <div class="cms-file-upload-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">Click to upload or drag files here</h6>
                                    <p class="text-muted mb-0 small">Supports PNG, JPG, JPEG, WEBP formats up to 5MB per image.</p>
                                </div>
                                <div id="selectedImagesContainer" class="cms-file-preview-grid"></div>
                                <input type="file" name="site_images[]" id="modal_report_images" class="d-none" multiple accept="image/png,image/jpeg,image/jpg,image/webp" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cms-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-cms-primary d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-check"></i> Submit Accomplishment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Image Lightbox Modal --}}
<div class="image-lightbox" id="reportImageLightbox" role="dialog" aria-modal="true" aria-label="Image preview">
    <button type="button" class="image-lightbox-close" id="lightboxCloseBtn" aria-label="Close preview">&times;</button>
    <img src="" alt="Site image preview" id="lightboxImage">
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reportModals = document.querySelectorAll('.report-details-modal');
        reportModals.forEach(function (modal) {
            document.body.appendChild(modal);
        });

        const modalProjectSelect = document.getElementById('modal_project_id');
        const modalPhaseSelect = document.getElementById('modal_phase_id');
        const imageInput = document.getElementById('modal_report_images');
        const previewContainer = document.getElementById('selectedImagesContainer');
        const filterForm = document.getElementById('filterForm');
        const createReportForm = document.getElementById('createReportForm');

        const phasesApiRouteTemplate = '{{ route('supervisor.api.reports.phases', ['project_id' => 'PROJECT_ID']) }}';
        const phasePlaceholder = '<option value="" selected disabled>Select construction phase...</option>';
        const loadingPlaceholder = '<option value="" selected disabled>Loading phases...</option>';
        const errorPlaceholder = '<option value="" disabled>Error loading phases.</option>';
        const emptyPlaceholder = '<option value="" disabled>No phases available.</option>';

        function enablePhaseSelect() {
            modalPhaseSelect.disabled = false;
            modalPhaseSelect.removeAttribute('disabled');
            modalPhaseSelect.style.pointerEvents = 'auto';
            modalPhaseSelect.style.cursor = 'pointer';
        }

        function disablePhaseSelect() {
            modalPhaseSelect.disabled = true;
            modalPhaseSelect.setAttribute('disabled', 'disabled');
            modalPhaseSelect.style.pointerEvents = 'none';
            modalPhaseSelect.style.cursor = 'not-allowed';
        }

        function renderPhaseOptions(phases) {
            modalPhaseSelect.innerHTML = phasePlaceholder;
            if (!phases || phases.length === 0) {
                modalPhaseSelect.innerHTML = emptyPlaceholder;
                enablePhaseSelect();
                return;
            }
            phases.forEach(phase => {
                const option = document.createElement('option');
                option.value = phase.phase_id;
                option.textContent = phase.phase_name;
                modalPhaseSelect.appendChild(option);
            });
            enablePhaseSelect();
        }

        function loadProjectPhases(projectId) {
            modalPhaseSelect.innerHTML = loadingPlaceholder;
            enablePhaseSelect();

            const endpoint = phasesApiRouteTemplate.replace('PROJECT_ID', encodeURIComponent(projectId));

            fetch(endpoint, {
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Phase load failed');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && Array.isArray(data.phases)) {
                        renderPhaseOptions(data.phases);
                        return;
                    }
                    modalPhaseSelect.innerHTML = errorPlaceholder;
                    enablePhaseSelect();
                })
                .catch(() => {
                    modalPhaseSelect.innerHTML = errorPlaceholder;
                    enablePhaseSelect();
                });
        }

        function initializePhaseDropdown() {
            if (modalProjectSelect && modalProjectSelect.value) {
                loadProjectPhases(modalProjectSelect.value);
            } else {
                modalPhaseSelect.innerHTML = emptyPlaceholder;
                enablePhaseSelect();
            }
        }

        if (modalProjectSelect) {
            modalProjectSelect.addEventListener('change', function() {
                const projectId = this.value;
                if (!projectId) {
                    disablePhaseSelect();
                    return;
                }
                loadProjectPhases(projectId);
            });
        }

        if (modalPhaseSelect) {
            modalPhaseSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const completionPercentage = selectedOption?.dataset?.completionPercentage ?? 0;
                updateCurrentPhaseProgress({ completion_percentage: completionPercentage, phase_name: selectedOption?.textContent || 'this phase' });
            });
        }

        const createReportModal = document.getElementById('createReportModal');
        if (createReportModal) {
            createReportModal.addEventListener('shown.bs.modal', function () {
                initializePhaseDropdown();
            });
        }

        initializePhaseDropdown();

        let selectedFiles = [];
        const uploadZone = document.getElementById('imageUploadZone');
        const uploadPromptText = document.getElementById('uploadPromptText');

        function renderImagePreviews(files) {
            previewContainer.innerHTML = '';
            if (!files || files.length === 0) {
                uploadZone.classList.remove('has-images');
                return;
            }

            uploadZone.classList.add('has-images');
            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) {
                    return;
                }
                const previewThumb = document.createElement('div');
                previewThumb.className = 'cms-file-preview-thumb';

                const img = document.createElement('img');
                img.alt = file.name;
                img.src = URL.createObjectURL(file);
                img.onload = () => URL.revokeObjectURL(img.src);

                const label = document.createElement('div');
                label.className = 'preview-label';
                label.textContent = file.name;

                previewThumb.appendChild(img);
                previewThumb.appendChild(label);
                previewContainer.appendChild(previewThumb);
            });
        }

        function updateImageInputFiles() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            imageInput.files = dataTransfer.files;
            renderImagePreviews(selectedFiles);
        }

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) {
                    return;
                }
                const exists = selectedFiles.some(existing => existing.name === file.name && existing.size === file.size && existing.type === file.type);
                if (!exists) {
                    selectedFiles.push(file);
                }
            });
            updateImageInputFiles();
        }

        imageInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        if (uploadZone) {
            uploadZone.addEventListener('dragenter', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadZone.classList.add('dragover');
            });
            uploadZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadZone.classList.add('dragover');
            });
            uploadZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadZone.classList.remove('dragover');
            });
            uploadZone.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadZone.classList.remove('dragover');
                if (e.dataTransfer && e.dataTransfer.files.length) {
                    handleFiles(e.dataTransfer.files);
                }
            });
        }

        if (createReportForm) {
            createReportForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!modalProjectSelect.value) {
                    Swal.fire({
                        title: 'Project Required',
                        text: 'You must select an assigned project before submitting a report.',
                        icon: 'warning',
                        confirmButtonColor: '#166534',
                        customClass: { confirmButton: 'btn-cms-primary' },
                        buttonsStyling: false,
                    });
                    return;
                }

                if (modalPhaseSelect.disabled || !modalPhaseSelect.value) {
                    Swal.fire({
                        title: 'Phase Required',
                        text: 'Please select a construction phase for this project before submitting.',
                        icon: 'warning',
                        confirmButtonColor: '#166534',
                        customClass: { confirmButton: 'btn-cms-primary' },
                        buttonsStyling: false,
                    });
                    return;
                }

                Swal.fire({
                    title: 'Confirm Submission',
                    text: 'Submit this accomplishment report for review?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, submit',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#166534',
                    cancelButtonColor: '#6c757d',
                    customClass: {
                        confirmButton: 'btn-cms-primary',
                        cancelButton: 'btn-cms-secondary'
                    },
                    buttonsStyling: false,
                }).then(result => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    const formData = new FormData(createReportForm);
                    Swal.fire({
                        title: 'Submitting report...',
                        html: 'Please wait while your report is uploaded.',
                        didOpen: () => Swal.showLoading(),
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                    });

                    fetch(createReportForm.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        },
                        body: formData,
                    })
                        .then(async response => {
                            const data = await response.json().catch(() => null);
                            if (!response.ok) {
                                let message = 'Server error while submitting the report.';
                                if (data) {
                                    if (data.message) {
                                        message = data.message;
                                    } else if (data.errors) {
                                        message = Object.values(data.errors).flat().join(' ');
                                    }
                                }
                                throw { message };
                            }
                            return data;
                        })
                        .then(data => {
                            Swal.fire({
                                title: 'Report Submitted',
                                text: 'Your accomplishment report was submitted successfully.',
                                icon: 'success',
                                confirmButtonColor: '#166534',
                            }).then(() => {
                                window.location.reload();
                            });
                        })
                        .catch(error => {
                            const message = (error && (error.message || error.error || 'Something went wrong.')) || 'Something went wrong.';
                            Swal.fire({
                                title: 'Submission Failed',
                                text: message,
                                icon: 'error',
                                confirmButtonColor: '#c92a2a',
                            });
                        });
                });
            });
        }

        if (filterForm) {
            const filterControls = filterForm.querySelectorAll('select[name="project_id"], select[name="phase_id"], select[name="status"], input[name="report_date"]');
            filterControls.forEach(control => {
                control.addEventListener('change', function() {
                    filterForm.submit();
                });
            });
        }

        // Image lightbox for report details modal
        const lightbox = document.getElementById('reportImageLightbox');
        const lightboxImage = document.getElementById('lightboxImage');
        const lightboxCloseBtn = document.getElementById('lightboxCloseBtn');

        function openLightbox(imageUrl) {
            if (!lightbox || !lightboxImage) return;
            lightboxImage.src = imageUrl;
            lightbox.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            if (!lightbox) return;
            lightbox.classList.remove('is-open');
            document.body.style.overflow = '';
            if (lightboxImage) {
                setTimeout(() => { lightboxImage.src = ''; }, 200);
            }
        }

        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('.lightbox-trigger');
            if (trigger) {
                const fullImage = trigger.dataset.fullImage || trigger.querySelector('img')?.src;
                if (fullImage) {
                    openLightbox(fullImage);
                }
            }
        });

        lightboxCloseBtn?.addEventListener('click', closeLightbox);
        lightbox?.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && lightbox?.classList.contains('is-open')) {
                closeLightbox();
            }
        });
    });
</script>
@endpush