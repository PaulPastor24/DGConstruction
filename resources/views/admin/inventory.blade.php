@extends('layouts.admin')

@section('title', 'Materials & Inventory')
@section('page_title', 'Materials & Inventory')

@push('styles')
<style>
    :root {
        --mi-dark: #1f3d2a;
        --mi-muted: #64748b;
        --mi-border: #e7f3ea;
        --mi-background: #f5fbf6;
        --mi-white: #ffffff;
        --mi-accent: #166534;
        --mi-accent-soft: #eaf7ef;
        --mi-accent-hover: #0f5132;
    }

    .content {
        background: var(--mi-background);
    }

    .mi-page {
        width: 100%;
        padding: 4px 0 28px;
    }

    .inventory-green-theme .card {
        border: 1px solid var(--mi-border) !important;
        box-shadow: 0 10px 28px rgba(22, 101, 52, 0.08);
    }

    .inventory-green-theme .search-container i {
        left: 12px;
        color: #7a8b7f;
    }

    .inventory-green-theme .badge-available {
        background-color: var(--mi-accent-soft);
        color: var(--mi-accent);
    }

    .inventory-green-theme .badge-low-stock {
        background-color: #fff7ed;
        color: #c2410c;
    }

    .inventory-green-theme .badge-out-of-stock {
        background-color: #fef2f2;
        color: #dc2626;
    }

    .inventory-green-theme .table-hover tbody tr:hover {
        background-color: #f7fcf8;
    }

    .inventory-green-theme .btn-primary,
    .inventory-green-theme .btn-success {
        background-color: var(--mi-accent) !important;
        border-color: var(--mi-accent) !important;
        color: #ffffff !important;
    }

    .inventory-green-theme .btn-primary:hover,
    .inventory-green-theme .btn-success:hover,
    .inventory-green-theme .btn-primary:focus,
    .inventory-green-theme .btn-success:focus {
        background-color: var(--mi-accent-hover) !important;
        border-color: var(--mi-accent-hover) !important;
        color: #ffffff !important;
    }

    .inventory-green-theme .btn-outline-secondary:hover {
        border-color: var(--mi-accent) !important;
        color: var(--mi-accent) !important;
        background-color: var(--mi-accent-soft) !important;
    }

    .inventory-green-theme .text-primary,
    .inventory-green-theme .nav-link.active,
    .inventory-green-theme .nav-link.active.text-primary {
        color: var(--mi-accent) !important;
    }

    .inventory-green-theme .border-primary {
        border-color: var(--mi-accent) !important;
    }

    .inventory-green-theme .bg-primary {
        background-color: var(--mi-accent) !important;
    }

    .inventory-green-theme .table thead {
        background-color: #f2f8f3 !important;
    }

    .inventory-green-theme .form-control:focus,
    .inventory-green-theme .form-select:focus {
        border-color: var(--mi-accent) !important;
        box-shadow: 0 0 0 0.2rem rgba(22, 101, 52, 0.16) !important;
    }

    .inventory-view-panel {
        transition: all 0.2s ease;
    }

    .inventory-green-theme .pagination .page-link {
        color: #166534;
        border-color: #d1fae5;
    }

    .inventory-green-theme .pagination .page-item.active .page-link {
        background-color: #16a34a;
        border-color: #16a34a;
        color: #ffffff;
    }

    .inventory-green-theme .pagination .page-link:hover {
        color: #14532d;
        background-color: #ecfdf5;
        border-color: #86efac;
    }

    .inventory-card-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        font-size: 20px;
        border-radius: 14px;
        border: 1px solid rgba(22, 101, 52, 0.14);
        background-color: rgba(22, 101, 52, 0.08);
        color: #166534;
        flex-shrink: 0;
    }

    .inventory-action-stack {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: flex-end;
    }

    .inventory-action-stack .btn {
        white-space: nowrap;
    }

    .inventory-mobile-table-label {
        display: none;
    }

    .inventory-card-icon.available {
        background-color: rgba(22, 101, 52, 0.08);
        color: #166534;
    }

    .inventory-card-icon.low-stock {
        background-color: rgba(249, 115, 22, 0.1);
        color: #c2410c;
        border-color: rgba(249, 115, 22, 0.16);
    }

    .inventory-card-icon.out-of-stock {
        background-color: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        border-color: rgba(239, 68, 68, 0.16);
    }

    .inventory-modal-card {
        border: 1px solid rgba(22, 101, 52, 0.12);
        border-radius: 16px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fdf9 100%);
    }

    .inventory-stat-pill {
        border-radius: 999px;
        padding: 0.35rem 0.7rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .inventory-modal-pagination .page-link {
        color: #166534;
        border-color: #d1fae5;
    }

    .inventory-modal-pagination .page-item.active .page-link {
        background-color: #16a34a;
        border-color: #16a34a;
        color: #ffffff;
    }

    .inventory-modal-pagination .page-link:hover {
        color: #14532d;
        background-color: #ecfdf5;
        border-color: #86efac;
    }

    /* HIGH-FIDELITY RECEIVE STOCK MODAL STYLING OVERRIDES */
    .modal-receive-stock .modal-content {
        border-radius: 12px !important;
        border: none !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08) !important;
        background-color: #ffffff;
    }

    .modal-receive-stock .modal-header-custom {
        padding: 1.5rem 1.5rem 0.5rem 1.5rem;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
    }

    .modal-receive-stock .modal-body-custom {
        padding: 0 1.5rem 1.5rem 1.5rem;
    }

    .modal-receive-stock .modal-icon-container {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background-color: #f0f7f4;
        border: 1px solid #e1efe8;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #166534;
        font-size: 1.35rem;
    }

    .modal-receive-stock .modal-title-text {
        font-size: 1.4rem;
        font-weight: 700;
        color: #166534;
    }

    .modal-receive-stock .modal-subtitle {
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 0.15rem;
    }

    .modal-receive-stock .close-btn-x {
        background: none;
        border: none;
        font-size: 1.25rem;
        color: #1e293b;
        cursor: pointer;
        padding: 0.25rem;
    }

    .modal-receive-stock .meta-info-card {
        background-color: #ffffff;
        border: 1px solid #edf2f0;
        border-radius: 8px;
        padding: 0.85rem 1.25rem;
        margin-bottom: 1.5rem;
    }

    .modal-receive-stock .meta-item {
        position: relative;
    }

    .modal-receive-stock .meta-item:not(:last-child)::after {
        content: '';
        position: absolute;
        right: 0;
        top: 15%;
        height: 70%;
        width: 1px;
        background-color: #e2e8f0;
    }

    .modal-receive-stock .meta-label {
        font-size: 0.725rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.25rem;
    }

    .modal-receive-stock .meta-value {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .modal-receive-stock .badge-status-pill {
        background-color: #eaf7ef;
        color: #166534;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        display: inline-block;
    }

    .modal-receive-stock .form-group-wrapper {
        margin-bottom: 1.25rem;
    }

    .modal-receive-stock .form-label-custom {
        font-weight: 700;
        font-size: 0.85rem;
        color: #0f172a;
        margin-bottom: 0.4rem;
    }

    .modal-receive-stock .form-label-custom .required-asterisk {
        color: #ef4444;
        margin-left: 0.15rem;
    }

    .modal-receive-stock .input-container-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .modal-receive-stock .input-icon-left {
        position: absolute;
        left: 14px;
        color: #94a3b8;
        font-size: 0.95rem;
    }

    .modal-receive-stock .control-field-input {
        width: 100%;
        padding: 0.65rem 0.85rem 0.65rem 2.5rem;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.875rem;
        color: #334155;
        background-color: #ffffff;
        transition: all 0.15s ease;
    }

    .modal-receive-stock .control-field-input:focus {
        outline: none;
        border-color: #166534;
        box-shadow: 0 0 0 3px rgba(22, 101, 52, 0.08);
    }

    .modal-receive-stock .select-caret-wrapper {
        position: relative;
        width: 100%;
    }

    .modal-receive-stock .select-caret-wrapper select {
        padding-right: 2.5rem;
        appearance: none;
        -webkit-appearance: none;
    }

    .modal-receive-stock .select-caret-wrapper::after {
        content: '\f282';
        font-family: 'bootstrap-icons';
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 0.8rem;
        pointer-events: none;
    }

    .modal-receive-stock .input-addon-right {
        background-color: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-left: none;
        padding: 0.65rem 1rem;
        font-size: 0.85rem;
        color: #475569;
        font-weight: 500;
        border-top-right-radius: 6px;
        border-bottom-right-radius: 6px;
    }

    .modal-receive-stock .input-has-addon {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    .modal-receive-stock .form-input-hint {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 0.35rem;
    }

    .modal-receive-stock .char-count-indicator {
        font-size: 0.725rem;
        color: #94a3b8;
    }

    .modal-receive-stock .summary-box-card {
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        height: auto;
        min-height: auto;
    }

    .modal-receive-stock .summary-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 700;
        font-size: 0.875rem;
        color: #0f172a;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 0.55rem;
        margin-bottom: 0.8rem;
    }

    .modal-receive-stock .summary-row-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.65rem;
        font-size: 0.84rem;
    }

    .modal-receive-stock .summary-row-label {
        color: #475569;
    }

    .modal-receive-stock .summary-row-value {
        font-weight: 600;
        color: #0f172a;
    }

    .modal-receive-stock .summary-row-value.received-highlight {
        color: #166534;
    }

    .modal-receive-stock .summary-total-divider {
        border-top: 1px dashed #cbd5e1;
        margin: 0.9rem 0 0.8rem 0;
    }

    .modal-receive-stock .new-stock-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.25rem;
    }

    .modal-receive-stock .new-stock-big-value {
        font-size: 1.6rem;
        font-weight: 800;
        color: #166534;
        line-height: 1.1;
    }

    .modal-receive-stock .alert-banner-toast {
        background-color: #f0fdf4;
        border: 1px solid #dcfce7;
        border-radius: 8px;
        padding: 0.85rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-top: 1.5rem;
        min-height: 96px;
    }

    .modal-receive-stock .alert-banner-toast i {
        color: #166534;
        font-size: 1.15rem;
    }

    .modal-receive-stock .alert-banner-text {
        font-size: 0.8rem;
        color: #14532d;
        font-weight: 500;
        line-height: 1.4;
    }

    .modal-receive-stock .footer-action-row {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }

    .modal-receive-stock .btn-action-cancel {
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        color: #334155;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.6rem 1.5rem;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.15s ease;
    }

    .modal-receive-stock .btn-action-cancel:hover {
        background-color: #f8fafc;
    }

    .modal-receive-stock .btn-action-submit {
        background-color: #166534;
        border: 1px solid #166534;
        color: #ffffff;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.6rem 1.5rem;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.15s ease;
    }

    .modal-receive-stock .btn-action-submit:hover {
        background-color: #14532d;
        border-color: #14532d;
    }

    @media (max-width: 991.98px) {
        .mi-page {
            padding: 4px 0 20px;
        }

        .inventory-green-theme .card-header .nav-tabs {
            flex-wrap: wrap;
        }

        .inventory-green-theme .card-header .nav-tabs .nav-link {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .inventory-action-stack {
            justify-content: flex-start;
        }

        .modal-receive-stock .modal-dialog {
            margin: 0.75rem;
            max-width: calc(100% - 1.5rem);
        }

        .modal-receive-stock .modal-body-custom {
            padding: 0 1rem 1rem 1rem;
        }
    }

    @media (max-width: 767.98px) {
        .inventory-green-theme .card-body {
            padding: 1rem;
        }

        .inventory-green-theme .row.g-3 > [class*='col-'] {
            width: 100%;
        }

        .inventory-card-icon {
            width: 42px;
            height: 42px;
            font-size: 18px;
        }

        .inventory-green-theme .search-container,
        .inventory-green-theme .col-md-2,
        .inventory-green-theme .col-md-4 {
            width: 100%;
            max-width: 100%;
        }

        .inventory-action-stack {
            width: 100%;
            flex-direction: column;
            align-items: stretch;
        }

        .inventory-action-stack .btn {
            width: 100%;
        }

        .inventory-mobile-table-label {
            display: inline-block;
            min-width: 96px;
            color: #64748b;
            font-weight: 600;
        }

        .inventory-green-theme .table-responsive {
            border: 1px solid #eef5ef;
            border-radius: 12px;
        }

        .modal-receive-stock .receive-stock-form-main,
        .modal-receive-stock .receive-stock-form-sidebar {
            flex: 0 0 100%;
            max-width: 100%;
            margin-left: 0;
        }

        .modal-receive-stock .footer-action-row {
            flex-direction: column;
            gap: 0.6rem;
        }

        .modal-receive-stock .btn-action-cancel,
        .modal-receive-stock .btn-action-submit {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="mi-page inventory-green-theme">
    
    <!-- Top 4 Summary Cards Grid Row -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="inventory-card-icon available">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Materials</div>
                        <div class="fs-2 fw-bold text-dark lh-1 my-1">{{ $metrics['total_materials'] }}</div>
                        <div class="text-muted" style="font-size: 11px;">All registered materials</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="inventory-card-icon available">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Available Materials</div>
                        <div class="fs-2 fw-bold text-dark lh-1 my-1">{{ $metrics['available_materials'] }}</div>
                        <div class="text-muted" style="font-size: 11px;">With sufficient stock</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="inventory-card-icon low-stock">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Low Stock</div>
                        <div class="fs-2 fw-bold text-dark lh-1 my-1">{{ $metrics['low_stock_alerts'] }}</div>
                        <div class="text-muted" style="font-size: 11px;">Below minimum level</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="inventory-card-icon out-of-stock">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Out of Stock</div>
                        <div class="fs-2 fw-bold text-dark lh-1 my-1">{{ $metrics['out_of_stock'] }}</div>
                        <div class="text-muted" style="font-size: 11px;">No available stock</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Success',
                    text: '{{ addslashes(session('success')) }}',
                    icon: 'success',
                    confirmButtonColor: '#166534'
                });
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Action failed',
                    text: '{{ addslashes(session('error')) }}',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
            });
        </script>
    @endif
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Validation error',
                    text: @json($errors->first()),
                    icon: 'warning',
                    confirmButtonColor: '#f59e0b'
                });
            });
        </script>
    @endif

    <!-- Main Workspace Split Grid Layout (Left Content, Right Dashboard Widgets) -->
    <div class="row g-4">
        
        <!-- LEFT MAIN DATA SECTOR -->
        <div class="col-lg-9">
            
            <!-- Core Inventory Management Master Panel -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <ul class="nav nav-tabs border-bottom-0">
                        <li class="nav-item">
                            <a class="inventory-view-toggle nav-link {{ $activeView === 'inventory' ? 'active fw-bold border-0 text-primary border-bottom border-primary border-2' : 'fw-semibold border-0 text-muted' }} px-3 pb-2" href="#" data-target="inventory-view">Inventory</a>
                        </li>
                        <li class="nav-item">
                            <a class="inventory-view-toggle nav-link {{ $activeView === 'usage' ? 'active fw-bold border-0 text-primary border-bottom border-primary border-2' : 'fw-semibold border-0 text-muted' }} px-3 pb-2" href="#" data-target="usage-view">Material Usage Logs</a>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body pt-3">
                    <div id="inventory-view" class="inventory-view-panel {{ $activeView === 'usage' ? 'd-none' : '' }}">
                    <!-- Filters Grid Alignment Matching Reference Layout Layout Header -->
                    <form method="GET" action="{{ route('admin.inventory') }}" class="row g-2 align-items-center mb-4" id="inventory-search-form">
                        <div class="col-lg-4 col-md-6 col-12 position-relative search-container">
                            <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm ps-4" placeholder="Search materials or usage logs...">
                            <input type="hidden" name="view" value="inventory" id="inventory-view-input">
                            <i class="bi bi-search position-absolute top-50 translate-middle-y ms-1 text-muted small"></i>
                        </div>
                        <div class="col-md-2">
                            <select name="category" class="form-select form-select-sm text-muted" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="stock_status" class="form-select form-select-sm text-muted" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="normal" {{ $stockStatus === 'normal' ? 'selected' : '' }}>Available</option>
                                <option value="low_stock" {{ $stockStatus === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                <option value="out_of_stock" {{ $stockStatus === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-12 col-12 inventory-action-stack">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-semibold bg-white text-dark" data-bs-toggle="modal" data-bs-target="#receiveStockModalGeneral">
                                <i class="bi bi-plus-lg me-1"></i> Add Material
                            </button>
                        </div>
                    </form>

                    <!-- Main Dynamic Table Content Mapping -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">
                                <tr>
                                    <th class="border-0">Material Name</th>
                                    <th class="border-0">Category</th>
                                    <th class="border-0">Unit</th>
                                    <th class="border-0">Current Stock</th>
                                    <th class="border-0">Minimum Stock</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($materials as $material)
                                    @php
                                        if($material->current_stock <= 0) {
                                            $badgeClass = 'badge-out-of-stock';
                                            $statusText = 'Out of Stock';
                                        } elseif($material->current_stock <= $material->minimum_stock_level) {
                                            $badgeClass = 'badge-low-stock';
                                            $statusText = 'Low Stock';
                                        } else {
                                            $badgeClass = 'badge-available';
                                            $statusText = 'Available';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold text-dark">{{ $material->name }}</td>
                                        <td class="text-muted">{{ $material->category ?? 'General' }}</td>
                                        <td class="text-muted">{{ $material->unit }}</td>
                                        <td class="fw-bold text-dark">{{ number_format($material->current_stock, 0) }}</td>
                                        <td class="text-muted">{{ number_format($material->minimum_stock_level, 0) }}</td>
                                        <td><span class="badge rounded-pill px-2.5 py-1.5 {{ $badgeClass }}" style="font-size: 11px; font-weight: 600;">{{ $statusText }}</span></td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <button type="button" class="btn btn-sm btn-light p-1 px-2 border text-primary bg-white" data-bs-toggle="modal" data-bs-target="#viewMaterialModal{{ $material->id }}"><i class="bi bi-eye"></i></button>
                                                <button type="button" class="btn btn-sm btn-light p-1 px-2 border text-success bg-white" data-bs-toggle="modal" data-bs-target="#editMaterialModal{{ $material->id }}"><i class="bi bi-pencil"></i></button>
                                                <button type="button" class="btn btn-sm btn-light p-1 px-2 border text-warning bg-white" data-bs-toggle="modal" data-bs-target="#receiveStockModalGeneral" data-material-id="{{ $material->id }}" data-material-name="{{ $material->name }}" data-material-unit="{{ $material->unit }}"><i class="bi bi-envelope-open"></i></button>
                                                <form method="POST" action="{{ route('admin.inventory.materials.destroy', $material->id) }}" class="inventory-delete-form d-inline m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-light p-1 px-2 border text-danger bg-white" type="submit"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-4">No structural materials profiles discovered.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Layout Footer Summary with Pagination Links -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 border-top pt-3 mt-3">
                        <span class="text-muted small">Showing {{ $materials->firstItem() ?? 0 }} to {{ $materials->lastItem() ?? 0 }} of {{ $materials->total() }} materials</span>
                        <div class="w-100 w-md-auto overflow-auto">{{ $materials->links('pagination::bootstrap-5') }}</div>
                    </div>
                    </div>

                    <div id="usage-view" class="inventory-view-panel {{ $activeView === 'inventory' ? 'd-none' : '' }}">
                        <form method="GET" action="{{ route('admin.inventory') }}" class="row g-2 align-items-center mb-3" id="usage-search-form">
                            <input type="hidden" name="category" value="{{ $category }}">
                            <input type="hidden" name="stock_status" value="{{ $stockStatus }}">
                            <input type="hidden" name="view" value="usage" id="usage-view-input">
                            <div class="col-lg-4 col-md-6 col-12 position-relative search-container">
                                <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm ps-4" placeholder="Search usage logs...">
                                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-1 text-muted small"></i>
                            </div>
                            <div class="col-md-2">
                                <select name="usage_category" class="form-select form-select-sm text-muted" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ $usageCategory === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="usage_status" class="form-select form-select-sm text-muted" onchange="this.form.submit()" style="display: none;">
                                    <option value="">All</option>
                                    <option value="with_remarks" {{ $usageStatus === 'with_remarks' ? 'selected' : '' }}>Has Notes</option>
                                    <option value="without_remarks" {{ $usageStatus === 'without_remarks' ? 'selected' : '' }}>No Notes</option>
                                </select>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0" style="font-size: 13px;">
                                <thead class="table-light text-muted fw-bold" style="font-size: 11px;">
                                    <tr>
                                        <th class="border-0">Date</th>
                                        <th class="border-0">Project</th>
                                        <th class="border-0">Phase</th>
                                        <th class="border-0">Material</th>
                                        <th class="border-0">Quantity Used</th>
                                        <th class="border-0">Unit</th>
                                        <th class="border-0">Used By (Supervisor)</th>
                                        <th class="border-0 text-center">Other Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($usageLogs as $log)
                                        <tr>
                                            <td class="text-muted">{{ optional($log->usage_date)->format('M d, Y') ?? '-' }}</td>
                                            <td class="fw-semibold text-dark">{{ optional($log->project)->project_name ?? 'N/A' }}</td>
                                            <td class="text-muted">{{ optional($log->phase)->phase_name ?? 'N/A' }}</td>
                                            <td class="fw-semibold text-dark">{{ optional($log->material)->name ?? 'N/A' }}</td>
                                            <td class="fw-bold text-dark">{{ number_format($log->quantity_used, 0) }}</td>
                                            <td class="text-muted">{{ optional($log->material)->unit ?? 'Piece' }}</td>
                                            <td>{{ optional($log->recorder)->name ?? 'Unknown' }}</td>
                                            <td class="text-center">
                                                <button type="button"
                                                    class="btn btn-outline-success btn-sm p-2"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#materialUsageDetailModal"
                                                    data-date="{{ optional($log->usage_date)->format('M d, Y') ?? '-' }}"
                                                    data-project="{{ optional($log->project)->project_name ?? 'N/A' }}"
                                                    data-phase="{{ optional($log->phase)->phase_name ?? 'N/A' }}"
                                                    data-material="{{ optional($log->material)->name ?? 'N/A' }}"
                                                    data-quantity="{{ number_format($log->quantity_used, 0) }}"
                                                    data-unit="{{ optional($log->material)->unit ?? 'Piece' }}"
                                                    data-recorder="{{ optional($log->recorder)->name ?? 'Unknown' }}"
                                                    data-notes="{{ e($log->remarks ?? '') }}"
                                                    data-photo="{{ $log->site_photo_path ? asset('storage/' . ltrim($log->site_photo_path, '/')) : '' }}"
                                                    title="View usage details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="text-center text-muted py-4">No analytical usage sequences registered.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COMPACT SIDEBAR COLUMN -->
        <div class="col-lg-3 d-flex flex-column gap-3">
            
            <!-- Widget Component 1: Compact Low Stock Visual Tracking alerts -->
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <h6 class="fw-bold mb-3 text-dark" style="font-size: 14px;">Low Stock Alerts</h6>
                <div class="d-flex flex-column gap-3">
                    @forelse($lowStockMaterials as $lowMat)
                    <div class="d-flex align-items-center justify-content-between" style="font-size: 13px;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle d-inline-block" style="width: 8px; height: 8px; background-color: #f97316;"></span>
                            <span class="text-dark fw-semibold">{{ $lowMat->name }}</span>
                        </div>
                        <span class="text-muted fw-bold">{{ number_format((float) $lowMat->current_stock, 0) }} / <span class="text-muted small fw-normal">{{ number_format((float) $lowMat->minimum_stock_level, 0) }} {{ $lowMat->unit }}</span></span>
                    </div>
                    @empty
                    <div class="text-muted small">No low-stock materials at the moment.</div>
                    @endforelse
                </div>
                <button type="button" class="btn btn-link p-0 text-center text-primary fw-bold text-decoration-none mt-3 d-block small" style="font-size: 12px;" data-bs-toggle="modal" data-bs-target="#lowStockModal">View all low stock</button>
            </div>

            <!-- Widget Component 2: Dynamic Receivals Stream Log -->
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <h6 class="fw-bold mb-3 text-dark" style="font-size: 14px;">Recent Stock Received</h6>
                <div class="d-flex flex-column gap-3">
                    @forelse($recentlyUpdatedMaterials as $recMat)
                    <div class="d-flex align-items-center justify-content-between" style="font-size: 13px;">
                        <div>
                            <div class="text-dark fw-semibold">{{ $recMat->name }}</div>
                            <div class="text-muted small" style="font-size: 11px;">{{ optional($recMat->updated_at)->format('M d, Y') ?? 'Recently updated' }}</div>
                        </div>
                        <span class="text-success fw-bold">{{ number_format((float) $recMat->current_stock, 0) }} {{ $recMat->unit }}</span>
                    </div>
                    @empty
                    <div class="text-muted small">No recent stock updates available.</div>
                    @endforelse
                </div>
                <button type="button" class="btn btn-link p-0 text-center text-primary fw-bold text-decoration-none mt-3 d-block small" style="font-size: 12px;" data-bs-toggle="modal" data-bs-target="#recentStockModal">View all received</button>
            </div>

            <!-- Widget Component 3: Clean Analytics Donut Graphic representation -->
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <h6 class="fw-bold mb-3 text-dark" style="font-size: 14px;">Inventory Summary</h6>
                <div class="d-flex justify-content-center mb-3">
                    <div class="position-relative d-flex align-items-center justify-content-center" style="width: 115px; height: 115px; border-radius: 50%; background: conic-gradient(#10b981 0% {{ $metrics['available_percentage'] }}%, #f97316 {{ $metrics['available_percentage'] }}% {{ $metrics['available_percentage'] + $metrics['low_stock_percentage'] }}%, #ef4444 {{ $metrics['available_percentage'] + $metrics['low_stock_percentage'] }}% 100%);">
                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 85px; height: 85px;">
                            <div class="text-center">
                                <span class="fs-4 fw-bold text-dark lh-1 d-block">{{ $metrics['total_materials'] }}</span>
                                <span class="text-muted" style="font-size: 9px; uppercase;">Total Items</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex flex-column gap-2" style="font-size: 12px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle d-inline-block" style="width: 10px; height: 10px; background-color: #10b981;"></span>
                            <span class="text-muted">Available</span>
                        </div>
                        <span class="fw-bold text-dark">{{ $metrics['available_materials'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle d-inline-block" style="width: 10px; height: 10px; background-color: #f97316;"></span>
                            <span class="text-muted">Low Stock</span>
                        </div>
                        <span class="fw-bold text-dark">{{ $metrics['low_stock_alerts'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle d-inline-block" style="width: 10px; height: 10px; background-color: #ef4444;"></span>
                            <span class="text-muted">Out of Stock</span>
                        </div>
                        <span class="fw-bold text-dark">{{ $metrics['out_of_stock'] }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Dialog Structures Block Configurations -->
<div class="modal fade" id="lowStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold text-dark">Low Stock Materials</h5>
                    <p class="text-muted small mb-0">Items that require replenishment soon.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Material</th>
                                <th>Category</th>
                                <th>Current</th>
                                <th>Minimum</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allLowStockMaterials as $material)
                                <tr>
                                    <td class="fw-semibold text-dark">{{ $material->name }}</td>
                                    <td class="text-muted">{{ $material->category ?? 'General' }}</td>
                                    <td class="fw-bold text-dark">{{ number_format((float) $material->current_stock, 0) }}</td>
                                    <td class="text-muted">{{ number_format((float) $material->minimum_stock_level, 0) }}</td>
                                    <td><span class="badge bg-warning-subtle text-warning rounded-pill px-2.5 py-1">Low Stock</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No low stock items available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 border-top pt-3 mt-3">
                    <span class="text-muted small">Showing {{ $allLowStockMaterials->firstItem() ?? 0 }} to {{ $allLowStockMaterials->lastItem() ?? 0 }} of {{ $allLowStockMaterials->total() }} items</span>
                    <div class="inventory-modal-pagination w-100 w-md-auto overflow-auto">{{ $allLowStockMaterials->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="recentStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold text-dark">Recent Stock Updates</h5>
                    <p class="text-muted small mb-0">Latest materials with current available stock.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Material</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allRecentlyUpdatedMaterials as $material)
                                <tr>
                                    <td class="fw-semibold text-dark">{{ $material->name }}</td>
                                    <td class="text-muted">{{ $material->category ?? 'General' }}</td>
                                    <td class="fw-bold text-success">{{ number_format((float) $material->current_stock, 0) }} {{ $material->unit }}</td>
                                    <td class="text-muted">{{ optional($material->updated_at)->format('M d, Y H:i') ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No recent stock updates available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 border-top pt-3 mt-3">
                    <span class="text-muted small">Showing {{ $allRecentlyUpdatedMaterials->firstItem() ?? 0 }} to {{ $allRecentlyUpdatedMaterials->lastItem() ?? 0 }} of {{ $allRecentlyUpdatedMaterials->total() }} items</span>
                    <div class="inventory-modal-pagination w-100 w-md-auto overflow-auto">{{ $allRecentlyUpdatedMaterials->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- HIGH-FIDELITY RECEIVE STOCK MODAL (MATCHES IMAGE EXACTLY) -->
<div class="modal fade modal-receive-stock" id="receiveStockModalGeneral" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <!-- Header Segment -->
            <div class="modal-header-custom">
                <div class="d-flex align-items-center gap-3">
                    <div class="modal-icon-container">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <h4 class="modal-title-text mb-0">Add Material</h4>
                        <p class="modal-subtitle mb-0">Create a new material or receive stock to update inventory.</p>
                    </div>
                </div>
                <button type="button" class="close-btn-x" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Modal Content Body -->
            <div class="modal-body-custom">
                <!-- Meta Statistics Header Bar -->
                <div class="meta-info-card">
                    <div class="row g-2 text-center text-sm-start">
                        <div class="col-sm-3 meta-item">
                            <div class="meta-label">Material</div>
                            <div class="meta-value" id="metaMaterialName">Cement (Holcim)</div>
                        </div>
                        <div class="col-sm-3 meta-item text-sm-center">
                            <div class="meta-label">Current Stock</div>
                            <div class="meta-value text-success" id="metaCurrentStock">120 Bags</div>
                        </div>
                        <div class="col-sm-3 meta-item text-sm-center">
                            <div class="meta-label">Minimum Stock</div>
                            <div class="meta-value" id="metaMinimumStock">50 Bags</div>
                        </div>
                        <div class="col-sm-3 text-sm-center">
                            <div class="meta-label">Status</div>
                            <div class="mt-1">
                                <span class="badge-status-pill" id="metaStatusBadge">Available</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="receiveStockForm" method="POST" action="{{ route('admin.inventory.materials.receive') }}">
                    @csrf
                    <div class="row g-4 align-items-start receive-stock-form-layout">
                        <!-- Left Layout Form Parameter Fields -->
                        <div class="receive-stock-form-main">
                            
                            <div class="row g-3">
                                <div class="col-12 col-lg-6">
                                    <div class="form-group-wrapper mb-0">
                                        <label class="form-label-custom">Material<span class="required-asterisk">*</span></label>
                                        <div class="input-container-group select-caret-wrapper">
                                            <i class="bi bi-box-seam input-icon-left"></i>
                                            <select id="receiveStockMaterialSelect" name="material_id" class="control-field-input" required>
                                                <option value="" {{ old('material_id') === null ? 'selected' : '' }}>Select material</option>
                                                @foreach($materials as $material)
                                                    <option value="{{ $material->id }}"
                                                            data-name="{{ $material->name }}"
                                                            data-unit="{{ $material->unit }}"
                                                            data-stock="{{ $material->current_stock }}"
                                                            data-min="{{ $material->minimum_stock_level }}"
                                                            data-category="{{ $material->category }}"
                                                            {{ old('material_id') == $material->id ? 'selected' : '' }}>
                                                        {{ $material->name }}
                                                    </option>
                                                @endforeach
                                                <option value="new" {{ old('material_id') === 'new' ? 'selected' : '' }}>Other (new material)</option>
                                            </select>
                                        </div>
                                        <div class="form-input-hint">Select an existing material or choose Other to type a new material name.</div>
                                        <input type="text" id="receiveStockMaterialInput" name="material_name" class="control-field-input mt-2 {{ old('material_id') === 'new' ? '' : 'd-none' }}" placeholder="Type new material name" autocomplete="off" value="{{ old('material_name') }}" {{ old('material_id') === 'new' ? 'required' : '' }}>
                                    </div>
                                    </div>

                                <div class="col-12 col-lg-6">
                                    <div class="form-group-wrapper mb-0">
                                        <label class="form-label-custom">Category</label>
                                        <div class="input-container-group">
                                            <i class="bi bi-tags input-icon-left"></i>
                                            <input type="text" id="receiveStockMaterialCategoryInput" name="category" class="control-field-input" placeholder="Category" value="{{ old('category') }}">
                                        </div>
                                        <div class="form-input-hint">Material category — stored in materials table.</div>
                                        </div>
                                </div>
                                </div>

                                <div class="row g-3">
                                <div class="col-12 col-lg-6">
                                    <div class="form-group-wrapper mb-0">
                                        <label class="form-label-custom">Quantity Received<span class="required-asterisk">*</span></label>
                                        <div class="input-container-group">
                                            <i class="bi bi-box input-icon-left"></i>
                                            <input type="number" step="0.01" min="0.01" id="inputQuantityReceived" name="quantity_received" class="control-field-input input-has-addon text-start" placeholder="Enter quantity received" value="{{ old('quantity_received') }}" required>
                                            <span class="input-addon-right" id="addonUnitText">Bags</span>
                                        </div>
                                        <div class="form-input-hint">Enter the total quantity of material received.</div>
                                        </div>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <div class="form-group-wrapper mb-0">
                                        <label class="form-label-custom">Received Date<span class="required-asterisk">*</span></label>
                                        <div class="input-container-group select-caret-wrapper">
                                            <i class="bi bi-calendar3 input-icon-left"></i>
                                            <input type="date" id="inputReceivedDate" name="received_date" class="control-field-input" value="{{ now()->toDateString() }}" required>
                                        </div>
                                        <div class="form-input-hint">Select the date when the stock was received.</div>
                                    </div>
                                    </div>
                                </div>

                                <div class="row g-3 mt-1">
                                <div class="col-12 col-lg-6">
                                    <div class="form-group-wrapper mb-0">
                                        <label class="form-label-custom">Supplier</label>
                                        <div class="input-container-group">
                                            <i class="bi bi-person input-icon-left"></i>
                                            <input type="text" name="supplier" id="inputSupplierText" class="control-field-input" placeholder="Enter supplier name (optional)" value="{{ old('supplier') }}">
                                        </div>
                                        <div class="form-input-hint">Supplier who delivered the materials.</div>
                                    </div>
                                    </div>
                                <div class="col-12 col-lg-6">
                                    <div class="form-group-wrapper mb-0">
                                        <label class="form-label-custom">Reference / OR No.</label>
                                        <div class="input-container-group">
                                            <i class="bi bi-file-earmark-text input-icon-left"></i>
                                            <input type="text" name="notes" class="control-field-input" placeholder="Enter reference or OR number (optional)" value="{{ old('notes') }}">
                                        </div>
                                        <div class="form-input-hint">Delivery receipt number or official receipt number.</div>
                                    </div>
                                </div>
                                </div>

                                <div class="row g-3 mt-1">
                                <div class="col-12 col-lg-6">
                                    <div class="form-group-wrapper mb-2">
                                        <label class="form-label-custom">Remarks (Optional)</label>
                                        <div class="input-container-group">
                                            <i class="bi bi-chat-square-dots input-icon-left" style="top: 14px; transform: none;"></i>
                                            <textarea name="remarks" id="textareaRemarks" class="control-field-input" rows="3" maxlength="255" placeholder="Enter any remarks or notes..." style="padding-top: 0.55rem; resize: none;">{{ old('remarks') }}</textarea>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <div class="form-input-hint my-0">Additional notes about this stock receipt.</div>
                                            <div class="char-count-indicator" id="remarksCharCounter">0 / 255</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="alert-banner-toast">
                                        <i class="bi bi-info-circle" style="font-size:1.25rem; margin-right:0.6rem;"></i>
                                        <div class="alert-banner-text">
                                            <strong>Note:</strong> The received quantity will be added to the current stock of this material.
                                        </div>
                                    </div>
                                </div>
                                </div>
                        </div>

                        <!-- Right Calculation Dynamic Summary Box Sidebar -->
                        <div class="receive-stock-form-sidebar">
                            <div class="summary-box-card">
                                <div class="summary-header">
                                    <i class="bi bi-graph-up-arrow text-muted"></i>
                                    <span>Stock Summary</span>
                                </div>
                                
                                <div class="summary-row-item">
                                    <span class="summary-row-label">Current Stock</span>
                                    <span class="summary-row-value" id="summaryCurrentStock">120 Bags</span>
                                </div>

                                <div class="summary-row-item">
                                    <span class="summary-row-label">Quantity Received</span>
                                    <span class="summary-row-value received-highlight" id="summaryQtyReceived">0 Bags</span>
                                </div>

                                <div class="summary-total-divider"></div>

                                <div class="new-stock-title">New Stock (After Receive)</div>
                                <div class="new-stock-big-value" id="summaryNewStockCalculation">120 Bags</div>
                            </div>
                        </div>
                    </div>

                    <!-- Lower Footer Interactive Buttons Row -->
                    <div class="footer-action-row">
                        <button type="button" class="btn-action-cancel" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i> Cancel
                        </button>
                        <button type="submit" class="btn-action-submit">
                            <i class="bi bi-check-circle"></i> Add Material
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addMaterialModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <form method="POST" action="{{ route('admin.inventory.materials.store') }}" class="inventory-form">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-dark">Add New Master Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Material Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Portland Cement" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Category</label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. Masonry">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-semibold">Unit Type</label>
                        <input type="text" name="unit" class="form-control" placeholder="e.g. Bag" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-semibold">Initial Stock Level</label>
                            <input type="number" step="0.01" min="0" name="current_stock" class="form-control" value="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted fw-semibold">Minimum Threshold Limit</label>
                            <input type="number" step="0.01" min="0" name="minimum_stock_level" class="form-control" value="0" required>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label small text-muted fw-semibold">Supplier Source Partner</label>
                        <input type="text" name="supplier" class="form-control">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Material Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($materials as $material)
<div class="modal fade" id="viewMaterialModal{{ $material->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-dark">{{ $material->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-2">Category Segment: <strong class="text-dark">{{ $material->category ?? 'General' }}</strong></p>
                <p class="text-muted mb-2">Unit Classification: <strong class="text-dark">{{ $material->unit }}</strong></p>
                <p class="text-muted mb-2">Current Active Stock: <strong class="text-dark">{{ number_format($material->current_stock, 2) }}</strong></p>
                <p class="text-muted mb-2">Minimum Level Bound: <strong class="text-dark">{{ number_format($material->minimum_stock_level, 2) }}</strong></p>
                <p class="text-muted mb-0">Assigned Vendor: <strong class="text-dark">{{ $material->supplier ?? 'Not specified' }}</strong></p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editMaterialModal{{ $material->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <form method="POST" action="{{ route('admin.inventory.materials.update', $material->id) }}" class="inventory-form">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-3">
                        <div class="inventory-card-icon available">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark">Modify Registered Inventory Item</h5>
                            <p class="text-muted small mb-0">Update the material profile, reorder threshold, and supplier details.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="inventory-modal-card p-3 mb-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="text-muted small fw-semibold mb-1">Current Stock</div>
                                <div class="fw-bold text-dark">{{ number_format((float) $material->current_stock, 0) }} {{ $material->unit }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small fw-semibold mb-1">Status</div>
                                <span class="inventory-stat-pill {{ $material->current_stock <= 0 ? 'bg-danger-subtle text-danger' : ($material->current_stock <= $material->minimum_stock_level ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success') }}">{{ $material->current_stock <= 0 ? 'Out of Stock' : ($material->current_stock <= $material->minimum_stock_level ? 'Low Stock' : 'Available') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted fw-semibold">Material Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $material->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted fw-semibold">Category</label>
                            <input type="text" name="category" class="form-control" value="{{ old('category', $material->category ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted fw-semibold">Unit</label>
                            <input type="text" name="unit" class="form-control" value="{{ old('unit', $material->unit) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted fw-semibold">Minimum Stock Level</label>
                            <input type="number" step="0.01" min="0" name="minimum_stock_level" class="form-control" value="{{ old('minimum_stock_level', $material->minimum_stock_level) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted fw-semibold">Supplier</label>
                            <input type="text" name="supplier" class="form-control" value="{{ old('supplier', $material->supplier ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $material->description) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<div class="modal fade" id="materialUsageDetailModal" tabindex="-1" aria-labelledby="materialUsageDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-2" style="background: linear-gradient(135deg, #ecfdf3 0%, #f8fff9 100%);">
                <div>
                    <h5 class="modal-title fw-bold text-success" id="materialUsageDetailModalLabel">Material Usage Details</h5>
                    <div class="text-muted small">Complete record for this material usage entry</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4 pt-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-light-subtle">
                            <div class="text-uppercase text-muted small fw-semibold">Material</div>
                            <div id="detailMaterial" class="fw-semibold text-dark"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-light-subtle">
                            <div class="text-uppercase text-muted small fw-semibold">Project</div>
                            <div id="detailProject" class="fw-semibold text-dark"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-light-subtle">
                            <div class="text-uppercase text-muted small fw-semibold">Phase</div>
                            <div id="detailPhase" class="fw-semibold text-dark"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-light-subtle">
                            <div class="text-uppercase text-muted small fw-semibold">Date Used</div>
                            <div id="detailDate" class="fw-semibold text-dark"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-light-subtle">
                            <div class="text-uppercase text-muted small fw-semibold">Quantity Used</div>
                            <div id="detailQuantity" class="fw-semibold text-dark"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-light-subtle">
                            <div class="text-uppercase text-muted small fw-semibold">Used By</div>
                            <div id="detailRecorder" class="fw-semibold text-dark"></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded-3 border bg-light-subtle">
                            <div class="text-uppercase text-muted small fw-semibold">Other Details / Notes</div>
                            <div id="detailNotes" class="text-muted mt-1"></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded-3 border bg-light-subtle">
                            <div class="text-uppercase text-muted small fw-semibold">Submitted Photo</div>
                            <div id="detailPhoto" class="mt-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function setActiveView(targetId) {
            document.querySelectorAll('.inventory-view-panel').forEach(function (panel) {
                panel.classList.toggle('d-none', panel.id !== targetId);
            });

            document.querySelectorAll('.inventory-view-toggle').forEach(function (link) {
                const isActive = link.getAttribute('data-target') === targetId;
                link.classList.toggle('active', isActive);
                link.classList.toggle('text-primary', isActive);
                link.classList.toggle('text-muted', !isActive);
                link.classList.toggle('fw-bold', isActive);
                link.classList.toggle('border-bottom', isActive);
                link.classList.toggle('border-primary', isActive);
                link.classList.toggle('border-2', isActive);
            });

            document.querySelectorAll('#inventory-view-input, #usage-view-input').forEach(function (input) {
                input.value = targetId === 'usage-view' ? 'usage' : 'inventory';
            });
        }

        document.querySelectorAll('.inventory-view-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function (event) {
                event.preventDefault();
                setActiveView(toggle.getAttribute('data-target'));
            });
        });

        document.querySelectorAll('#inventory-search-form input[name="search"], #usage-search-form input[name="search"]').forEach(function (input) {
            let debounceTimer;

            input.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    const form = input.closest('form');
                    const activeTarget = document.querySelector('.inventory-view-toggle.active')?.getAttribute('data-target');
                    const viewValue = activeTarget === 'usage-view' ? 'usage' : 'inventory';
                    const hiddenInput = form.querySelector('input[name="view"]');

                    if (hiddenInput) {
                        hiddenInput.value = viewValue;
                    }

                    form.submit();
                }, 350);
            });
        });

        const activePanel = document.querySelector('.inventory-view-panel:not(.d-none)');
        if (activePanel) {
            setActiveView(activePanel.id);
        }

        // REAL-TIME MODAL MATHEMATICAL COMPUTATION & VALUE TRACKING LOGIC
        const receiveStockModal = document.getElementById('receiveStockModalGeneral');
        const receiveStockForm = document.getElementById('receiveStockForm');
        const receiveStockSelect = document.getElementById('receiveStockMaterialSelect');
        const receiveStockTextInput = document.getElementById('receiveStockMaterialInput');
        const receiveStockCategoryInput = document.getElementById('receiveStockMaterialCategoryInput');
        const receiveStockSubmitRoute = '{{ route('admin.inventory.materials.receive') }}';

        const inputQty = document.getElementById('inputQuantityReceived');
        const txtRemarks = document.getElementById('textareaRemarks');
        const charCounter = document.getElementById('remarksCharCounter');

        const metaName = document.getElementById('metaMaterialName');
        const metaCurrent = document.getElementById('metaCurrentStock');
        const metaMin = document.getElementById('metaMinimumStock');
        const metaStatus = document.getElementById('metaStatusBadge');
        const addonUnit = document.getElementById('addonUnitText');

        const sumCurrent = document.getElementById('summaryCurrentStock');
        const sumReceived = document.getElementById('summaryQtyReceived');
        const sumCalculatedTotal = document.getElementById('summaryNewStockCalculation');

        let currentMaterialStockValue = 0;
        let activeMaterialUnitText = "Bags";

        function formatStockValue(value) {
            const numericValue = Number(value);
            if (!Number.isFinite(numericValue)) {
                return '0';
            }

            const roundedValue = Math.round(numericValue * 100) / 100;
            return Number.isInteger(roundedValue)
                ? String(roundedValue)
                : roundedValue.toFixed(2).replace(/\.0+$/, '').replace(/(\.[1-9]*)0+$/, '$1');
        }

        function resetMaterialSummary() {
            if (metaName) metaName.textContent = 'Select a material';
            if (metaCurrent) metaCurrent.textContent = '—';
            if (metaMin) metaMin.textContent = '—';
            if (metaStatus) {
                metaStatus.textContent = 'Select material';
                metaStatus.className = 'badge-status-pill';
            }
            if (addonUnit) addonUnit.textContent = 'Unit';
            if (sumCurrent) sumCurrent.textContent = '—';
            if (sumReceived) sumReceived.textContent = '0 Unit';
            if (sumCalculatedTotal) sumCalculatedTotal.textContent = '0 Unit';
            if (receiveStockForm) receiveStockForm.setAttribute('action', receiveStockSubmitRoute);
        }

        function calculateLiveStockSummary() {
            const incomingQty = parseFloat(inputQty.value) || 0;
            const computedNewTotal = currentMaterialStockValue + incomingQty;
            
            if (sumReceived) {
                sumReceived.textContent = formatStockValue(incomingQty) + " " + activeMaterialUnitText;
            }
            if (sumCalculatedTotal) {
                sumCalculatedTotal.textContent = formatStockValue(computedNewTotal) + " " + activeMaterialUnitText;
            }
            
            if(incomingQty > 0 && sumReceived) {
                sumReceived.classList.add('text-success');
            } else if (sumReceived) {
                sumReceived.classList.remove('text-success');
            }
        }

        function toggleNewMaterialInput() {
            if (!receiveStockSelect || !receiveStockTextInput) {
                return;
            }

            if (receiveStockSelect.value === 'new') {
                receiveStockTextInput.classList.remove('d-none');
                if (receiveStockCategoryInput) {
                    receiveStockCategoryInput.removeAttribute('readonly');
                    receiveStockCategoryInput.setAttribute('required', 'required');
                }
                receiveStockTextInput.setAttribute('required', 'required');
                receiveStockTextInput.focus();
            } else {
                receiveStockTextInput.classList.add('d-none');
                receiveStockTextInput.removeAttribute('required');
                receiveStockTextInput.value = '';
                if (receiveStockCategoryInput) {
                    // for existing, keep category visible but not required and set readonly=false so user can change if desired
                    receiveStockCategoryInput.removeAttribute('required');
                    receiveStockCategoryInput.removeAttribute('readonly');
                }
            }
        }

        function updateMaterialSummaryFromSelection() {
            if (!receiveStockSelect) {
                return;
            }

            const chosenOption = receiveStockSelect.options[receiveStockSelect.selectedIndex];

            if (!chosenOption || chosenOption.value === "") {
                resetMaterialSummary();
                return;
            }

            if (receiveStockSelect.value === 'new') {
                resetMaterialSummary();
                toggleNewMaterialInput();
                return;
            }

            const mName = chosenOption.getAttribute('data-name') || chosenOption.textContent.trim();
            activeMaterialUnitText = chosenOption.getAttribute('data-unit') || "Bags";
            currentMaterialStockValue = parseFloat(chosenOption.getAttribute('data-stock')) || 0;
            const minStockLevel = parseFloat(chosenOption.getAttribute('data-min')) || 0;
            const existingCategory = chosenOption.getAttribute('data-category') || '';

            if (receiveStockCategoryInput) {
                // populate category input for existing materials but keep it hidden unless creating new
                receiveStockCategoryInput.value = existingCategory;
            }

            if (receiveStockForm) {
                receiveStockForm.setAttribute('action', receiveStockSubmitRoute);
            }

            if (metaName) metaName.textContent = mName;
            if (metaCurrent) metaCurrent.textContent = formatStockValue(currentMaterialStockValue) + " " + activeMaterialUnitText;
            if (metaMin) metaMin.textContent = formatStockValue(minStockLevel) + " " + activeMaterialUnitText;
            if (addonUnit) addonUnit.textContent = activeMaterialUnitText;
            if (sumCurrent) sumCurrent.textContent = formatStockValue(currentMaterialStockValue) + " " + activeMaterialUnitText;

            if (metaStatus) {
                if (currentMaterialStockValue <= 0) {
                    metaStatus.textContent = "Out of Stock";
                    metaStatus.className = "badge-status-pill bg-danger-subtle text-danger";
                } else if (currentMaterialStockValue <= minStockLevel) {
                    metaStatus.textContent = "Low Stock";
                    metaStatus.className = "badge-status-pill bg-warning-subtle text-warning";
                } else {
                    metaStatus.textContent = "Available";
                    metaStatus.className = "badge-status-pill";
                }
            }

            if (inputQty) inputQty.value = '';
            if (txtRemarks) txtRemarks.value = '';
            if (charCounter) charCounter.textContent = '0 / 255';
            calculateLiveStockSummary();
        }

        if (txtRemarks && charCounter) {
            txtRemarks.addEventListener('input', function() {
                charCounter.textContent = this.value.length + " / 255";
            });
        }

        if (inputQty) {
            inputQty.addEventListener('input', calculateLiveStockSummary);
        }

        if (receiveStockSelect) {
            receiveStockSelect.addEventListener('change', function () {
                toggleNewMaterialInput();
                updateMaterialSummaryFromSelection();
            });
        }

        if (receiveStockModal) {
            receiveStockModal.addEventListener('show.bs.modal', function (event) {
                const triggerButton = event.relatedTarget;
                const materialId = triggerButton?.getAttribute('data-material-id') || '';
                const materialName = triggerButton?.getAttribute('data-material-name') || '';

                if (receiveStockSelect) {
                    if (materialId) {
                        receiveStockSelect.value = materialId;
                    } else if (materialName) {
                        const matchingOption = Array.from(receiveStockSelect.options).find(function (option) {
                            return option.getAttribute('data-name')?.trim() === materialName.trim();
                        });
                        if (matchingOption) {
                            receiveStockSelect.value = matchingOption.value;
                        }
                    } else {
                        receiveStockSelect.selectedIndex = 1;
                    }

                    toggleNewMaterialInput();
                    updateMaterialSummaryFromSelection();
                }
            });
        }

        const detailModal = document.getElementById('materialUsageDetailModal');
        if (detailModal) {
            detailModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                document.getElementById('detailMaterial').textContent = button.getAttribute('data-material') || 'N/A';
                document.getElementById('detailProject').textContent = button.getAttribute('data-project') || 'N/A';
                document.getElementById('detailPhase').textContent = button.getAttribute('data-phase') || 'N/A';
                document.getElementById('detailDate').textContent = button.getAttribute('data-date') || 'N/A';
                document.getElementById('detailQuantity').textContent = (button.getAttribute('data-quantity') || '0') + ' ' + (button.getAttribute('data-unit') || '');
                document.getElementById('detailRecorder').textContent = button.getAttribute('data-recorder') || 'Unknown';

                const notes = button.getAttribute('data-notes') || '';
                document.getElementById('detailNotes').textContent = notes ? notes : 'No notes were provided for this usage entry.';

                const photoContainer = document.getElementById('detailPhoto');
                const photoUrl = button.getAttribute('data-photo') || '';
                if (photoUrl) {
                    photoContainer.innerHTML = '<img src="' + photoUrl + '" alt="Material usage photo" class="img-fluid rounded-3 border" style="max-height: 240px; object-fit: cover;">';
                } else {
                    photoContainer.innerHTML = '<div class="text-muted">No photo was submitted for this usage entry.</div>';
                }
            });
        }

        document.querySelectorAll('.inventory-delete-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                Swal.fire({
                    title: 'Remove specific record profile?',
                    text: 'This operation is absolute and cannot be instantly undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Delete Structural Entry'
                }).then(function (result) {
                    if (result.isConfirmed) { form.submit(); }
                });
            });
        });

        function attachModalPaginationHandlers() {
            document.querySelectorAll('.modal .pagination a').forEach(function (link) {
                link.onclick = null;
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    const modal = link.closest('.modal');
                    const href = link.getAttribute('href');

                    if (!modal || !href) {
                        return;
                    }

                    const modalBody = modal.querySelector('.modal-body');
                    const paginationContainer = modal.querySelector('.inventory-modal-pagination');

                    if (!modalBody || !paginationContainer) {
                        return;
                    }

                    modalBody.classList.add('opacity-50');

                    fetch(href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(function (response) {
                            return response.text();
                        })
                        .then(function (html) {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const refreshedModal = doc.getElementById(modal.id);

                            if (!refreshedModal) {
                                window.location.href = href;
                                return;
                            }

                            const refreshedBody = refreshedModal.querySelector('.modal-body');
                            const refreshedPagination = refreshedModal.querySelector('.inventory-modal-pagination');

                            if (refreshedBody) {
                                modalBody.innerHTML = refreshedBody.innerHTML;
                            }

                            if (refreshedPagination && paginationContainer) {
                                paginationContainer.innerHTML = refreshedPagination.innerHTML;
                            }

                            window.history.pushState({}, '', href);
                            attachModalPaginationHandlers();
                            modalBody.classList.remove('opacity-50');
                        })
                        .catch(function () {
                            window.location.href = href;
                        });
                });
            });
        }

        attachModalPaginationHandlers();
    });
</script>
@endsection