@extends('layouts.supervisor')

@section('title', 'Material Tracking - D&G Construction Monitor')
@section('page_title', 'Material Tracking')

@section('content')
<div class="container-fluid p-0">
    {{-- Dynamic Metrics Dashboard Summary Group --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white" style="border-radius: 12px;">
                <small class="text-muted text-uppercase tracking-wider fw-bold" style="font-size: 10px;">Active Deliveries</small>
                <h3 class="heading-syne fw-extrabold text-success mt-2 mb-1" style="font-size: 28px;">
                    {{ $metrics['active_deliveries'] ?? 0 }}
                </h3>
                <small class="text-muted" style="font-size: 11px;">This week</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white" style="border-radius: 12px;">
                <small class="text-muted text-uppercase tracking-wider fw-bold" style="font-size: 10px;">Low Stock Alerts</small>
                <h3 class="heading-syne fw-extrabold text-dark mt-2 mb-1" style="font-size: 28px;">
                    {{ $metrics['low_stock_alerts'] ?? 0 }}
                </h3>
                <small class="text-muted" style="font-size: 11px;">Immediate reorder needed</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white" style="border-radius: 12px;">
                <small class="text-muted text-uppercase tracking-wider fw-bold" style="font-size: 10px;">Material Asset Value</small>
                <h3 class="heading-syne fw-extrabold text-success mt-2 mb-1" style="font-size: 28px;">
                    ₱{{ isset($metrics['total_value']) ? number_format($metrics['total_value'] / 1000000, 1) . 'M' : '0.0M' }}
                </h3>
                <small class="text-muted" style="font-size: 11px;">Total allocated to site</small>
            </div>
        </div>
    </div>

    {{-- Stock Inventory Feed Section --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h6 class="heading-syne fw-bold m-0 text-dark text-uppercase tracking-wider" style="font-size: 14px;">
                Inventory Status
            </h6>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="d-flex flex-column gap-3 mt-2">
                @if(isset($inventory) && $inventory->count() > 0)
                    @foreach($inventory as $item)
                        <div class="p-3 border rounded-3 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2" style="border-color: #f1f3f5 !important;">
                            <div>
                                <h6 class="mb-1 text-dark fw-bold" style="font-size: 13px;">{{ $item->name }}</h6>
                                <p class="mb-0 text-muted" style="font-size: 11px;">
                                    Delivered: {{ $item->delivered }} {{ $item->unit }} · Used: {{ $item->used }} {{ $item->unit }}
                                </p>
                            </div>
                            <div class="text-end">
                                <span class="text-{{ $item->status_color ?? 'success' }} fw-bold" style="font-size: 11px;">
                                    {{ $item->status_text }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-4 text-center text-muted fst-italic border rounded-3 bg-light-subtle" style="font-size: 13px;">
                        No current material storage allocations managed for this structure block.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Transaction Logging Section --}}
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h6 class="heading-syne fw-bold m-0 text-dark text-uppercase tracking-wider" style="font-size: 14px;">
                Log Material Delivery
            </h6>
        </div>
        <div class="card-body px-4 pb-4">
            <form action="{{ route('supervisor.materials.log') }}" method="POST" class="mt-2">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-muted fw-bold" style="font-size: 11px;">Material Type</label>
                        <select name="material_id" class="form-select border p-2 text-muted" style="font-size: 13px; border-radius: 6px;" required>
                            <option value="" selected disabled>Select material items...</option>
                            @if(isset($materials_list))
                                @foreach($materials_list as $material)
                                    <option value="{{ $material->id }}">{{ $material->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted fw-bold" style="font-size: 11px;">Quantity</label>
                        <input type="number" name="quantity" step="any" class="form-control border p-2" placeholder="0.00" style="font-size: 13px; border-radius: 6px;" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted fw-bold" style="font-size: 11px;">Unit measurement</label>
                        <select name="unit" class="form-select border p-2 text-muted" style="font-size: 13px; border-radius: 6px;" required>
                            <option value="tons">tons</option>
                            <option value="bags">bags</option>
                            <option value="cu_m">m³</option>
                            <option value="pcs">pieces</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted fw-bold" style="font-size: 11px;">Supplier / Vendor Entity Name</label>
                        <input type="text" name="supplier_name" class="form-control border p-2" placeholder="Enter source supplier..." style="font-size: 13px; border-radius: 6px;" required>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success heading-syne fw-bold px-4 py-2" style="border-radius: 8px; font-size: 13px; background-color: #198754; border: none;">
                        Log Receipt Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection