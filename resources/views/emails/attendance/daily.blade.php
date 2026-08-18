@component('mail::message')
# Daily Attendance Report

Attendance summary for **{{ $reportDate }}**.

| Metric | Count |
|:--|--:|
| Total records | {{ $summary['total'] }} |
| Present | {{ $summary['present'] }} |
| Late / half day | {{ $summary['late'] }} |
| Absent | {{ $summary['absent'] }} |
| Overtime records | {{ $summary['overtime_records'] }} |
| Overtime hours | {{ number_format($summary['overtime_hours'], 2) }} |

@if($records->isNotEmpty())
## Attendance details

| Worker | Time in | Time out | OT | Status |
|:--|:--|:--|--:|:--|
@foreach($records as $record)
| {{ $record['worker'] }} | {{ $record['time_in'] ?? '—' }} | {{ $record['time_out'] ?? '—' }} | {{ $record['overtime_label'] }} | {{ $record['status'] }} |
@endforeach
@endif

This report was generated automatically by the D&G Construction Management System.
@endcomponent
