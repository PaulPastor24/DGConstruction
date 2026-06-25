@extends('layouts.admin')

@section('title', 'Materials & Inventory - D&G Construction Monitor')
@section('page_title', 'Materials & Inventory')

@section('content')
<div class="page active" id="pg-inventory">

    <!-- Top Summary Statistics Grid Layout -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card p-3 border-start border-primary border-3 h-100">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Active Deliveries</small>
                <div class="h3 fw-black my-1 text-dark">{{ $metrics['active_deliveries'] ?? 0 }}</div>
                <small class="text-muted" style="font-size: 11px;">This week Deliveries</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-start border-danger border-3 h-100">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Low Stock Alerts</small>
                <div class="h3 fw-black my-1 text-dark">{{ $metrics['low_stock_alerts'] ?? 0 }}</div>
                <small class="text-danger" style="font-size: 11px;">Immediate reorder needed Alerts</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-start border-success border-3 h-100">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Total Inventory Value</small>
                <div class="h3 fw-black my-1 text-dark">₱{{ number_format($metrics['total_value'] ?? 0, 2) }}M</div>
                <small class="text-success" style="font-size: 11px;">Overall Inventory</small>
            </div>
        </div>
    </div>

    <!-- Main Workspace Layout Grid -->
    <div class="row match-height">
        
        <!-- Left Side Panel: Stock Tracking & Volumetric Status Summary -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <div class="card-title h6 mb-1">Inventory Status</div>
                        <small class="text-muted d-block" style="font-size: 11px;">Switch by project to review local stock, not just the overall total</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label for="inventory-location" class="fw-bold text-uppercase text-muted mb-0" style="font-size: 10px;">Project</label>
                            <select name="project_id" class="form-control">
                                <option value="">Overall</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->project_id }}">{{ $project->project_name }}</option>
                                @endforeach
                            </select>   
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($inventoryItems ?? [] as $item)
                            <div class="list-group-item p-3 border-bottom d-flex align-items-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="p-2 rounded bg-light" style="font-size: 18px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        {{ $item->icon_glyph ?? '📦' }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0" style="font-size: 13px;">
                                            {{ $item->name }}
                                            @if(($item->remaining_percentage ?? 100) <= ($item->low_stock_threshold ?? 15))
                                                <span class="text-danger ps-1" style="font-size: 11px; font-weight: normal;">Low stock</span>
                                            @endif
                                        </div>
                                        <small class="text-muted" style="font-size: 11px;">
                                            Across projects: Delivered: {{ number_format($item->delivered_quantity) }} {{ $item->unit }} &middot; Used: {{ number_format($item->used_quantity) }} {{ $item->unit }}
                                        </small>
                                    </div>
                                </div>
                                <div class="text-end" style="width: 120px;">
                                    <small class="text-muted d-block mb-1" style="font-size: 11px;">{{ $item->remaining_percentage }}% remaining</small>
                                    <div class="progress-bar-wrap" style="height: 6px; background-color: rgba(0,0,0,0.05); border-radius: 4px; overflow: hidden;">
                                        <div class="progress-bar-fill" style="height: 100%; width: {{ $item->remaining_percentage }}%; background-color: {{ ($item->remaining_percentage <= ($item->low_stock_threshold ?? 15)) ? 'var(--red, #ef4444)' : 'var(--green, #22c55e)' }};"></div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-5 text-center text-muted">
                                <p class="mb-0">No raw tracking materials or volumetric inventory mapped to this location hub.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side Panel: Entry Inbound Management & Transport Hauling Pipelines -->
        <div class="col-lg-6 d-flex flex-column gap-4 mb-4">
            
            <!-- Widget 1: Log Material Delivery Form -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title h6 mb-0">Log Material Delivery</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.inventory.store-delivery') }}" method="POST">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label text-uppercase text-muted fw-bold mb-1" style="font-size: 10px;">Material</label>
                                <select class="form-select form-select-sm" name="material_id" required>
                                    <option value="">Select Material...</option>
                                    @foreach($availableMaterials as $mat)
                                        <option value="{{ $mat->id }}">{{ $mat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-uppercase text-muted fw-bold mb-1" style="font-size: 10px;">Quantity</label>
                                <input type="number" class="form-control form-control-sm" name="quantity" min="1" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-uppercase text-muted fw-bold mb-1" style="font-size: 10px;">Unit</label>
                                <select class="form-select form-select-sm" name="unit" required>
                                    <option value="tons">tons</option>
                                    <option value="m³">m³</option>
                                    <option value="pcs">pcs</option>
                                    <option value="bags">bags</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-uppercase text-muted fw-bold mb-1" style="font-size: 10px;">Supplier</label>
                                <div class="d-flex gap-2">
                                    <input type="text" class="form-control form-control-sm" name="supplier_name" placeholder="Supplier name..." required>
                                    <button type="submit" class="btn btn-sm btn-success px-3">Log</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Widget 2: Hauling Logistics Fleet Queue Table -->
            <div class="card flex-grow-1">
                <div class="card-header">
                    <div class="card-title h6 mb-0">Hauling & Logistics</div>
                </div>
                <div class="table-responsive flex-grow-1">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="table-light text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.5px;">
                            <tr>
                                <th class="ps-3" style="padding: 10px 12px;">Trip</th>
                                <th style="padding: 10px 12px;">Material</th>
                                <th style="padding: 10px 12px;">Truck</th>
                                <th class="pe-3 text-end" style="padding: 10px 12px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($haulingTrips ?? [] as $trip)
                                <tr>
                                    <td class="ps-3 text-muted fw-medium" style="padding: 12px;">{{ $trip->trip_code }}</td>
                                    <td style="padding: 12px; color: var(--muted);">{{ $trip->material_description }}</td>
                                    <td style="padding: 12px; color: var(--muted);">{{ $trip->truck_plate }}</td>
                                    <td class="pe-3 text-end" style="padding: 12px;">
                                        @if($trip->status === 'In Transit')
                                            <span class="badge rounded px-2 py-1" style="font-size: 11px; background-color: rgba(245,158,11,0.08); color: #d97706;">In Transit</span>
                                        @elseif($trip->status === 'Completed')
                                            <span class="badge rounded px-2 py-1" style="font-size: 11px; background-color: rgba(34,197,94,0.08); color: #16a34a;">Completed</span>
                                        @else
                                            <span class="badge rounded px-2 py-1" style="font-size: 11px; background-color: rgba(0,0,0,0.05); color: var(--muted);">Dispatched</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-4 text-center text-muted my-auto">
                                        <small class="d-block py-2">No heavy logistics hauling manifests or transit route trucks operating currently.</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection