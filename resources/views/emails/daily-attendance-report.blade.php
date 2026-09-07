<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Daily Attendance Report</title></head>
<body style="font-family: Arial, sans-serif; color: #1f2937;">
    <h2>Daily Attendance Report</h2>
    <p>Date: {{ $date->format('F d, Y') }}</p>
    <table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%;">
        <thead><tr><th align="left">Worker</th><th align="left">Schedule</th><th align="left">Time In</th><th align="left">Time Out</th><th align="left">Status</th><th align="left">OT</th></tr></thead>
        <tbody>
        @forelse($records as $record)
            <tr>
                <td>{{ $record->worker?->full_name ?: trim(($record->worker?->first_name ?? '').' '.($record->worker?->last_name ?? '')) }}</td>
                <td>{{ $record->worker?->schedule_start }} - {{ $record->worker?->schedule_end }}</td>
                <td>{{ $record->time_in ?: '-' }}</td>
                <td>{{ $record->time_out ?: '-' }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $record->status)) }}</td>
                <td>{{ $record->overtime_minutes }} min</td>
            </tr>
        @empty
            <tr><td colspan="6">No attendance records were recorded.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
