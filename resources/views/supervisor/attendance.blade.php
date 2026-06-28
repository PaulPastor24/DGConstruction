@extends('layouts.supervisor')

@section('title', 'Group Attendance - D&G Construction Monitor')
@section('page_title', 'Group Attendance')

@section('content')
<div class="container-fluid p-0">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h4 class="fw-bold mb-1">Daily Workforce Attendance</h4>
                    <p class="text-muted mb-0 small">Log and update field personnel attendance for today&apos;s shifts.</p>
                </div>
                <div class="w-100" style="max-width: 220px;">
                    <input type="date" name="attendance_date" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('supervisor.attendance.save') }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="text-muted border-bottom">
                                <th class="pb-3 border-0" style="width: 40%;">Personnel Name</th>
                                <th class="pb-3 border-0">Trade / Designation</th>
                                <th class="pb-3 border-0 text-center" style="width: 35%;">Status Log</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($workers) && $workers->count() > 0)
                                @foreach($workers as $worker)
                                    <tr class="border-bottom">
                                        <td class="py-3 fw-semibold">{{ $worker->full_name ?? ($worker->first_name . ' ' . $worker->last_name) ?? 'Worker' }}</td>
                                        <td class="py-3 text-muted">{{ $worker->trade ?? 'General' }}</td>
                                        <td class="py-3">
                                            <div class="d-flex justify-content-center flex-wrap gap-2">
                                                <input type="radio" class="btn-check" name="attendance[{{ $worker->worker_id ?? $worker->id }}]" id="present-{{ $worker->worker_id ?? $worker->id }}" value="present" checked>
                                                <label class="btn btn-outline-success btn-sm px-3 rounded-pill fw-medium" for="present-{{ $worker->worker_id ?? $worker->id }}">Present</label>

                                                <input type="radio" class="btn-check" name="attendance[{{ $worker->worker_id ?? $worker->id }}]" id="late-{{ $worker->worker_id ?? $worker->id }}" value="late">
                                                <label class="btn btn-outline-warning btn-sm px-3 rounded-pill fw-medium" for="late-{{ $worker->worker_id ?? $worker->id }}">Late</label>

                                                <input type="radio" class="btn-check" name="attendance[{{ $worker->worker_id ?? $worker->id }}]" id="absent-{{ $worker->worker_id ?? $worker->id }}" value="absent">
                                                <label class="btn btn-outline-danger btn-sm px-3 rounded-pill fw-medium" for="absent-{{ $worker->worker_id ?? $worker->id }}">Absent</label>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted fst-italic">
                                        No personnel assigned to this active work site.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if(isset($workers) && $workers->count() > 0)
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-success px-4 py-2 fw-semibold">
                            Save Attendance Logs
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>
@endsection