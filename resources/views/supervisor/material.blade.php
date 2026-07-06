@extends('layouts.supervisor')

@section('title', 'Material Tracking - D&G Construction Monitor')
@section('page_title', 'Material Tracking')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    brand: {
                        dark: '#166534',    /* Primary Dark Green */
                        green: '#16a34a',   /* Secondary Green */
                        accent: '#10b981',  /* Emerald Accent */
                    }
                },
                fontFamily: {
                    heading: ['"Plus Jakarta Sans"', 'sans-serif'],
                    sans: ['Inter', 'sans-serif'],
                },
                borderRadius: {
                    'card': '16px',
                    'btn': '12px',
                    'input': '10px',
                },
                boxShadow: {
                    'saas': '0 4px 20px -2px rgba(0, 0, 0, 0.05), 0 2px 8px -1px rgba(0, 0, 0, 0.03)',
                }
            }
        }
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const forms = [
            document.getElementById('material-filters-form'),
            document.getElementById('material-sidebar-filters')
        ].filter(Boolean);

        forms.forEach(function (form) {
            const searchInput = form.querySelector('input[name="search"]');
            const statusSelect = form.querySelector('select[name="status"]');
            const projectSelect = form.querySelector('select[name="project_id"]');
            const phaseSelect = form.querySelector('select[name="phase_id"]');

            if (searchInput) {
                let timer;
                searchInput.addEventListener('input', function () {
                    clearTimeout(timer);
                    timer = setTimeout(function () {
                        form.submit();
                    }, 400);
                });
            }

            [statusSelect, projectSelect, phaseSelect].filter(Boolean).forEach(function (select) {
                select.addEventListener('change', function () {
                    form.submit();
                });
            });
        });
    });
</script>

<div class="min-h-screen bg-[#f8fafc] font-sans antialiased text-gray-900 p-2 sm:p-6 lg:p-8" x-data="{ openUsageModal: false, selectedMaterialId: null, selectedUnit: '', selectedPhaseId: null, previewUrl: '', previewImage(event) { const file = event.target.files && event.target.files[0]; if (!file) { this.previewUrl = ''; return; } const reader = new FileReader(); reader.onload = (e) => { this.previewUrl = e.target.result; }; reader.readAsDataURL(file); } }">
    

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="bg-white rounded-card p-6 shadow-saas border border-gray-100 flex items-center justify-between transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
            <div class="space-y-1">
                <dt class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Materials</dt>
                <dd class="text-3xl font-extrabold tracking-tight text-gray-900 font-heading">{{ $metrics['total_materials'] ?? '32' }}</dd>
                <span class="text-xs text-gray-400 block">Registered items</span>
            </div>
            <div class="p-4 bg-green-50 rounded-xl text-brand-dark">
                <i class="bi bi-box-seam text-2xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-card p-6 shadow-saas border border-gray-100 flex items-center justify-between transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
            <div class="space-y-1">
                <dt class="text-xs font-bold uppercase tracking-wider text-gray-400">Materials Used</dt>
                <dd class="text-3xl font-extrabold tracking-tight text-gray-900 font-heading">{{ $metrics['materials_used'] ?? '0' }}</dd>
                <span class="text-xs text-gray-400 block">Active project allocation</span>
            </div>
            <div class="p-4 bg-emerald-50 rounded-xl text-brand-accent">
                <i class="bi bi-truck text-2xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-card p-6 shadow-saas border border-gray-100 flex items-center justify-between transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
            <div class="space-y-1">
                <dt class="text-xs font-bold uppercase tracking-wider text-gray-400">Low Stock</dt>
                <dd class="text-3xl font-extrabold tracking-tight text-orange-600 font-heading">{{ $metrics['low_stock_alerts'] ?? '3' }}</dd>
                <span class="text-xs text-gray-400 block">Requires attention</span>
            </div>
            <div class="p-4 bg-orange-50 rounded-xl text-orange-600">
                <i class="bi bi-exclamation-triangle text-2xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-card p-6 shadow-saas border border-gray-100 flex items-center justify-between transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
            <div class="space-y-1">
                <dt class="text-xs font-bold uppercase tracking-wider text-gray-400">Critical Materials</dt>
                <dd class="text-3xl font-extrabold tracking-tight text-red-600 font-heading">{{ $metrics['critical_materials'] ?? '0' }}</dd>
                <span class="text-xs text-gray-400 block">Urgent reorder required</span>
            </div>
            <div class="p-4 bg-red-50 rounded-xl text-red-600">
                <i class="bi bi-patch-exclamation text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-1 gap-8 items-start">
        
        <div class="space-y-8">
            
            <div class="bg-white rounded-card border border-gray-200 shadow-saas overflow-hidden">
                
                <div class="p-6 border-b border-gray-200 bg-white">
                    <form id="material-filters-form" method="GET" action="{{ route('supervisor.materials') }}" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between w-full">
                        <input type="hidden" name="project_id" value="{{ optional($selectedProject)->project_id }}">
                        <input type="hidden" name="phase_id" value="{{ optional($selectedPhase)->phase_id }}">

                        <div class="relative flex-1 max-w-md">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search materials..." class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-input bg-gray-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-dark transition">
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
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

                            <button type="button" @click="openUsageModal = true" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-brand-dark rounded-btn shadow-saas hover:bg-green-900 transition hover:scale-[1.01] active:scale-[0.99] whitespace-nowrap">
                                <i class="bi bi-plus-lg"></i> Record Usage
                            </button>
                        </div>
                    </form>
                </div>

                <div class="hidden md:block overflow-hidden">
                    <table class="w-full table-fixed border-collapse text-left text-sm">
                        <colgroup>
                            <col class="w-[24%]">
                            <col class="w-[8%]">
                            <col class="w-[11%]">
                            <col class="w-[11%]">
                            <col class="w-[11%]">
                            <col class="w-[13%]">
                            <col class="w-[22%]">
                        </colgroup>
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Material</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Unit</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Planned</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Used</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Remaining</th>
                                <th class="py-4 px-5 bg-gray-50 text-left align-middle">Status</th>
                                <th class="py-4 px-5 bg-gray-50 text-center align-middle">Action</th>
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
                                        <td class="py-4 px-5 align-middle text-sm text-gray-900 font-bold">{{ number_format($item->planned, 2) }}</td>
                                        <td class="py-4 px-5 align-middle text-sm text-gray-600 font-medium">
                                            {{ number_format($item->used, 2) }}
                                        </td>
                                        <td class="py-4 px-5 align-middle text-sm font-bold text-brand-dark font-mono">
                                            {{ number_format($item->remaining, 2) }}</td>
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
                                        <td class="py-4 px-4 align-middle text-center">
                                            <div class="flex justify-center">
                                                <button @click="selectedMaterialId='{{ $item->id }}'; selectedUnit='{{ $item->unit }}'; openUsageModal = true" class="w-full max-w-[150px] inline-flex items-center justify-center gap-1.5 px-3 py-2.5 text-xs font-bold text-white bg-[#166534] border border-[#166534] rounded-btn hover:bg-[#14532d] transition shadow-sm whitespace-nowrap">
                                                    <i class="bi bi-plus-circle"></i> Record Usage
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="py-12 text-center">
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

                <div class="block md:hidden divide-y divide-gray-100">
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
                                        <span class="text-xs font-bold text-brand-dark font-mono">{{ number_format($item->remaining, 2) }}</span>
                                    </div>
                                </div>

                                <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                                    <div class="bg-brand-green h-full transition-all duration-500" style="width: {{ ($item->planned > 0) ? min(100, round(($item->used / $item->planned) * 100)) : 0 }}%"></div>
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <button @click="selectedMaterialId='{{ $item->id }}'; selectedUnit='{{ $item->unit }}'; openUsageModal = true" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-[#166534] rounded-btn shadow-saas hover:bg-[#14532d] transition">
                                        <i class="bi bi-plus-circle"></i> Record Usage
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="p-4 border-t border-gray-100 bg-gray-50/50 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-xs font-medium text-gray-500">
                        @if($inventory->total() > 0)
                            Showing {{ $inventory->firstItem() }} to {{ $inventory->lastItem() }} of {{ $inventory->total() }} entries
                        @else
                            Showing 0 entries
                        @endif
                    </span>
                    <div class="flex items-center gap-1">
                        {{ $inventory->appends(request()->query())->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>

        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-card border border-gray-200 p-5 shadow-saas">
                <div class="flex items-center justify-between gap-2 mb-4">
                    <div class="flex items-center gap-2 text-brand-dark">
                        <i class="bi bi-clock-history"></i>
                        <h3 class="text-xs font-bold tracking-wider text-gray-900 uppercase font-heading">Recent Material Usage</h3>
                    </div>
                </div>
                <div class="space-y-4 divide-y divide-gray-100">
                    @forelse($recentUsages as $usage)
                        <div class="text-xs pt-3 first:pt-0 space-y-1.5">
                            <div class="flex justify-between font-bold text-gray-800">
                                <span>{{ $usage->material->name ?? 'Unknown Material' }}</span>
                                <span class="text-brand-dark font-mono font-bold">-{{ number_format($usage->quantity_used, 2) }} {{ $usage->unit }}</span>
                            </div>
                            <div class="flex justify-between text-[11px] text-gray-400 font-medium">
                                <span>{{ $usage->phase->phase_name ?? 'Unknown Phase' }}</span>
                                <span title="{{ optional($usage->created_at)->format('M d, Y h:i A') }}">{{ optional($usage->created_at)->diffForHumans() }}</span>
                            </div>
                            <p class="text-gray-500 text-[11px]">Recorded by: <span class="font-semibold text-gray-700">{{ $usage->recorder->name ?? 'Supervisor' }}</span></p>
                        </div>
                    @empty
                        <div class="text-xs pt-3 text-gray-500">No recent material usage records available.</div>
                    @endforelse
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
                 class="relative transform overflow-hidden rounded-card bg-white text-left shadow-xl transition-all w-full max-w-4xl border border-gray-100">
                 
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
                                    @foreach($projectPhases as $phase)
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

                        <div class="grid grid-cols-3 gap-3">
                            <div class="col-span-2">
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

</div>

<script>
    function showMaterialFeedback(type, message) {
        Swal.fire({
            icon: type === 'success' ? 'success' : 'error',
            title: type === 'success' ? 'Success' : 'Please check',
            text: message,
            showConfirmButton: true,
            confirmButtonColor: '#0b6054',
            timer: type === 'success' ? 3000 : 4000,
            timerProgressBar: true
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const usageForm = document.getElementById('material_usage_form');

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