<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Daily Attendance Report</title></head>
<body style="font-family: Arial, sans-serif; color: #1f2937;">
    <h2>Daily Attendance Report</h2>
    <p>Date: {{ $date->format('F d, Y') }}</p>
    <table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th align="left">Worker</th>
                <th align="left">Project / Deployment</th>
                <th align="left">Schedule</th>
                <th align="left">Time In</th>
                <th align="left">Time Out</th>
                <th align="left">Status</th>
                <th align="left">OT</th>
            </tr>
        </thead>
        <tbody>
        @forelse($records as $record)
            @php
                $worker = $record->display_worker ?? $record->worker ?? $record->deployment?->worker;
                $project = $record->display_project ?? $record->deployment?->project;

                $workerName = trim(($worker?->first_name ?? '') . ' ' . ($worker?->last_name ?? ''));

                if ($workerName === '') {
                    $workerName = $worker?->full_name ?? $worker?->name ?? 'Unknown Worker';
                }

                $projectName = $project?->project_name ?? $project?->name ?? 'No Project';
                $scheduleStart = $worker?->schedule_start ?? '07:00';
                $scheduleEnd = $worker?->schedule_end ?? '17:00';
                $overtimeMinutes = (int) ($record->overtime_minutes ?? 0);
                $overtimeLabel = $overtimeMinutes > 0 ? ($overtimeMinutes . ' min') : '—';
            @endphp
            <tr>
                <td>{{ $workerName }}</td>
                <td>{{ $projectName }}</td>
                <td>{{ \Carbon\Carbon::parse($scheduleStart)->format('g:i A') }} - {{ \Carbon\Carbon::parse($scheduleEnd)->format('g:i A') }}</td>
                <td>{{ $record->time_in ?: '-' }}</td>
                <td>{{ $record->time_out ?: '-' }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $record->status)) }}</td>
                <td>{{ $overtimeLabel }}</td>
            </tr>
        @empty
            <tr><td colspan="7">No attendance records were recorded.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
