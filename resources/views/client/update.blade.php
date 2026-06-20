@extends('layouts.client')

@section('title', 'Site Updates - Client Portal')

@section('content')
<div class="container-fluid p-0">
    
    <div class="mb-4">
        <h2 class="heading-syne fw-extrabold text-dark m-0" style="font-size: 28px;">Site Updates</h2>
        <p class="text-muted mb-0 mt-1" style="font-size: 13px;">Latest news and milestones from your construction site.</p>
    </div>

    <div class="d-flex flex-column gap-3">
        @if(isset($updates) && $updates->count() > 0)
            @foreach($updates as $update)
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="heading-syne fw-bold m-0 text-dark" style="font-size: 15px;">
                                {{ $update->title }}
                            </h6>
                            <small class="text-muted fw-medium" style="font-size: 11px;">
                                {{ date('M d, Y', strtotime($update->log_date)) }}
                            </small>
                        </div>
                        <p class="text-muted mb-3 lh-base" style="font-size: 13px;">
                            {{ $update->content }}
                        </p>
                        @if($update->phase_tag_name)
                            <div class="pt-2 border-top text-muted" style="font-size: 11px; border-color: #f1f3f5 !important;">
                                {{ $update->phase_tag_name }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body p-5 text-center text-muted fst-italic">
                    <span style="font-size: 13px;">There are no recorded daily construction log modifications or site updates documented yet.</span>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection