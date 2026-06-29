@extends('layouts.client')

@section('title', 'Reports & Documents - Client Portal')

@section('content')
<div class="container-fluid p-0">
    
    <div class="mb-4">
        <span class="text-uppercase tracking-wider text-success fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">WORKSPACE DOCUMENTATION</span>
        <h2 class="fw-extrabold text-dark m-0 mt-1" style="font-size: 1.75rem; font-weight: 800;">Reports & Transmittals</h2>
        <p class="text-muted mb-0 mt-1" style="font-size: 0.875rem;">Access formal engineering write-ups, material certifications, and digital site receipts.</p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-md-4">
            <div class="report-summary-widget">
                <div class="widget-icon bg-success-subtle text-success">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                </div>
                <div>
                    <span class="widget-label">Engineering Reports</span>
                    <h3>{{ isset($reports) ? $reports->count() : '0' }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="report-summary-widget">
                <div class="widget-icon bg-warning-subtle text-warning">
                    <i class="bi bi-collection-fill"></i>
                </div>
                <div>
                    <span class="widget-label">Transmitted Folders</span>
                    <h3>{{ isset($reports) ? 'Active' : 'N/A' }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 report-main-panel mb-4">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
            <h6 class="text-uppercase font-bold tracking-wider m-0" style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); letter-spacing: 0.05em;">
                Available Files
            </h6>
        </div>
        
        <div class="card-body px-4 pb-4">
            @if(isset($reports) && $reports->count() > 0)
                <div class="table-responsive">
                    <table class="table report-custom-table align-middle m-0">
                        <thead>
                            <tr>
                                <th scope="col">File Name</th>
                                <th scope="col">Category</th>
                                <th scope="col">Date Logged</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @php
                                                $fileName = $report->file_name ?? $report->title ?? 'Report_Document.pdf';
                                                $isExcel = Str::contains(strtolower($fileName), ['xls', 'xlsx', 'csv']);
                                                $isImage = Str::contains(strtolower($fileName), ['jpg', 'jpeg', 'png']);
                                            @endphp
                                            
                                            <div class="file-icon-avatar {{ $isExcel ? 'excel-theme' : ($isImage ? 'image-theme' : 'pdf-theme') }}">
                                                @if($isExcel)
                                                    <i class="bi bi-file-earmark-excel-fill"></i>
                                                @elseif($isImage)
                                                    <i class="bi bi-file-earmark-image-fill"></i>
                                                @else
                                                    <i class="bi bi-file-earmark-pdf-fill"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="file-primary-title d-block">{{ $fileName }}</span>
                                                <span class="file-size-subtext text-muted">{{ $report->file_size ?? '2.4 MB' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-data-text">{{ $report->category ?? 'Progress Report' }}</span>
                                    </td>
                                    <td>
                                        <span class="date-log-text">
                                            {{ $report->created_at ? $report->created_at->format('M d, Y') : date('M d, Y') }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            @if(isset($report->file_path))
                                                <a href="{{ asset('storage/' . $report->file_path) }}" target="_blank" class="btn btn-action-circle" title="Open Document">
                                                    <i class="bi bi-file-earmark-text-fill"></i>
                                                </a>
                                                <a href="{{ asset('storage/' . $report->file_path) }}" download class="btn btn-action-circle" title="Download Document">
                                                    <i class="bi bi-cloud-arrow-down-fill"></i>
                                                </a>
                                            @else
                                                <button class="btn btn-action-circle" disabled title="No document available"><i class="bi bi-file-earmark-text"></i></button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-5 text-center text-muted border border-dashed rounded-xl bg-light-subtle">
                    <div class="mb-2 fs-3"><i class="bi bi-folder-x text-muted"></i></div>
                    <p class="m-0 font-semibold text-sm">No downloadable files, architectural blueprints, or formal document registries match this workspace profile.</p>
                </div>
            @endif
        </div>
    </div>

</div>

<style>
    /* --- STRUCTURAL CARD BOX METRICS --- */
    .report-main-panel {
        background: #ffffff;
        border: 1px solid #f1f5f9 !important;
        border-radius: 20px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.003);
    }

    .report-summary-widget {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .widget-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .widget-label { font-size: 0.78rem; color: var(--text-muted); font-weight: 500; }
    .report-summary-widget h3 { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--text-primary); }

    /* --- PREMIUM CUSTOM DATA DATA-TABLE --- */
    .report-custom-table th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: var(--text-muted);
        padding: 1rem 1.25rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .report-custom-table td {
        padding: 1.15rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .report-custom-table tr:last-child td {
        border-bottom: none;
    }

    /* --- FILE AVATAR COLOR PALETTES --- */
    .file-icon-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .pdf-theme { background-color: #fef2f2; color: #ef4444; }
    .excel-theme { background-color: #f0fdf4; color: #16a34a; }
    .image-theme { background-color: #eff6ff; color: #3b82f6; }

    .file-primary-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-primary);
        word-break: break-word;
    }

    .file-size-subtext {
        font-size: 0.76rem;
        font-weight: 500;
    }

    .category-data-text {
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
    }

    .date-log-text {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    /* --- CIRCULAR HOVER ACTION BUTTONS --- */
    .btn-action-circle {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.9rem;
        padding: 0;
        transition: all 0.2s ease;
    }

    .btn-action-circle:hover {
        background-color: #013220;
        border-color: #013220;
        color: #ffffff;
    }

    @media (max-width: 991px) {
        .table-responsive {
            overflow-x: hidden;
        }
        .report-custom-table {
            min-width: 0;
            border: none;
        }
        .report-custom-table thead {
            display: none;
        }
        .report-custom-table tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #ffffff;
            overflow: hidden;
        }
        .report-custom-table td {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 0.75rem;
            align-items: center;
            padding: 1rem;
            border-bottom: none;
        }
        .report-custom-table td:nth-child(1),
        .report-custom-table td:nth-child(2) {
            width: 100%;
        }
        .report-custom-table td:nth-child(3),
        .report-custom-table td:nth-child(4) {
            width: auto;
        }
        .report-custom-table td.text-end {
            justify-content: flex-end;
        }
        .btn-action-circle {
            width: 40px;
            height: 40px;
        }
    }

    .font-semibold { font-weight: 600; }
    .text-sm { font-size: 0.85rem; }
    .rounded-xl { border-radius: 14px !important; }
</style>
@endsection