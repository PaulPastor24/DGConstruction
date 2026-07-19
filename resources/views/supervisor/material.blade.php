@extends('layouts.supervisor')

@section('title', 'Material Tracking - D&G Construction Monitor')
@section('page_title', 'Material Tracking')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    brand: {
                        dark: '#2a4028',
                        green: '#365233',
                        accent: '#4b6b46',
                    }
                },
                fontFamily: {
                    heading: ['"Syne"', 'sans-serif'],
                    sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                },
                borderRadius: {
                    'card': '16px',
                    'btn': '12px',
                    'input': '10px',
                },
                boxShadow: {
                    'saas': '0 8px 24px rgba(15, 23, 42, 0.06)',
                }
            }
        }
    }
</script>

<style>
    /* Supervisor Material Tracking - compact mobile layout */
    [x-cloak] { display: none !important; }

    .material-stat-card,
    .material-inventory-card,
    .material-recent-card {
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .material-stat-card:hover,
    .material-inventory-card:hover,
    .material-recent-card:hover {
        transform: translateY(-1px);
    }

    @media (max-width: 640px) {
        .material-page-shell {
            padding: 0.15rem 0 0.75rem !important;
            background: transparent !important;
        }

        .material-metric-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 0.75rem !important;
            margin-bottom: 1rem !important;
        }

        .material-stat-card {
            min-height: 112px !important;
            padding: 0.85rem !important;
            border-radius: 18px !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            justify-content: space-between !important;
            gap: 0.65rem !important;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.055) !important;
        }

        .material-stat-card .space-y-1 {
            width: 100% !important;
            order: 2 !important;
        }

        .material-stat-card dt {
            font-size: 0.6rem !important;
            line-height: 1.2 !important;
            letter-spacing: 0.07em !important;
        }

        .material-stat-card dd {
            margin-top: 0.16rem !important;
            font-size: 1.48rem !important;
            line-height: 1 !important;
        }

        .material-stat-card span {
            margin-top: 0.18rem !important;
            font-size: 0.66rem !important;
            line-height: 1.25 !important;
        }

        .material-stat-icon {
            width: 40px !important;
            height: 40px !important;
            padding: 0 !important;
            border-radius: 14px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            align-self: flex-end !important;
            margin-bottom: -0.3rem !important;
            order: 1 !important;
        }

        .material-stat-icon i {
            font-size: 1.15rem !important;
        }

        .material-main-grid {
            gap: 1rem !important;
        }

        .material-stack {
            gap: 1rem !important;
        }

        .material-inventory-card,
        .material-recent-card {
            border-radius: 18px !important;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.055) !important;
        }

        .material-filter-panel {
            padding: 0.9rem !important;
        }

        .material-filter-form {
            gap: 0.7rem !important;
        }

        .material-filter-search {
            max-width: 100% !important;
            width: 100% !important;
        }

        .material-filter-search input {
            height: 42px !important;
            font-size: 0.82rem !important;
            border-radius: 13px !important;
            background: #ffffff !important;
        }

        .material-filter-controls {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 0.5rem !important;
            justify-content: center !important;
            align-items: stretch !important;
        }

        .material-filter-controls > .relative {
            flex: 1 1 0 !important;
            min-width: 0 !important;
            max-width: none !important;
        }

        .material-filter-controls select {
            width: 100% !important;
            min-height: 42px !important;
            border-radius: 13px !important;
            font-size: 0.82rem !important;
        }

        .material-mobile-list > div {
            padding: 1rem !important;
        }

        .material-mobile-list h4 {
            font-size: 0.9rem !important;
            line-height: 1.25 !important;
        }

        .material-mobile-list .grid {
            gap: 0.45rem !important;
            padding: 0.75rem !important;
        }

        .material-card-footer {
            padding: 0.85rem !important;
            gap: 0.65rem !important;
        }

        .material-card-footer .pagination {
            justify-content: flex-start !important;
        }
    }

    @media (min-width: 641px) and (max-width: 1024px) {
        .material-metric-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }

        .material-filter-form {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 0.5rem !important;
            justify-content: center !important;
            align-items: stretch !important;
        }

        .material-filter-search {
            flex: 1 1 0 !important;
            min-width: 0 !important;
            max-width: none !important;
        }

        .material-filter-search input {
            width: 100% !important;
        }

        .material-filter-controls {
            display: contents !important;
        }

        .material-filter-controls > .relative {
            flex: 1 1 0 !important;
            min-width: 0 !important;
        }

        .material-filter-controls select {
            width: 100% !important;
        }
    }

    @media (max-width: 640px) {
        .material-toggle-bar .inline-flex {
            width: 100% !important;
        }

        .material-toggle-bar button {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
            font-size: 0.75rem !important;
        }

        .material-toggle-bar .gap-2 {
            gap: 0.4rem !important;
        }

        .material-filter-panel {
            padding: 0.75rem !important;
        }

        .material-filter-panel .flex.flex-wrap {
            gap: 0.5rem !important;
        }

        .material-filter-controls select,
        .material-filter-search input {
            min-height: 38px !important;
            font-size: 0.82rem !important;
            padding-top: 0.4rem !important;
            padding-bottom: 0.4rem !important;
        }

        .material-filter-panel .btn,
        .material-filter-panel button {
            min-height: 38px !important;
            font-size: 0.82rem !important;
        }

        .material-inventory-card .table-responsive {
            font-size: 0.82rem !important;
        }

        .material-inventory-card .table th,
        .material-inventory-card .table td {
            padding: 0.6rem 0.5rem !important;
        }

        .material-mobile-list > div {
            padding: 0.85rem !important;
        }

        .material-card-footer {
            padding: 0.75rem !important;
            font-size: 0.78rem !important;
        }

        .material-card-footer .pagination .page-link {
            min-width: 32px !important;
            min-height: 32px !important;
            font-size: 0.72rem !important;
            padding: 0.25rem 0.4rem !important;
        }

        .material-modal {
            max-width: calc(100vw - 1rem) !important;
            margin: 0.5rem !important;
        }

        .material-modal .modal-body {
            padding: 0.85rem !important;
            max-height: calc(100vh - 110px) !important;
        }

        .material-modal .modal-header {
            padding: 0.85rem !important;
        }

        .material-modal .modal-header h3 {
            font-size: 1rem !important;
        }

        .material-modal .grid {
            gap: 0.75rem !important;
        }

        .material-modal .grid > div {
            padding-left: 0.25rem !important;
            padding-right: 0.25rem !important;
        }

        .material-modal input,
        .material-modal select,
        .material-modal textarea {
            font-size: 0.9rem !important;
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }

        .material-modal .btn {
            min-height: 40px !important;
            font-size: 0.85rem !important;
            padding-left: 0.85rem !important;
            padding-right: 0.85rem !important;
        }

        .material-modal .space-y-6 {
            gap: 0.85rem !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const forms = [
            document.getElementById('material-filters-form'),
            document.getElementById('requests-filters-form'),
            document.getElementById('usage-filters-form'),
            document.getElementById('material-sidebar-filters')
        ].filter(Boolean);

        forms.forEach(function (form) {
            const searchInput = form.querySelector('input[name="search"], input[name="usage_search"]');
            const selects = form.querySelectorAll('select');

            if (searchInput) {
                let timer;
                searchInput.addEventListener('input', function () {
                    clearTimeout(timer);
                    timer = setTimeout(function () {
                        form.submit();
                    }, 400);
                });
            }

            selects.forEach(function (select) {
                select.addEventListener('change', function () {
                    form.submit();
                });
            });
        });
    });
</script>

<div class="material-page-shell min-h-screen bg-[#f8fafc] font-sans antialiased text-gray-900 p-1 sm:p-6 lg:p-8" x-data="{ openUsageModal: false, openRequestModal: false, selectedMaterialId: null, selectedUnit: '', selectedPhaseId: null, previewUrl: '', activeTab: '{{ request('tab', 'inventory') }}', previewImage(event) { const file = event.target.files && event.target.files[0]; if (!file) { this.previewUrl = ''; return; } const reader = new FileReader(); reader.onload = (e) => { this.previewUrl = e.target.result; }; reader.readAsDataURL(file); } }">
    

    <div class="material-metric-grid grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-5 sm:mb-8">
        <div class="material-stat-card bg-white rounded-card p-4 sm:p-6 shadow-saas border border-gray-100 flex items-center justify-between transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
            <div class="space-y-1">
                <dt class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Materials</dt>
                <dd class="text-3xl font-extrabold tracking-tight text-gray-900 font-heading">{{ $metrics['total_materials'] ?? '32' }}</dd>
                <span class="text-xs text-gray-400 block">Registered items</span>
            </div>
            <div class="material-stat-icon p-4 bg-green-50 rounded-xl text-brand-dark">
                <i class="bi bi-box-seam text-2xl"></i>
            </div>
        </div>

        <div class="material-stat-card bg-white rounded-card p-4 sm:p-6 shadow-saas border border-gray-100 flex items-center justify-between transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
            <div class="space-y-1">
                <dt class="text-xs font-bold uppercase tracking-wider text-gray-400">Materials Used</dt>
                <dd class="text-3xl font-extrabold tracking-tight text-gray-900 font-heading">{{ $metrics['materials_used'] ?? '0' }}</dd>
                <span class="text-xs text-gray-400 block">Active project allocation</span>
            </div>
            <div class="material-stat-icon p-4 bg-emerald-50 rounded-xl text-brand-accent">
                <i class="bi bi-truck text-2xl"></i>
            </div>
        </div>

        <div class="material-stat-card bg-white rounded-card p-4 sm:p-6 shadow-saas border border-gray-100 flex items-center justify-between transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
            <div class="space-y-1">
                <dt class="text-xs font-bold uppercase tracking-wider text-gray-400">Low Stock</dt>
                <dd class="text-3xl font-extrabold tracking-tight text-orange-600 font-heading">{{ $metrics['low_stock_alerts'] ?? '3' }}</dd>
                <span class="text-xs text-gray-400 block">Requires attention</span>
            </div>
            <div class="material-stat-icon p-4 bg-orange-50 rounded-xl text-orange-600">
                <i class="bi bi-exclamation-triangle text-2xl"></i>
            </div>
        </div>

        <div class="material-stat-card bg-white rounded-card p-4 sm:p-6 shadow-saas border border-gray-100 flex items-center justify-between transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
            <div class="space-y-1">
                <dt class="text-xs font-bold uppercase tracking-wider text-gray-400">Critical Materials</dt>
                <dd class="text-3xl font-extrabold tracking-tight text-red-600 font-heading">{{ $metrics['critical_materials'] ?? '0' }}</dd>
                <span class="text-xs text-gray-400 block">Urgent reorder required</span>
            </div>
            <div class="material-stat-icon p-4 bg-red-50 rounded-xl text-red-600">
                <i class="bi bi-patch-exclamation text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="material-main-grid grid grid-cols-1 lg:grid-cols-1 gap-5 lg:gap-8 items-start">

        <div class="material-stack space-y-5 lg:space-y-8">

            <!-- ===================== INVENTORY PANEL ===================== -->
            <div x-show="activeTab === 'inventory'" class="material-inventory-card bg-white rounded-card border border-gray-200 shadow-saas overflow-hidden">
                @include('partials.supervisor.material-toggle')

                <div class="material-filter-panel p-4 sm:p-6 border-b border-gray-200 bg-white">
                    <form id="material-filters-form" method="GET" action="{{ route('supervisor.materials') }}" class="material-filter-form flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between w-full">
                        <input type="hidden" name="project_id" value="{{ optional($selectedProject)->project_id }}">
                        <input type="hidden" name="phase_id" value="{{ optional($selectedPhase)->phase_id }}">
                        <input type="hidden" name="tab" :value="activeTab">
                        <div class="material-filter-search relative flex-1 max-w-md">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search materials..." class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-input bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-dark transition">
                        </div>

                        <div class="material-filter-controls flex flex-wrap items-center gap-3">
                            <div class="relative">
                                <select name="status" class="appearance-none bg-white pl-4 pr-10 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-input shadow-saas focus:outline-none focus:ring-2 focus:ring-brand-dark transition">
                                    <option value="">All Statuses</option>
                                    <option value="available" @selected($selectedStatus === 'available')>Available</option>
                                    <option value="low_stock" @selected($selectedStatus === 'low_stock')>Low Stock</option>
                                    <option value="critical" @selected($selectedStatus === 'critical')>Critical</option>
                                    <option value="out_of_stock" @selected($selectedStatus === 'out_of_stock')>Out of Stock</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                    <i class="bi bi-chevron-down text-xs"></i>
                                </div>
                            </div>

                            <div class="relative">
                                <select name="project_id" class="appearance-none bg-white pl-4 pr-10 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-input shadow-saas focus:outline-none focus:ring-2 focus:ring-brand-dark transition">
                                    @foreach($assignedProjects as $project)
                                        <option value="{{ $project->project_id }}" @selected(optional($selectedProject)->project_id == $project->project_id)>{{ $project->project_name }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                    <i class="bi bi-chevron-down text-xs"></i>
                                </div>
                            </div>

                            <div class="relative">
                                <select name="phase_id" class="appearance-none bg-white pl-4 pr-10 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-input shadow-saas focus:outline-none focus:ring-2 focus:ring-brand-dark transition">
                                    <option value="">All Phases</option>
                                    @foreach($projectPhases as $phase)
                                        <option value="{{ $phase->phase_id }}" @selected(optional($selectedPhase)->phase_id == $phase->phase_id)>{{ $phase->phase_name }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                    <i class="bi bi-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full table-fixed border-collapse text-left text-sm">
                        <colgroup>
                            <col class="w-[30%]">
                            <col class="w-[10%]">
                            <col class="w-[15%]">
                            <col class="w-[15%]">
                            <col class="w-[15%]">
                            <col class="w-[15%]">
                        </colgroup>
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Material</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Unit</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Planned Allocation</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Used</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Remaining</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @if(isset($inventory) && $inventory->count() > 0)
                                @foreach($inventory as $item)
                                    <tr class="hover:bg-gray-50/70 transition duration-150 group">
                                        <td class="py-4 px-4 align-middle">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="w-9 h-9 rounded-xl bg-green-50 text-brand-dark flex items-center justify-center font-semibold text-base shadow-sm shrink-0">
                                                    <i class="bi bi-box"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-sm font-bold text-gray-900 group-hover:text-brand-dark transition leading-snug break-words">{{ $item->name }}</div>
                                                    <div class="text-xs text-gray-400">SKU-{{ 1000 + $item->id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-5 align-middle text-sm text-gray-500 font-mono font-medium">{{ $item->unit }}</td>
                                        <td class="py-4 px-5 align-middle text-sm text-gray-900 font-bold">
                                            {{ number_format($item->planned, 2) }}
                                        </td>
                                        <td class="py-4 px-5 align-middle text-sm text-gray-600 font-medium">
                                            {{ number_format($item->used, 2) }}
                                        </td>
                                        <td class="py-4 px-5 align-middle text-sm font-bold text-brand-dark font-mono">
                                            {{ number_format(max(0, (float) $item->remaining), 2) }}</td>
                                        <td class="py-4 px-5 align-middle">
                                            @php
                                                $statusColorMap = [
                                                    'success' => 'bg-green-50 text-green-700 ring-green-600/20',
                                                    'warning' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
                                                    'danger' => 'bg-red-50 text-red-700 ring-red-600/20',
                                                    'dark' => 'bg-red-100 text-red-900 ring-red-700/20',
                                                ];
                                                $currentTheme = $statusColorMap[$item->status_color ?? 'success'] ?? $statusColorMap['success'];
                                            @endphp
                                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $currentTheme }}">
                                                {{ $item->status_text ?? 'Available' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <div class="max-w-xs mx-auto text-gray-400">
                                            <i class="bi bi-box-seam text-4xl text-gray-300 block mb-3"></i>
                                            <p class="text-sm font-medium">No material allocations managed found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="material-mobile-list block md:hidden divide-y divide-gray-100">
                    @if(isset($inventory) && $inventory->count() > 0)
                        @foreach($inventory as $item)
                            <div class="p-5 bg-white space-y-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-green-50 text-brand-dark flex items-center justify-center font-semibold">
                                            <i class="bi bi-box"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-900">{{ $item->name }}</h4>
                                        </div>
                                    </div>
                                    @php
                                        $statusColorMap = [
                                            'success' => 'bg-green-50 text-green-700 ring-green-600/20',
                                            'warning' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
                                            'danger' => 'bg-red-50 text-red-700 ring-red-600/20',
                                            'dark' => 'bg-red-100 text-red-900 ring-red-700/20',
                                        ];
                                        $mobileTheme = $statusColorMap[$item->status_color ?? 'success'] ?? $statusColorMap['success'];
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-bold ring-1 ring-inset {{ $mobileTheme }}">
                                        {{ $item->status_text ?? 'Available' }}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-3 gap-2 text-center bg-gray-50 p-3 rounded-xl border border-gray-100">
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Planned</span>
                                        <span class="text-xs font-bold text-gray-800 font-mono">{{ number_format($item->planned, 2) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Used</span>
                                        <span class="text-xs font-bold text-gray-800 font-mono">{{ number_format($item->used, 2) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Remaining</span>
                                        <span class="text-xs font-bold text-brand-dark font-mono">{{ number_format(max(0, (float) $item->remaining), 2) }}</span>
                                    </div>
                                </div>

                                <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                                    <div class="bg-brand-green h-full transition-all duration-500" style="width: {{ ($item->planned > 0) ? min(100, round(($item->used / $item->planned) * 100)) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="material-card-footer p-4 border-t border-gray-100 bg-gray-50/50 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-xs font-medium text-gray-500">
                        @if($inventory->total() > 0)
                            Showing {{ $inventory->firstItem() }} to {{ $inventory->lastItem() }} of {{ $inventory->total() }} entries
                        @else
                            Showing 0 entries
                        @endif
                    </span>
                    <div class="flex items-center gap-1">
                        @if($inventory->hasPages())
                            {{ $inventory->links('pagination::bootstrap-5') }}
                        @else
                            <nav aria-label="Material pagination" class="pagination">
                                <span class="page-item active"><span class="page-link">1</span></span>
                            </nav>
                        @endif
                    </div>
                </div>

            </div>

            <!-- ===================== REQUEST MATERIAL TABLE PANEL ===================== -->
            <div x-show="activeTab === 'requests'" class="material-inventory-card bg-white rounded-card border border-gray-200 shadow-saas overflow-hidden" x-cloak>
                @include('partials.supervisor.material-toggle')

                <div class="material-filter-panel p-4 sm:p-6 border-b border-gray-200 bg-white">
                    <form id="requests-filters-form" method="GET" action="{{ route('supervisor.materials') }}" class="material-filter-form flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between w-full">
                        <input type="hidden" name="project_id" value="{{ optional($selectedProject)->project_id }}">
                        <input type="hidden" name="phase_id" value="{{ optional($selectedPhase)->phase_id }}">
                        <input type="hidden" name="tab" :value="activeTab">

                        <div class="material-filter-search relative flex-1 max-w-md">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search requested materials..." class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-input bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-dark transition">
                        </div>

                        <div class="material-filter-controls flex flex-wrap items-center gap-3">
                            <div class="relative">
                                <select name="request_status" class="appearance-none bg-white pl-4 pr-10 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-input shadow-saas focus:outline-none focus:ring-2 focus:ring-brand-dark transition">
                                    <option value="">All Statuses</option>
                                    <option value="pending" @selected($requestStatus === 'pending')>Pending</option>
                                    <option value="approved" @selected($requestStatus === 'approved')>Approved</option>
                                    <option value="rejected" @selected($requestStatus === 'rejected')>Rejected</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                    <i class="bi bi-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full table-fixed border-collapse text-left text-sm">
                        <colgroup>
                            <col class="w-[16%]">
                            <col class="w-[26%]">
                            <col class="w-[18%]">
                            <col class="w-[12%]">
                            <col class="w-[14%]">
                            <col class="w-[14%]">
                        </colgroup>
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Request ID</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Material</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Project</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Quantity</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Status</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Requested At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @if(isset($materialRequests) && $materialRequests->count() > 0)
                                @foreach($materialRequests as $req)
                                    <tr class="hover:bg-gray-50/70 transition duration-150 group">
                                        <td class="py-4 px-5 align-middle text-sm font-bold text-gray-900 font-mono">#{{ $req->request_id }}</td>
                                        <td class="py-4 px-5 align-middle">
                                            <div class="text-sm font-bold text-gray-900 leading-snug">{{ $req->material->name ?? 'Unknown Material' }}</div>
                                            <div class="text-xs text-gray-400">{{ $req->unit ?? 'unit' }}</div>
                                        </td>
                                        <td class="py-4 px-5 align-middle text-sm text-gray-500">{{ $req->project->project_name ?? 'N/A' }}</td>
                                        <td class="py-4 px-5 align-middle text-sm font-bold text-gray-900 font-mono">{{ number_format($req->requested_quantity, 2) }}</td>
                                        <td class="py-4 px-5 align-middle">
                                            @php
                                                $reqStatusMap = [
                                                    'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                                    'approved' => 'bg-green-50 text-green-700 ring-green-600/20',
                                                    'rejected' => 'bg-red-50 text-red-700 ring-red-600/20',
                                                ];
                                                $reqTheme = $reqStatusMap[$req->status] ?? 'bg-gray-100 text-gray-700 ring-gray-600/20';
                                            @endphp
                                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $reqTheme }}">
                                                {{ ucfirst($req->status) }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-5 align-middle text-sm text-gray-500">{{ optional($req->created_at)->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <div class="max-w-xs mx-auto text-gray-400">
                                            <i class="bi bi-cart-x text-4xl text-gray-300 block mb-3"></i>
                                            <p class="text-sm font-medium">No material requests submitted yet.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="material-mobile-list block md:hidden divide-y divide-gray-100">
                    @if(isset($materialRequests) && $materialRequests->count() > 0)
                        @foreach($materialRequests as $req)
                            <div class="p-5 bg-white space-y-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900">{{ $req->material->name ?? 'Unknown Material' }}</h4>
                                        <span class="text-xs text-gray-400">#{{ $req->request_id }} &middot; {{ $req->project->project_name ?? 'N/A' }}</span>
                                    </div>
                                    @php
                                        $mStatusMap = [
                                            'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                            'approved' => 'bg-green-50 text-green-700 ring-green-600/20',
                                            'rejected' => 'bg-red-50 text-red-700 ring-red-600/20',
                                        ];
                                        $mTheme = $mStatusMap[$req->status] ?? 'bg-gray-100 text-gray-700 ring-gray-600/20';
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-bold ring-1 ring-inset {{ $mTheme }}">
                                        {{ ucfirst($req->status) }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-center bg-gray-50 p-3 rounded-xl border border-gray-100">
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Quantity</span>
                                        <span class="text-xs font-bold text-gray-800 font-mono">{{ number_format($req->requested_quantity, 2) }} {{ $req->unit ?? '' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Requested</span>
                                        <span class="text-xs font-bold text-gray-800">{{ optional($req->created_at)->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="material-card-footer p-4 border-t border-gray-100 bg-gray-50/50 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-xs font-medium text-gray-500">
                        @if(isset($materialRequests) && $materialRequests->total() > 0)
                            Showing {{ $materialRequests->firstItem() }} to {{ $materialRequests->lastItem() }} of {{ $materialRequests->total() }} requests
                        @else
                            Showing 0 requests
                        @endif
                    </span>
                    <div class="flex items-center gap-1">
                        @if(isset($materialRequests) && $materialRequests->hasPages())
                            {{ $materialRequests->links('pagination::bootstrap-5') }}
                        @endif
                    </div>
                </div>
            </div>

            <!-- ===================== RECENT MATERIAL USAGE PANEL ===================== -->
            <div x-show="activeTab === 'usage'" class="material-inventory-card bg-white rounded-card border border-gray-200 shadow-saas overflow-hidden" x-cloak>
                @include('partials.supervisor.material-toggle')

                <div class="material-filter-panel p-4 sm:p-6 border-b border-gray-200 bg-white">
                    <form id="usage-filters-form" method="GET" action="{{ route('supervisor.materials') }}" class="material-filter-form flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between w-full">
                        <input type="hidden" name="project_id" value="{{ optional($selectedProject)->project_id }}">
                        <input type="hidden" name="phase_id" value="{{ optional($selectedPhase)->phase_id }}">
                        <input type="hidden" name="tab" value="usage">

                        <div class="material-filter-search relative flex-1 max-w-md">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="usage_search" value="{{ $usageSearch ?? '' }}" placeholder="Search usage by material..." class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-input bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-dark transition">
                        </div>

                        <div class="material-filter-controls flex flex-wrap items-center gap-3">
                            <div class="relative">
                                <select name="phase_id" class="appearance-none bg-white pl-4 pr-10 py-2.5 text-sm text-gray-700 border border-gray-200 rounded-input shadow-saas focus:outline-none focus:ring-2 focus:ring-brand-dark transition">
                                    <option value="">All Phases</option>
                                    @foreach($projectPhases as $phase)
                                        <option value="{{ $phase->phase_id }}" @selected(optional($selectedPhase)->phase_id == $phase->phase_id)>{{ $phase->phase_name }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                    <i class="bi bi-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full table-fixed border-collapse text-left text-sm">
                        <colgroup>
                            <col class="w-[26%]">
                            <col class="w-[18%]">
                            <col class="w-[14%]">
                            <col class="w-[16%]">
                            <col class="w-[26%]">
                        </colgroup>
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Material</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Phase</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Quantity</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Date</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Recorded By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @if($recentUsages instanceof \Illuminate\Pagination\LengthAwarePaginator && $recentUsages->count() > 0)
                                @foreach($recentUsages as $usage)
                                    <tr class="hover:bg-gray-50/70 transition duration-150 group">
                                        <td class="py-4 px-5 align-middle">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="w-9 h-9 rounded-xl bg-green-50 text-brand-dark flex items-center justify-center font-semibold text-base shadow-sm shrink-0">
                                                    <i class="bi bi-box"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-sm font-bold text-gray-900 leading-snug break-words">{{ $usage->material->name ?? 'Unknown Material' }}</div>
                                                    <div class="text-xs text-gray-400">{{ $usage->unit ?? 'unit' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-5 align-middle text-sm text-gray-500">{{ $usage->phase->phase_name ?? 'Unknown Phase' }}</td>
                                        <td class="py-4 px-5 align-middle text-sm font-bold text-brand-dark font-mono">-{{ number_format($usage->quantity_used, 2) }}</td>
                                        <td class="py-4 px-5 align-middle text-sm text-gray-500">{{ optional($usage->usage_date)->format('M d, Y') }}</td>
                                        <td class="py-4 px-5 align-middle text-sm text-gray-600">{{ $usage->recorder->name ?? 'Supervisor' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="py-12 text-center">
                                        <div class="max-w-xs mx-auto text-gray-400">
                                            <i class="bi bi-clock-history text-4xl text-gray-300 block mb-3"></i>
                                            <p class="text-sm font-medium">No recent material usage records available.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="material-mobile-list block md:hidden divide-y divide-gray-100">
                    @if($recentUsages instanceof \Illuminate\Pagination\LengthAwarePaginator && $recentUsages->count() > 0)
                        @foreach($recentUsages as $usage)
                            <div class="p-5 bg-white space-y-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-green-50 text-brand-dark flex items-center justify-center font-semibold">
                                            <i class="bi bi-box"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-900">{{ $usage->material->name ?? 'Unknown Material' }}</h4>
                                        </div>
                                    </div>
                                    <span class="text-sm font-bold text-brand-dark font-mono">-{{ number_format($usage->quantity_used, 2) }}</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-center bg-gray-50 p-3 rounded-xl border border-gray-100">
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Phase</span>
                                        <span class="text-xs font-bold text-gray-800">{{ $usage->phase->phase_name ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Date</span>
                                        <span class="text-xs font-bold text-gray-800">{{ optional($usage->usage_date)->format('M d, Y') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">By</span>
                                        <span class="text-xs font-bold text-gray-800">{{ $usage->recorder->name ?? 'Sup' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="material-card-footer p-4 border-t border-gray-100 bg-gray-50/50 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-xs font-medium text-gray-500">
                        @if($recentUsages instanceof \Illuminate\Pagination\LengthAwarePaginator && $recentUsages->total() > 0)
                            Showing {{ $recentUsages->firstItem() }} to {{ $recentUsages->lastItem() }} of {{ $recentUsages->total() }} entries
                        @else
                            Showing 0 entries
                        @endif
                    </span>
                    <div class="flex items-center gap-1">
                        @if($recentUsages instanceof \Illuminate\Pagination\LengthAwarePaginator && $recentUsages->hasPages())
                            @php($recentUsages->setPageName('recent_page'))
                            {{ $recentUsages->links('pagination::bootstrap-5') }}
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>

    <div x-show="openUsageModal" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
         
        <div class="fixed inset-0 bg-black/50 backdrop-blur-xs"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div x-show="openUsageModal"
                 @click.away="openUsageModal = false"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                  class="relative transform overflow-hidden rounded-card bg-white text-left shadow-xl transition-all w-full max-w-4xl material-modal border border-gray-100">
                 
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                    <div class="flex items-center gap-2 text-brand-dark">
                        <i class="bi bi-pencil-square text-lg"></i>
                        <h3 class="text-lg font-bold text-gray-900 font-heading">Record Material Usage</h3>
                    </div>
                    <button @click="openUsageModal = false" class="text-gray-400 hover:text-gray-600 transition p-1.5 rounded-lg hover:bg-gray-50">
                        <i class="bi bi-x-lg text-sm"></i>
                    </button>
                </div>

                <form id="material_usage_form" action="{{ route('supervisor.materials.log') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6 bg-white" x-init="if (selectedMaterialId) { $nextTick(() => { const materialSelect = $refs.materialSelect; if(materialSelect){ materialSelect.value = selectedMaterialId; } }); }" x-effect="if (selectedMaterialId && $refs.materialSelect) selectedUnit = $refs.materialSelect.selectedOptions[0]?.dataset.unit || selectedUnit">
                    @csrf
                    <input type="hidden" name="form_type" value="usage">
                    <input type="hidden" name="project_id" value="{{ optional($selectedProject)->project_id }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Project</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i class="bi bi-building"></i>
                                </span>
                                <input type="text" class="w-full pl-10 pr-4 py-3 text-sm border border-gray-200 rounded-input bg-gray-50 text-gray-600 outline-none font-medium" value="{{ optional($selectedProject)->project_name ?? 'No project selected' }}" readonly>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Construction Phase</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i class="bi bi-diagram-2"></i>
                                </span>
                                <select x-model="selectedPhaseId" name="phase_id" class="w-full appearance-none bg-white pl-10 pr-10 py-3 text-sm border border-gray-200 rounded-input focus:outline-none focus:ring-2 focus:ring-brand-dark transition font-medium text-gray-700" required>
                                    <option value="" disabled selected>Select phase...</option>
                                    @foreach($projectPhasesForUsage as $phase)
                                        <option value="{{ $phase->phase_id }}">{{ $phase->phase_name }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                    <i class="bi bi-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Material</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i class="bi bi-box"></i>
                                </span>
                                <select x-ref="materialSelect" x-model="selectedMaterialId" name="material_id" @change="selectedUnit = $event.target.selectedOptions[0].dataset.unit || ''" class="w-full appearance-none bg-white pl-10 pr-10 py-3 text-sm border border-gray-200 rounded-input focus:outline-none focus:ring-2 focus:ring-brand-dark transition font-medium text-gray-700" required>
                                    <option value="" disabled>Select material...</option>
                                    @foreach($materials_list as $material)
                                        <option value="{{ $material->id }}" data-unit="{{ $material->unit }}" @if(old('material_id') == $material->id) selected @endif>{{ $material->name }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                    <i class="bi bi-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="col-span-2 sm:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Quantity Used</label>
                                <input type="number" name="quantity_used" step="0.01" min="0.01" inputmode="decimal" class="w-full px-4 py-3 text-sm border border-gray-200 rounded-input bg-white focus:outline-none focus:ring-2 focus:ring-brand-dark transition text-gray-900 font-bold" placeholder="0.00" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Unit</label>
                                <div class="flex h-[48px] w-full items-center justify-center rounded-input border border-gray-200 bg-gray-50 px-3 text-sm font-bold text-gray-700">
                                    <span x-text="selectedUnit || '—'"></span>
                                </div>
                                <input type="hidden" name="unit" x-model="selectedUnit">
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Usage Date</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i class="bi bi-calendar3 text-xs"></i>
                                </span>
                                <input type="date" name="usage_date" class="w-full pl-10 pr-4 py-3 text-sm border border-gray-200 rounded-input bg-white focus:outline-none focus:ring-2 focus:ring-brand-dark transition font-medium text-gray-800" value="{{ now()->toDateString() }}" required>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Remarks / Notes</label>
                            <textarea name="remarks" rows="3" maxlength="1000" class="w-full px-4 py-3 text-sm border border-gray-200 rounded-input bg-white focus:outline-none focus:ring-2 focus:ring-brand-dark transition placeholder-gray-400 font-medium" placeholder="Specify structural block location, structural columns pouring context logs, etc..."></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Upload Site Photos</label>
                            <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-3">
                                <label class="block cursor-pointer rounded-xl border border-dashed border-gray-200 bg-white p-6 text-center transition hover:border-green-400 hover:bg-gray-50">
                                    <input type="file" name="site_photo" accept="image/png,image/jpeg" class="hidden" @change="previewImage($event)">
                                    <div x-show="!previewUrl" class="flex flex-col items-center justify-center gap-1">
                                        <i class="bi bi-cloud-arrow-up text-2xl text-gray-400 transition mb-1"></i>
                                        <span class="text-xs font-bold text-brand-dark">Select Photo</span>
                                        <span class="text-[9px] text-gray-400">PNG, JPG up to 5MB</span>
                                    </div>
                                    <div x-show="previewUrl" x-cloak class="mt-3">
                                        <img :src="previewUrl" alt="Selected site photo" class="mx-auto w-full max-h-[320px] rounded-xl object-contain">
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="pt-5 border-t border-gray-200 flex items-center justify-end gap-3 bg-white">
                        <button type="button" @click="openUsageModal = false; previewUrl = ''" class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-200 rounded-btn shadow-saas hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-brand-dark rounded-btn shadow-saas hover:bg-green-900 transition hover:scale-[1.01] active:scale-[0.99]">
                            Save Usage
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Request Material Modal -->
    <div x-show="openRequestModal" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
         
        <div class="fixed inset-0 bg-black/50 backdrop-blur-xs"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div x-show="openRequestModal"
                 @click.away="openRequestModal = false"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                  class="relative transform overflow-hidden rounded-card bg-white text-left shadow-xl transition-all w-full max-w-2xl material-modal border border-gray-100">
                  
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                    <div class="flex items-center gap-2 text-brand-dark">
                        <i class="bi bi-cart-plus text-lg"></i>
                        <h3 class="text-lg font-bold text-gray-900 font-heading">Request Material</h3>
                    </div>
                    <button @click="openRequestModal = false" class="text-gray-400 hover:text-gray-600 transition p-1.5 rounded-lg hover:bg-gray-50">
                        <i class="bi bi-x-lg text-sm"></i>
                    </button>
                </div>

                <form id="material_request_form" action="{{ route('supervisor.materials.request') }}" method="POST" class="p-6 space-y-6 bg-white">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ optional($selectedProject)->project_id }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Project</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i class="bi bi-building"></i>
                                </span>
                                <input type="text" class="w-full pl-10 pr-4 py-3 text-sm border border-gray-200 rounded-input bg-gray-50 text-gray-600 outline-none font-medium" value="{{ optional($selectedProject)->project_name ?? 'No project selected' }}" readonly>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Material <span class="text-danger">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i class="bi bi-box"></i>
                                </span>
                                 <select name="material_id" class="w-full appearance-none bg-white pl-10 pr-10 py-3 text-sm border border-gray-200 rounded-input focus:outline-none focus:ring-2 focus:ring-brand-dark transition font-medium text-gray-700" required>
                                     <option value="" disabled selected>Select material...</option>
                                     @foreach($request_materials_list as $material)
                                         <option value="{{ $material->id }}" data-available="{{ $material->current_stock ?? 0 }}" data-unit="{{ $material->unit }}">{{ $material->name }} ({{ $material->current_stock ?? 0 }} {{ $material->unit ?? '' }})</option>
                                     @endforeach
                                 </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                    <i class="bi bi-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="col-span-2 sm:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Requested Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="requested_quantity" step="0.01" min="0.01" inputmode="decimal" class="w-full px-4 py-3 text-sm border border-gray-200 rounded-input bg-white focus:outline-none focus:ring-2 focus:ring-brand-dark transition text-gray-900 font-bold" placeholder="0.00" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Available</label>
                                <div class="flex h-[48px] w-full items-center justify-center rounded-input border border-gray-200 bg-gray-50 px-3 text-sm font-bold text-gray-700">
                                    <span id="requestAvailableQty">--</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Remarks / Justification</label>
                            <textarea name="remarks" rows="3" maxlength="1000" class="w-full px-4 py-3 text-sm border border-gray-200 rounded-input bg-white focus:outline-none focus:ring-2 focus:ring-brand-dark transition placeholder-gray-400 font-medium" placeholder="Explain why this material is needed for the project..."></textarea>
                        </div>
                    </div>

                    <div class="pt-5 border-t border-gray-200 flex items-center justify-end gap-3 bg-white">
                        <button type="button" @click="openRequestModal = false" class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-200 rounded-btn shadow-saas hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-brand-dark rounded-btn shadow-saas hover:bg-green-900 transition hover:scale-[1.01] active:scale-[0.99]">
                            <i class="bi bi-send me-1"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

    {{-- close material-page-shell --}}
    </div>

<script>
    function showMaterialFeedback(type, message) {
        Swal.fire({
            icon: type === 'success' ? 'success' : 'error',
            title: type === 'success' ? 'Success' : 'Please check',
            text: message,
            showConfirmButton: true,
            confirmButtonColor: '#166534',
            timer: type === 'success' ? 3000 : 4000,
            timerProgressBar: true
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const usageForm = document.getElementById('material_usage_form');
        const requestForm = document.getElementById('material_request_form');

        if (usageForm) {
            usageForm.addEventListener('submit', function (event) {
                const quantityInput = usageForm.querySelector('input[name="quantity_used"]');
                const phaseSelect = usageForm.querySelector('select[name="phase_id"]');
                const materialSelect = usageForm.querySelector('select[name="material_id"]');
                const usageDateInput = usageForm.querySelector('input[name="usage_date"]');
                const quantityValue = quantityInput ? parseFloat(quantityInput.value) : NaN;

                if (Number.isNaN(quantityValue) || quantityValue <= 0) {
                    event.preventDefault();
                    showMaterialFeedback('error', 'Please enter a positive quantity for material usage.');
                    quantityInput?.focus();
                    return;
                }

                if (!phaseSelect?.value || !materialSelect?.value || !usageDateInput?.value) {
                    event.preventDefault();
                    showMaterialFeedback('error', 'Please complete all required usage fields before saving.');
                }
            });

            usageForm.addEventListener('submit', function () {
                showSupervisorLoading('Recording material usage...');
            });
        }

        if (requestForm) {
            const materialSelect = requestForm.querySelector('select[name="material_id"]');
            const availableDisplay = document.getElementById('requestAvailableQty');

            if (materialSelect) {
                materialSelect.addEventListener('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    const available = selectedOption?.getAttribute('data-available') || '0';
                    if (availableDisplay) {
                        availableDisplay.textContent = parseFloat(available).toFixed(2);
                    }
                });
            }

            requestForm.addEventListener('submit', function (event) {
                const materialSelect = requestForm.querySelector('select[name="material_id"]');
                const quantityInput = requestForm.querySelector('input[name="requested_quantity"]');
                const availableDisplay = document.getElementById('requestAvailableQty');

                if (!materialSelect?.value) {
                    event.preventDefault();
                    showMaterialFeedback('error', 'Please select a material to request.');
                    return;
                }

                const requestedQty = parseFloat(quantityInput?.value || '0');
                const availableQty = parseFloat(availableDisplay?.textContent || '0');

                if (Number.isNaN(requestedQty) || requestedQty <= 0) {
                    event.preventDefault();
                    showMaterialFeedback('error', 'Please enter a valid requested quantity greater than zero.');
                    return;
                }

                if (requestedQty > availableQty) {
                    event.preventDefault();
                    showMaterialFeedback('error', `Requested quantity (${requestedQty.toFixed(2)}) exceeds available stock (${availableQty.toFixed(2)}). Please adjust your request.`);
                    return;
                }

                showSupervisorLoading('Submitting material request...');
            });
        }

        @if(session('success'))
            showMaterialFeedback('success', @json(session('success')));
        @endif

        @if(session('error'))
            showMaterialFeedback('error', @json(session('error')));
        @endif
    });
</script>

@endsection