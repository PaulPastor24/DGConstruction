```blade
@extends('layouts.admin')

@section('title', 'Progress Reports - D&G Construction Monitor')
@section('page_title', 'Progress Reports')

@push('styles')
<style>
    #pg-reports {
        --report-green: #20b866;
        --report-green-dark: #15834a;
        --report-green-soft: #eef9f3;

        --report-blue: #3b82f6;
        --report-blue-dark: #2563eb;
        --report-blue-soft: #eff6ff;

        --report-amber: #f59e0b;
        --report-amber-dark: #b76b00;
        --report-amber-soft: #fff8e8;

        --report-red: #ef4444;
        --report-red-dark: #dc2626;
        --report-red-soft: #fff1f2;

        --report-dark: #17211b;
        --report-text: #3f4a43;
        --report-muted: #778078;
        --report-border: #e5e9e6;
        --report-page: #f4f6f4;
        --report-white: #ffffff;

        --report-shadow:
            0 10px 30px rgba(17, 45, 27, 0.055);

        color: var(--report-dark);

        font-family:
            Inter,
            ui-sans-serif,
            system-ui,
            -apple-system,
            BlinkMacSystemFont,
            "Segoe UI",
            Roboto,
            Helvetica,
            Arial,
            sans-serif;

        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-rendering: optimizeLegibility;

        padding-bottom: 2rem;
    }

    #pg-reports *,
    #pg-reports *::before,
    #pg-reports *::after {
        box-sizing: border-box;
        font-family: inherit;
    }

    /* ================================
       SUMMARY CARDS
    ================================ */

    .report-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.4rem;
    }

    .report-summary-card {
        position: relative;
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr);
        gap: 0.9rem;
        align-items: center;
        min-height: 140px;
        padding: 1.25rem;
        overflow: hidden;
        border: 1px solid var(--report-border);
        border-radius: 16px;
        background: var(--report-white);
        box-shadow: var(--report-shadow);
        transition:
            transform 0.2s ease,
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .report-summary-card::before {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 3px;
        border-radius: 16px 0 0 16px;
        background: var(--summary-color);
    }

    .report-summary-card:hover {
        transform: translateY(-2px);
        border-color: #d9dfdb;
        box-shadow:
            0 14px 34px rgba(17, 45, 27, 0.075);
    }

    .report-summary-card.awaiting {
        --summary-color: var(--report-amber);
        --summary-soft: var(--report-amber-soft);
        --summary-dark: var(--report-amber-dark);
    }

    .report-summary-card.review {
        --summary-color: var(--report-blue);
        --summary-soft: var(--report-blue-soft);
        --summary-dark: var(--report-blue-dark);
    }

    .report-summary-card.approved {
        --summary-color: var(--report-green);
        --summary-soft: var(--report-green-soft);
        --summary-dark: var(--report-green-dark);
    }

    .report-summary-card.revision {
        --summary-color: var(--report-red);
        --summary-soft: var(--report-red-soft);
        --summary-dark: var(--report-red-dark);
    }

    .report-summary-icon {
        display: grid;
        place-items: center;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: var(--summary-soft);
        color: var(--summary-color);
        font-size: 1.15rem;
    }

    .report-summary-content {
        min-width: 0;
    }

    .report-summary-label {
        margin-bottom: 0.4rem;
        color: var(--report-muted);
        font-size: 0.62rem;
        font-weight: 600;
        letter-spacing: 0.055em;
        line-height: 1.35;
        text-transform: uppercase;
    }

    .report-summary-value {
        color: var(--report-dark);
        font-size: 1.65rem;
        font-weight: 700;
        letter-spacing: -0.035em;
        line-height: 1;
    }

    .report-summary-helper {
        margin-top: 0.6rem;
        color: var(--report-muted);
        font-size: 0.68rem;
        font-weight: 400;
        line-height: 1.45;
    }

    .report-summary-helper.highlight {
        color: var(--summary-dark);
        font-weight: 500;
    }

    /* ================================
       COMMON CARD
    ================================ */

    .report-card {
        overflow: hidden;
        border: 1px solid var(--report-border);
        border-radius: 16px;
        background: var(--report-white);
        box-shadow: var(--report-shadow);
    }

    .report-card + .report-card,
    .report-card + .report-information-panel {
        margin-top: 1.4rem;
    }

    .report-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-height: 62px;
        padding: 1rem 1.35rem;
        border-bottom: 1px solid var(--report-border);
    }

    .report-card-title {
        margin: 0;
        color: var(--report-dark);
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        line-height: 1.4;
    }

    .report-count-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        min-height: 28px;
        padding: 0.4rem 0.7rem;
        border: 1px solid rgba(245, 158, 11, 0.2);
        border-radius: 999px;
        background: var(--report-amber-soft);
        color: var(--report-amber-dark);
        font-size: 0.62rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .report-count-badge::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--report-amber);
    }

    /* ================================
       SUBMISSIONS TABLE
    ================================ */

    .report-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .report-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
        color: var(--report-text);
        font-size: 0.75rem;
    }

    .report-table thead {
        background: #fbfcfb;
    }

    .report-table th {
        padding: 0.95rem 1.35rem;
        border-bottom: 1px solid var(--report-border);
        color: var(--report-muted);
        font-size: 0.58rem;
        font-weight: 600;
        letter-spacing: 0.065em;
        line-height: 1.3;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .report-table td {
        padding: 1rem 1.35rem;
        border-bottom: 1px solid #edf0ee;
        vertical-align: middle;
    }

    .report-table tbody tr {
        transition:
            background-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .report-table tbody tr:hover {
        background: #fafcfb;
    }

    .report-table tbody tr.selected-report-row {
        background: var(--report-green-soft);
        box-shadow: inset 3px 0 0 var(--report-green);
    }

    .report-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .report-project-name {
        color: var(--report-dark);
        font-weight: 600;
    }

    .report-secondary-text {
        color: var(--report-muted);
        font-weight: 400;
    }

    .phase-badge {
        display: inline-flex;
        align-items: center;
        min-height: 27px;
        padding: 0.38rem 0.6rem;
        border: 1px solid var(--report-border);
        border-radius: 7px;
        background: #fafbfa;
        color: var(--report-text);
        font-size: 0.62rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        min-height: 27px;
        padding: 0.38rem 0.6rem;
        border-radius: 7px;
        font-size: 0.6rem;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
    }

    .status-badge::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .status-badge.awaiting {
        background: var(--report-amber-soft);
        color: var(--report-amber-dark);
    }

    .status-badge.in-review {
        background: var(--report-blue-soft);
        color: var(--report-blue-dark);
    }

    .status-badge.approved {
        background: var(--report-green-soft);
        color: var(--report-green-dark);
    }

    .status-badge.revision {
        background: var(--report-red-soft);
        color: var(--report-red-dark);
    }

    .open-report-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        min-height: 32px;
        padding: 0.45rem 0.8rem;
        border: 1px solid var(--report-border);
        border-radius: 8px;
        background: var(--report-white);
        color: var(--report-dark);
        font-size: 0.65rem;
        font-weight: 600;
        line-height: 1;
        text-decoration: none;
        transition:
            color 0.2s ease,
            background 0.2s ease,
            border-color 0.2s ease,
            transform 0.2s ease;
    }

    .open-report-button:hover {
        color: var(--report-green-dark);
        border-color: rgba(32, 184, 102, 0.35);
        background: var(--report-green-soft);
        transform: translateY(-1px);
    }

    /* ================================
       TABLE EMPTY STATE
    ================================ */

    .report-empty-cell {
        padding: 0 !important;
    }

    .report-empty-state {
        display: grid;
        place-items: center;
        min-height: 245px;
        padding: 2rem;
        color: var(--report-muted);
        text-align: center;
    }

    .report-empty-content {
        max-width: 530px;
    }

    .report-empty-icon {
        display: grid;
        place-items: center;
        width: 58px;
        height: 58px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        background: #f3f5f4;
        color: #9ba39d;
        font-size: 1.35rem;
    }

    .report-empty-title {
        margin: 0;
        color: var(--report-dark);
        font-size: 0.8rem;
        font-weight: 600;
        line-height: 1.5;
    }

    .report-empty-description {
        margin: 0.35rem 0 0;
        color: var(--report-muted);
        font-size: 0.68rem;
        font-weight: 400;
        line-height: 1.6;
    }

    /* ================================
       INFORMATION PANEL
    ================================ */

    .report-information-panel {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr);
        align-items: center;
        gap: 1rem;
        min-height: 98px;
        padding: 1.2rem 1.4rem;
        border: 1px solid var(--report-border);
        border-radius: 14px;
        background: var(--report-white);
        box-shadow: var(--report-shadow);
    }

    .report-information-icon {
        display: grid;
        place-items: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--report-green-soft);
        color: var(--report-green-dark);
        font-size: 0.95rem;
    }

    .report-information-text {
        margin: 0;
        color: var(--report-muted);
        font-size: 0.7rem;
        font-weight: 400;
        line-height: 1.7;
        text-align: center;
    }

    /* ================================
       SELECTED REPORT AREA
    ================================ */

    .selected-report-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.4rem;
        margin-top: 1.4rem;
    }

    .selected-report-card {
        min-width: 0;
    }

    .selected-report-card .report-card-header {
        min-height: 60px;
    }

    .selected-project-badge {
        display: inline-flex;
        align-items: center;
        min-height: 27px;
        max-width: 220px;
        padding: 0.38rem 0.62rem;
        overflow: hidden;
        border-radius: 7px;
        background: #f3f5f4;
        color: var(--report-text);
        font-size: 0.62rem;
        font-weight: 500;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .report-card-body {
        padding: 1.3rem;
    }

    .report-details-list {
        display: flex;
        flex-direction: column;
    }

    .report-detail-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.85rem 0;
        border-bottom: 1px solid var(--report-border);
    }

    .report-detail-row:first-child {
        padding-top: 0;
    }

    .report-detail-row:last-of-type {
        border-bottom: 0;
    }

    .report-detail-label {
        flex: 0 0 auto;
        color: var(--report-muted);
        font-size: 0.68rem;
        font-weight: 400;
    }

    .report-detail-value {
        color: var(--report-dark);
        font-size: 0.7rem;
        font-weight: 600;
        line-height: 1.5;
        text-align: right;
    }

    .report-detail-value.completion {
        color: var(--report-green-dark);
    }

    .report-notes {
        min-height: 120px;
        margin-top: 0.9rem;
        padding: 1rem;
        border: 1px solid var(--report-border);
        border-radius: 10px;
        background: #fafbfa;
        color: var(--report-text);
        font-size: 0.7rem;
        font-weight: 400;
        line-height: 1.7;
    }

    /* ================================
       ATTACHMENTS
    ================================ */

    .attachment-list {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    .attachment-item {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr) auto;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        border: 1px solid var(--report-border);
        border-radius: 10px;
        background: #fafbfa;
    }

    .attachment-icon {
        display: grid;
        place-items: center;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: var(--report-blue-soft);
        color: var(--report-blue);
        font-size: 0.95rem;
    }

    .attachment-link {
        min-width: 0;
        overflow: hidden;
        color: var(--report-dark);
        font-size: 0.68rem;
        font-weight: 600;
        line-height: 1.4;
        text-decoration: none;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .attachment-link:hover {
        color: var(--report-blue-dark);
        text-decoration: underline;
    }

    .attachment-size {
        color: var(--report-muted);
        font-size: 0.59rem;
        font-weight: 400;
        white-space: nowrap;
    }

    .attachment-empty {
        display: grid;
        place-items: center;
        min-height: 105px;
        padding: 1.2rem;
        border: 1px dashed #d5dcd7;
        border-radius: 10px;
        color: var(--report-muted);
        font-size: 0.66rem;
        line-height: 1.6;
        text-align: center;
    }

    /* ================================
       REVIEW FORM
    ================================ */

    .review-form {
        margin-top: 1.2rem;
        padding-top: 1.2rem;
        border-top: 1px solid var(--report-border);
    }

    .review-label {
        display: block;
        margin-bottom: 0.55rem;
        color: var(--report-muted);
        font-size: 0.59rem;
        font-weight: 600;
        letter-spacing: 0.055em;
        line-height: 1.4;
        text-transform: uppercase;
    }

    .review-textarea {
        display: block;
        width: 100%;
        min-height: 105px;
        padding: 0.85rem 0.95rem;
        resize: vertical;
        border: 1px solid var(--report-border);
        border-radius: 10px;
        background: var(--report-white);
        color: var(--report-dark);
        font-size: 0.7rem;
        font-weight: 400;
        line-height: 1.65;
        outline: none;
        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .review-textarea::placeholder {
        color: #a1a9a3;
    }

    .review-textarea:focus {
        border-color: rgba(32, 184, 102, 0.5);
        box-shadow: 0 0 0 4px rgba(32, 184, 102, 0.09);
    }

    .review-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 0.7rem;
        margin-top: 0.9rem;
    }

    .report-action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        min-height: 39px;
        padding: 0.65rem 1rem;
        border-radius: 9px;
        font-size: 0.68rem;
        font-weight: 600;
        line-height: 1;
        cursor: pointer;
        transition:
            transform 0.2s ease,
            background 0.2s ease,
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .report-action-button:hover {
        transform: translateY(-1px);
    }

    .report-action-button.revision {
        border: 1px solid var(--report-border);
        background: var(--report-white);
        color: var(--report-text);
    }

    .report-action-button.revision:hover {
        border-color: rgba(239, 68, 68, 0.3);
        background: var(--report-red-soft);
        color: var(--report-red-dark);
    }

    .report-action-button.approve {
        border: 1px solid var(--report-green);
        background: var(--report-green);
        color: var(--report-white);
        box-shadow: 0 7px 18px rgba(32, 184, 102, 0.18);
    }

    .report-action-button.approve:hover {
        border-color: var(--report-green-dark);
        background: var(--report-green-dark);
        box-shadow: 0 9px 22px rgba(32, 184, 102, 0.23);
    }

    /* ================================
       RESPONSIVE
    ================================ */

    @media (max-width: 1200px) {
        .report-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 992px) {
        .selected-report-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .report-summary-card {
            min-height: 120px;
            padding: 1rem;
        }

        .report-card-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .report-information-panel {
            grid-template-columns: 1fr;
            justify-items: center;
        }

        .report-detail-row {
            flex-direction: column;
            gap: 0.35rem;
        }

        .report-detail-value {
            text-align: left;
        }
    }

    @media (max-width: 560px) {
        .report-summary-grid {
            grid-template-columns: 1fr;
        }

        .report-summary-card {
            min-height: 110px;
        }

        .report-table th,
        .report-table td {
            padding-right: 1rem;
            padding-left: 1rem;
        }

        .review-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .report-action-button {
            width: 100%;
        }

        .attachment-item {
            grid-template-columns: 34px minmax(0, 1fr);
        }

        .attachment-size {
            grid-column: 2;
        }
    }
</style>
@endpush

@section('content')
<div class="page active" id="pg-reports">

    {{-- Summary Cards --}}
    <div class="report-summary-grid">

        <article class="report-summary-card awaiting">
            <div class="report-summary-icon">
                <i class="bi bi-clock"></i>
            </div>

            <div class="report-summary-content">
                <div class="report-summary-label">
                    Awaiting Review
                </div>

                <div class="report-summary-value">
                    {{ $queueCount['awaiting_review'] ?? 0 }}
                </div>

                <div class="report-summary-helper">
                    Submitted by supervisors today
                </div>
            </div>
        </article>

        <article class="report-summary-card review">
            <div class="report-summary-icon">
                <i class="bi bi-person-check"></i>
            </div>

            <div class="report-summary-content">
                <div class="report-summary-label">
                    In Review
                </div>

                <div class="report-summary-value">
                    {{ $queueCount['in_review'] ?? 0 }}
                </div>

                <div class="report-summary-helper">
                    Assigned to admin reviewers
                </div>
            </div>
        </article>

        <article class="report-summary-card approved">
            <div class="report-summary-icon">
                <i class="bi bi-check-lg"></i>
            </div>

            <div class="report-summary-content">
                <div class="report-summary-label">
                    Approved
                </div>

                <div class="report-summary-value">
                    {{ $queueCount['approved'] ?? 0 }}
                </div>

                <div class="report-summary-helper highlight">
                    This week
                </div>
            </div>
        </article>

        <article class="report-summary-card revision">
            <div class="report-summary-icon">
                <i class="bi bi-exclamation-lg"></i>
            </div>

            <div class="report-summary-content">
                <div class="report-summary-label">
                    Needs Revision
                </div>

                <div class="report-summary-value">
                    {{ $queueCount['needs_revision'] ?? 0 }}
                </div>

                <div class="report-summary-helper highlight">
                    Returned to supervisor
                </div>
            </div>
        </article>

    </div>

    {{-- Submission Queue --}}
    <section class="report-card">

        <div class="report-card-header">
            <h2 class="report-card-title">
                Supervisor Submissions Queue
            </h2>

            @if(isset($submissions) && count($submissions) > 0)
                <span class="report-count-badge">
                    {{ count($submissions) }}
                    {{ count($submissions) === 1 ? 'Pending' : 'Pending' }}
                </span>
            @endif
        </div>

        <div class="report-table-wrapper">
            <table class="report-table">

                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Supervisor</th>
                        <th>Submitted</th>
                        <th>Phase</th>
                        <th>Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($submissions ?? [] as $sub)

                        <tr class="{{
                            isset($selectedReport) &&
                            $selectedReport->id == $sub->id
                                ? 'selected-report-row'
                                : ''
                        }}">

                            <td>
                                <span class="report-project-name">
                                    {{ $sub->project_name }}
                                </span>
                            </td>

                            <td>
                                <span class="report-secondary-text">
                                    {{ $sub->supervisor_name }}
                                </span>
                            </td>

                            <td>
                                <span class="report-secondary-text">
                                    {{ \Carbon\Carbon::parse($sub->submitted_at)->format('M d, h:i A') }}
                                </span>
                            </td>

                            <td>
                                <span class="phase-badge">
                                    {{ $sub->phase_name }}
                                </span>
                            </td>

                            <td>
                                @if($sub->status === 'Awaiting Review')
                                    <span class="status-badge awaiting">
                                        Awaiting Review
                                    </span>

                                @elseif($sub->status === 'Approved')
                                    <span class="status-badge approved">
                                        Approved
                                    </span>

                                @elseif($sub->status === 'Needs Revision')
                                    <span class="status-badge revision">
                                        Needs Revision
                                    </span>

                                @else
                                    <span class="status-badge in-review">
                                        In Review
                                    </span>
                                @endif
                            </td>

                            <td style="text-align: right;">
                                <a
                                    href="?report_id={{ $sub->id }}"
                                    class="open-report-button">

                                    <span>Open</span>

                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="report-empty-cell">

                                <div class="report-empty-state">
                                    <div class="report-empty-content">

                                        <div class="report-empty-icon">
                                            <i class="bi bi-inbox"></i>
                                        </div>

                                        <h3 class="report-empty-title">
                                            No submissions waiting for review
                                        </h3>

                                        <p class="report-empty-description">
                                            No supervisor accomplishment logs or
                                            verification submittals are currently
                                            pending in the queue.
                                        </p>

                                    </div>
                                </div>

                            </td>
                        </tr>

                    @endforelse
                </tbody>

            </table>
        </div>
    </section>

    @if(isset($selectedReport))

        {{-- Selected Report Content --}}
        <div class="selected-report-grid">

            {{-- Report Details --}}
            <section class="report-card selected-report-card">

                <div class="report-card-header">
                    <h2 class="report-card-title">
                        Selected Report Details
                    </h2>

                    <span class="selected-project-badge">
                        {{ $selectedReport->project_name }}
                    </span>
                </div>

                <div class="report-card-body">

                    <div class="report-details-list">

                        <div class="report-detail-row">
                            <span class="report-detail-label">
                                Supervisor
                            </span>

                            <span class="report-detail-value">
                                {{ $selectedReport->supervisor_fullname }}
                            </span>
                        </div>

                        <div class="report-detail-row">
                            <span class="report-detail-label">
                                Report Period
                            </span>

                            <span class="report-detail-value">
                                {{ $selectedReport->period_range }}
                            </span>
                        </div>

                        <div class="report-detail-row">
                            <span class="report-detail-label">
                                Current Phase
                            </span>

                            <span class="report-detail-value">
                                {{ $selectedReport->phase_name }}
                            </span>
                        </div>

                        <div class="report-detail-row">
                            <span class="report-detail-label">
                                Completion
                            </span>

                            <span class="report-detail-value completion">
                                {{ $selectedReport->completion_percentage }}%
                            </span>
                        </div>

                    </div>

                    <div class="report-notes">
                        {{ $selectedReport->notes_summary }}
                    </div>

                </div>
            </section>

            {{-- Evidence and Review --}}
            <section class="report-card selected-report-card">

                <div class="report-card-header">
                    <h2 class="report-card-title">
                        Evidence & Review Decision
                    </h2>
                </div>

                <div class="report-card-body">

                    <div class="attachment-list">

                        @forelse($selectedReport->attachments ?? [] as $file)

                            <div class="attachment-item">

                                <div class="attachment-icon">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>

                                <a
                                    href="{{ asset($file->file_path) }}"
                                    target="_blank"
                                    class="attachment-link"
                                    title="{{ $file->file_name }}">

                                    {{ $file->file_name }}
                                </a>

                                <span class="attachment-size">
                                    {{ $file->file_size_readable }}
                                </span>

                            </div>

                        @empty

                            <div class="attachment-empty">
                                <div>
                                    <i
                                        class="bi bi-paperclip"
                                        style="
                                            display: block;
                                            margin-bottom: 0.45rem;
                                            font-size: 1.1rem;
                                        ">
                                    </i>

                                    No attached image verification files or
                                    PDF logs were provided.
                                </div>
                            </div>

                        @endforelse

                    </div>

                    <form
                        action="{{ route('admin.reports.evaluate', $selectedReport->id) }}"
                        method="POST"
                        class="review-form">

                        @csrf

                        <label
                            for="reviewer_notes"
                            class="review-label">

                            Reviewer Notes
                        </label>

                        <textarea
                            class="review-textarea"
                            id="reviewer_notes"
                            name="reviewer_notes"
                            rows="4"
                            placeholder="Add findings, required revisions, or approval notes..."
                            required>{{ old('reviewer_notes') }}</textarea>

                        <div class="review-actions">

                            <button
                                type="submit"
                                name="decision"
                                value="revision"
                                class="report-action-button revision">

                                <i class="bi bi-arrow-counterclockwise"></i>

                                Request Revision
                            </button>

                            <button
                                type="submit"
                                name="decision"
                                value="approve"
                                class="report-action-button approve">

                                <i class="bi bi-check-lg"></i>

                                Approve Report
                            </button>

                        </div>

                    </form>

                </div>
            </section>

        </div>

    @else

        {{-- No Selected Report --}}
        <div class="report-information-panel">

            <div class="report-information-icon">
                <i class="bi bi-info-lg"></i>
            </div>

            <p class="report-information-text">
                Select an active supervisor log row from the queue grid above
                to isolate item logs, review evidence files, and execute
                deployment approvals.
            </p>

        </div>

    @endif

</div>
@endsection
```
