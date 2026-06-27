@extends('layouts.client')

@section('title', 'Timeline & Milestones - Client Portal')

@section('content')
<div class="container-fluid p-0">
    
    <div class="mb-4">
        <h2 class="heading-syne fw-extrabold text-dark m-0" style="font-size: 28px;">Timeline & Milestones</h2>
        <p class="text-muted mb-0 mt-1" style="font-size: 13px;">Construction phases and project milestones.</p>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h6 class="heading-syne fw-bold m-0 text-dark text-uppercase tracking-wider" style="font-size: 12px; letter-spacing: 0.5px;">
                Phase Milestones
            </h6>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="d-flex flex-column gap-3 mt-2">
                @if(isset($phases) && $phases->count() > 0)
                    @foreach($phases as $phase)
                        <div class="p-3 border rounded-3 bg-white d-flex align-items-center justify-content-between flex-wrap gap-3" style="border-color: #f1f3f5 !important;">
                            <div class="d-flex align-items-center gap-3">
                                <span class="rounded-circle d-inline-block" style="width: 12px; height: 12px; background-color: var(--bs-{{ $phase->color ?? 'secondary' }});"></span>
                                <div>
                                    <h6 class="mb-1 text-dark fw-bold" style="font-size: 14px;">{{ $phase->name }}</h6>
                                    <p class="mb-0 text-muted" style="font-size: 11px;">
                                        {{ date('M d', strtotime($phase->start_date)) }} – {{ date('M d, Y', strtotime($phase->end_date)) }} 
                                        @if($phase->status_description)
                                            · <span class="fst-italic">{{ $phase->status_description }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <div class="heading-syne fw-extrabold text-{{ ($phase->color ?? 'secondary') === 'secondary' ? 'muted' : (($phase->color ?? 'secondary') === 'warning' ? 'dark' : $phase->color) }}" style="font-size: 15px;">
                                    {{ $phase->progress_percentage == 100 ? '✓' : $phase->progress_percentage . '%' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-4 text-center text-muted fst-italic border rounded-3 bg-light-subtle" style="font-size: 13px;">
                        No structured phase track plans or milestone records exist for this project model context.
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection