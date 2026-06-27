@extends('layouts.supervisor')

@section('title', 'Group Attendance - D&G Construction Monitor')
@section('page_title', 'Group Attendance')

@section('content')
<div class="container-fluid p-0">
    <form action="{{ route('supervisor.attendance.save') }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h6 class="heading-syne fw-bold m-0 text-dark text-uppercase tracking-wider" style="font-size: 14px;">
                        Daily Workforce Attendance
                    </h6>
                    <p class="text-muted mb-0 mt-1" style="font-size: 11px;">Log and update field personnel logs for today's shifts.</p>
                </div>
                <div style="min-width: 200px;">
                    <input type="date" name="attendance_date" class="form-control border shadow-sm px-3 text-muted" value="{{ date('Y-m-d') }}" style="font-size: 13px; border-radius: 6px;">
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                <div class="table-responsive">
                    <table class="table align-middle border-0 mt-2">
                        <thead>
                            <tr class="text-muted border-bottom" style="font-size: 11px; font-weight: bold; text-uppercase: uppercase; letter-spacing: 0.5px;">
                                <th class="pb-3 border-0" style="width: 40%;">Personnel Name</th>
                                <th class="pb-3 border-0">Trade / Designation</th>
                                <th class="pb-3 border-0 text-center" style="width: 35%;">Status Log</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($workers) && $workers->count() > 0)
                                @foreach($workers as $worker)
                                    <tr class="border-bottom" style="border-color: #f1f3f5 !important; font-size: 13px;">
                                        <td class="py-3 text-dark fw-bold">{{ $worker->name }}</td>
                                        <td class="py-3 text-muted">{{ $worker->role }}</td>
                                        <td class="py-3">
                                            <div class="d-flex justify-content-center gap-2">
                                                <input type="radio" class="btn-check" name="attendance[{{ $worker->id }}]" id="present-{{ $worker->id }}" value="present" checked>
                                                <label class="btn btn-outline-success btn-sm px-3 rounded-pill fw-medium" for="present-{{ $worker->id }}" style="font-size: 11px;">Present</label>

                                                <input type="radio" class="btn-check" name="attendance[{{ $worker->id }}]" id="late-{{ $worker->id }}" value="late">
                                                <label class="btn btn-outline-warning btn-sm px-3 rounded-pill fw-medium" for="late-{{ $worker->id }}" style="font-size: 11px;">Late</label>

                                                <input type="radio" class="btn-check" name="attendance[{{ $worker->id }}]" id="absent-{{ $worker->id }}" value="absent">
                                                <label class="btn btn-outline-danger btn-sm px-3 rounded-pill fw-medium" for="absent-{{ $worker->id }}" style="font-size: 11px;">Absent</label>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted fst-italic" style="font-size: 13px;">
                                        No personnel assigned to this active work site.
                                    </td>
                                endtr
                            @endif
                        </tbody>
                    </table>
                </div>

                @if(isset($workers) && $workers->count() > 0)
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-success heading-syne fw-bold px-4 py-2" style="border-radius: 8px; font-size: 13px; background-color: #198754; border: none;">
                            Save Attendance Logs
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>
@endsection