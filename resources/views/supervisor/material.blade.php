@extends('layouts.supervisor')

@section('title', 'Material Tracking - D&G Construction Monitor')
@section('page_title', 'Material Tracking')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white" style="border-radius: 16px;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.16em;">Active Deliveries</small>
                <h3 class="fw-bold text-success mt-2 mb-1" style="font-size: 28px;">{{ $metrics['active_deliveries'] ?? 0 }}</h3>
                <small class="text-muted">This week</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white" style="border-radius: 16px;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.16em;">Low Stock Alerts</small>
                <h3 class="fw-bold text-dark mt-2 mb-1" style="font-size: 28px;">{{ $metrics['low_stock_alerts'] ?? 0 }}</h3>
                <small class="text-muted">Immediate reorder needed</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white" style="border-radius: 16px;">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.16em;">Material Asset Value</small>
                <h3 class="fw-bold text-success mt-2 mb-1" style="font-size: 28px;">₱{{ isset($metrics['total_value']) ? number_format($metrics['total_value'] / 1000000, 1) . 'M' : '0.0M' }}</h3>
                <small class="text-muted">Total allocated to site</small>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-1">Inventory Status</h5>
            <p class="text-muted small mb-4">Monitor stock levels for your assigned site resources.</p>
            <div class="d-flex flex-column gap-3">
                @if(isset($inventory) && $inventory->count() > 0)
                    @foreach($inventory as $item)
                        <div class="p-3 border rounded-3 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2" style="border-color: #eef2e5 !important;">
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
                    <div class="p-4 text-center text-muted border rounded-3 bg-light-subtle">
                        No current material storage allocations managed for this structure block.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-1">Log Material Delivery</h5>
            <p class="text-muted small mb-4">Capture incoming materials and supplier details.</p>
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
                    <button type="submit" class="btn btn-success px-4 py-2 fw-semibold">
                        Log Receipt Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection