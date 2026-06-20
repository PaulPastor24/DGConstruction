@extends('layouts.supervisor')

@section('title', 'Submit Progress Report')
@section('page_title', 'Submit Progress Report')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/supervisor.css') }}">
@endpush

@section('content')
<div class="mb-4">
    <h2 class="heading-syne fs-1 mb-1 fw-extrabold">Submit Progress Report</h2>
    <p class="text-muted">Report daily accomplishments and project status for admin review.</p>
</div>

<div class="row mb-4 g-3">
    <div class="col-md-4">
        <div class="metric-card">
            <span class="text-muted small text-uppercase fw-bold">Reports This Month</span>
            <div class="d-flex align-items-baseline gap-2 mt-1">
                <h1 class="heading-syne m-0 display-5 fw-bold text-dark">{{ $reportsCount ?? 0 }}</h1>
                @if(($pendingCount ?? 0) > 0)
                    <span class="text-success small fw-bold">{{ $pendingCount }} pending review</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-card approved">
            <span class="text-muted small text-uppercase fw-bold">Approved</span>
            <div class="d-flex align-items-baseline gap-2 mt-1">
                <h1 class="heading-syne m-0 display-5 fw-bold text-dark">{{ $approvedCount ?? 0 }}</h1>
                <span class="text-muted small">Last 30 days</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-card rate">
            <span class="text-muted small text-uppercase fw-bold">On-Time Submission Rate</span>
            <div class="d-flex align-items-baseline gap-2 mt-1">
                <h1 class="heading-syne m-0 display-5 fw-bold text-dark">{{ $submissionRate ?? '0%' }}</h1>
                <span class="text-primary small fw-bold">Excellent</span>
            </div>
        </div>
    </div>
</div>

<div class="d-flex align-items-center justify-content-between mb-4 bg-white p-3 border rounded-4 shadow-sm" style="font-size: 13px;">
    <div class="d-flex align-items-center gap-2 fw-bold text-success">
        <span class="badge bg-success rounded-circle d-flex align-items-center justify-content-center" style="width:20px; height:20px;">1</span> Project Info
    </div>
    <div style="flex: 1; height: 1px; background: #e2e8f0; margin: 0 15px;"></div>
    <div class="d-flex align-items-center gap-2 text-muted">
        <span class="badge bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:20px; height:20px;">2</span> Accomplishments
    </div>
    <div style="flex: 1; height: 1px; background: #e2e8f0; margin: 0 15px;"></div>
    <div class="d-flex align-items-center gap-2 text-muted">
        <span class="badge bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:20px; height:20px;">3</span> Documentation
    </div>
    <div style="flex: 1; height: 1px; background: #e2e8f0; margin: 0 15px;"></div>
    <div class="d-flex align-items-center gap-2 text-muted">
        <span class="badge bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:20px; height:20px;">4</span> Submit
    </div>
</div>

<div class="card-custom">
    <h4 class="heading-syne fs-5 mb-4 border-bottom pb-2">Report Details</h4>
    
    <form action="#" method="POST">
        @csrf
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label text-muted small fw-bold">Project</label>
                <select name="project_id" class="form-select form-input-custom @error('project_id') is-invalid @enderror">
                    <option value="" disabled {{ old('project_id') ? '' : 'selected' }}>Select assigned operational project...</option>
                    @foreach($projects ?? [] as $project)
                        <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
                @error('project_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-6">
                <label class="form-label text-muted small fw-bold">Report Period</label>
                <input type="date" name="report_date" class="form-control form-input-custom @error('report_date') is-invalid @enderror" value="{{ old('report_date', now()->format('Y-m-d')) }}">
                @error('report_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label text-muted small fw-bold">Current Phase</label>
                <select name="project_phase_id" class="form-select form-input-custom @error('project_phase_id') is-invalid @enderror">
                    <option value="" disabled {{ old('project_phase_id') ? '' : 'selected' }}>Select current operational phase...</option>
                    @foreach($phases ?? [] as $phase)
                        <option value="{{ $phase->id }}" {{ old('project_phase_id') == $phase->id ? 'selected' : '' }}>
                            {{ $phase->name }}
                        </option>
                    @endforeach
                </select>
                @error('project_phase_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label text-muted small fw-bold">Completion Description</label>
                <textarea name="description" rows="5" class="form-control form-input-custom @error('description') is-invalid @enderror" placeholder="Document structure pouring progression, rebar structural bindings, or logistics bottlenecks...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label text-muted small fw-bold">Completion % (Est.)</label>
                <input type="number" name="completion_percentage" class="form-control form-input-custom @error('completion_percentage') is-invalid @enderror" min="0" max="100" value="{{ old('completion_percentage') }}" placeholder="e.g. 0">
                @error('completion_percentage')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label text-muted small fw-bold">Workers On-Site</label>
                <input type="number" name="workers_count" class="form-control form-input-custom @error('workers_count') is-invalid @enderror" min="0" value="{{ old('workers_count') }}" placeholder="e.g. 0">
                @error('workers_count')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4 pt-3 border-top d-flex justify-content-end">
            <button type="submit" class="btn btn-success px-4 py-2 fw-bold" style="background-color: var(--accent-color); border: none; border-radius: 8px;">
                Publish Field Logs
            </button>
        </div>
    </form>
</div>
@endsection