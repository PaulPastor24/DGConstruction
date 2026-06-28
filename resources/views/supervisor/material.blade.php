@extends('layouts.supervisor')

@section('title', 'Material Tracking - D&G Construction Monitor')
@section('page_title', 'Material Tracking')

@section('content')
<div class="d-flex flex-column gap-3">
    <section class="page-card">
        <div class="page-hero">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div>
                    <div class="eyebrow">Site Operations</div>
                    <h1 class="page-title mb-2">Material Monitoring</h1>
                    <p class="page-subtitle mb-0">Track deliveries, stock health, and site material value in the same workspace language as the dashboard.</p>
                </div>
                <span class="badge rounded-pill badge-soft">
                    <i class="bi bi-box-seam me-2"></i>Inventory overview
                </span>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-12 col-lg-4">
                    <div class="stat-card">
                        <div class="stat-title">Active Deliveries</div>
                        <div class="stat-value">{{ $metrics['active_deliveries'] ?? 0 }}</div>
                        <div class="stat-meta">This week</div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="stat-card">
                        <div class="stat-title">Low Stock Alerts</div>
                        <div class="stat-value">{{ $metrics['low_stock_alerts'] ?? 0 }}</div>
                        <div class="stat-meta">Immediate reorder needed</div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="stat-card">
                        <div class="stat-title">Material Asset Value</div>
                        <div class="stat-value">₱{{ isset($metrics['total_value']) ? number_format($metrics['total_value'] / 1000000, 1) . 'M' : '0.0M' }}</div>
                        <div class="stat-meta">Total allocated to site</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-card mb-3">
        <div class="section-card-body">
            <h5 class="fw-bold mb-1">Inventory Status</h5>
            <p class="text-muted small mb-3">Monitor stock levels for your assigned site resources.</p>
            <div class="d-flex flex-column gap-3">
                @if(isset($inventory) && $inventory->count() > 0)
                    @foreach($inventory as $item)
                        <div class="dashboard-surface d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h6 class="mb-1 text-dark fw-semibold">{{ $item->name }}</h6>
                                <p class="mb-0 text-muted small">
                                    Delivered: {{ $item->delivered }} {{ $item->unit }} · Used: {{ $item->used }} {{ $item->unit }}
                                </p>
                            </div>
                            <div class="text-end">
                                <span class="fw-semibold text-{{ $item->status_color ?? 'success' }} small">
                                    {{ $item->status_text }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="dashboard-empty-state">
                        <div class="dashboard-empty-icon"><i class="bi bi-box-seam"></i></div>
                        <div>No current material storage allocations are managed for this structure block.</div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="section-card">
        <div class="section-card-body">
            <h5 class="fw-bold mb-1">Log Material Delivery</h5>
            <p class="text-muted small mb-3">Capture incoming materials and supplier details.</p>
            <form action="{{ route('supervisor.materials.log') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-muted fw-semibold small">Material Type</label>
                        <select name="material_id" class="form-select" required>
                            <option value="" selected disabled>Select material items...</option>
                            @if(isset($materials_list))
                                @foreach($materials_list as $material)
                                    <option value="{{ $material->id }}">{{ $material->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted fw-semibold small">Quantity</label>
                        <input type="number" name="quantity" step="any" class="form-control" placeholder="0.00" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted fw-semibold small">Unit measurement</label>
                        <select name="unit" class="form-select" required>
                            <option value="tons">tons</option>
                            <option value="bags">bags</option>
                            <option value="cu_m">m³</option>
                            <option value="pcs">pieces</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted fw-semibold small">Supplier / Vendor Entity Name</label>
                        <input type="text" name="supplier_name" class="form-control" placeholder="Enter source supplier..." required>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary-soft px-4 py-2">
                        Log Receipt Entry
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection