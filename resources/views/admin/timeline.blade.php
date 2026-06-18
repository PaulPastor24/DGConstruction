@extends('layouts.admin')

@section('title', 'Project Timeline - D&G Construction Monitor')
@section('page_title', 'Project Timeline')

@section('content')
<div class="page active" id="pg-timeline">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Gantt Schedule & Milestones</div>
            <select class="form-select" id="timeline-project-select" style="width:auto; max-width:250px;">
                <option value="Rizal Residential Complex">Rizal Residential Complex</option>
                <option value="CoreConstruct Warehouse Hub">CoreConstruct Warehouse Hub</option>
            </select>
        </div>
        <p class="text-muted p-3">Timeline monitoring workspace engine. Gantt visualizations and schedule trackers map here.</p>
    </div>
</div>
@endsection