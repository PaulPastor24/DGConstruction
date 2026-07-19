<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Construction Phases Report - {{ $project->project_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            line-height: 1.6;
            padding: 24px;
        }
        .page {
            max-width: 980px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 26px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 22px 60px rgba(15, 23, 42, 0.08);
        }
        .header {
            padding: 36px 44px;
            background: linear-gradient(135deg, #0b6054 0%, #0f766e 100%);
            color: #ffffff;
        }
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            letter-spacing: 0.02em;
        }
        .header p {
            font-size: 14px;
            opacity: 0.92;
        }
        .content {
            padding: 36px 44px 40px;
        }
        .project-info {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }
        .project-card,
        .stats-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 22px;
        }
        .project-label,
        .stats-label {
            display: block;
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 12px;
        }
        .project-value,
        .stats-value {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.5;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }
        .stat-card {
            text-align: center;
            min-height: 108px;
        }
        .stat-card p {
            font-size: 11px;
            color: #64748b;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        .stat-card h3 {
            font-size: 26px;
            color: #0f172a;
            margin: 0;
        }
        .progress-card {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .progress-bar {
            width: 100%;
            height: 18px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            margin-top: 12px;
        }
        .progress-fill {
            height: 100%;
            background: #0b6054;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .phases-table-wrapper {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            padding: 28px;
        }
        .table-header {
            margin-bottom: 20px;
        }
        .table-header h2 {
            font-size: 18px;
            color: #0f172a;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            color: #334155;
        }
        thead tr {
            background: #f8fafc;
        }
        th,
        td {
            padding: 14px 16px;
            border: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: middle;
        }
        th {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .status-completed { background: #dcfce7; color: #166534; }
        .status-in_progress { background: #dbeafe; color: #1d4ed8; }
        .status-delayed { background: #fee2e2; color: #991b1b; }
        .status-pending { background: #f1f5f9; color: #475569; }
        .mini-progress {
            width: 60px;
            height: 6px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
            margin-right: 8px;
        }
        .mini-progress-fill {
            height: 100%;
            background: #0b6054;
        }
        .footer {
            padding: 28px 44px 32px;
            text-align: center;
            color: #64748b;
            font-size: 12px;
            border-top: 1px solid #e2e8f0;
        }
        @media print {
            body { background: white; padding: 0; }
            .page { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <h1>Construction Phases Report</h1>
            <p>Phase progress summary for <strong>{{ $project->project_name }}</strong> • generated {{ now()->format('M d, Y h:i A') }}</p>
        </div>

        <div class="content">
            <div class="project-info">
                <div class="project-card">
                    <span class="project-label">Project Name</span>
                    <span class="project-value">{{ $project->project_name }}</span>
                </div>
                <div class="project-card">
                    <span class="project-label">Status</span>
                    <span class="project-value">{{ ucfirst($project->status ?? 'Planning') }}</span>
                </div>
                <div class="project-card">
                    <span class="project-label">Location</span>
                    <span class="project-value">{{ $project->location ?? 'N/A' }}</span>
                </div>
                <div class="project-card">
                    <span class="project-label">Start Date</span>
                    <span class="project-value">{{ $project->start_date ? $project->start_date->format('M d, Y') : 'N/A' }}</span>
                </div>
                <div class="project-card">
                    <span class="project-label">Target End Date</span>
                    <span class="project-value">{{ $project->target_end_date ? $project->target_end_date->format('M d, Y') : 'N/A' }}</span>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <p>Total Phases</p>
                    <h3>{{ $phases->count() }}</h3>
                </div>
                <div class="stat-card progress-card">
                    <p>Overall Progress</p>
                    <h3>{{ round($overallProgress, 0) }}%</h3>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ min(100, round($overallProgress, 0)) }}%;">{{ round($overallProgress, 0) }}%</div>
                    </div>
                </div>
                <div class="stat-card">
                    <p>In Progress</p>
                    <h3>{{ $inProgressCount }}</h3>
                </div>
                <div class="stat-card">
                    <p>Completed</p>
                    <h3>{{ $completedCount }}</h3>
                </div>
                <div class="stat-card">
                    <p>Delayed</p>
                    <h3>{{ $delayedCount }}</h3>
                </div>
            </div>

            <div class="phases-table-wrapper">
                <div class="table-header">
                    <h2>Phase Breakdown</h2>
                </div>
                @if($phases->count() > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>Phase Order</th>
                                <th>Phase Name</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($phases as $phase)
                                <tr>
                                    <td>#{{ $phase->phase_order }}</td>
                                    <td>{{ $phase->phase_name }}</td>
                                    <td><span class="status-badge {{ $phase->status }}">{{ match($phase->status) {
                                        'completed' => 'Completed',
                                        'in_progress' => 'In Progress',
                                        'delayed' => 'Delayed',
                                        default => 'Pending'
                                    } }}</span></td>
                                    <td>
                                        <div class="mini-progress">
                                            <div class="mini-progress-fill" style="width: {{ (float)($phase->completion_percentage ?? 0) }}%;"></div>
                                        </div>
                                        {{ round((float)($phase->completion_percentage ?? 0), 0) }}%
                                    </td>
                                    <td>{{ $phase->planned_start_date ? $phase->planned_start_date->format('M d, Y') : 'Pending' }}</td>
                                    <td>{{ $phase->planned_end_date ? $phase->planned_end_date->format('M d, Y') : 'Pending' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>No phases available for this project.</p>
                @endif
            </div>
        </div>

        <div class="footer">
            <p>Automatically generated by the D&amp;G Construction Management System</p>
            <p>Report generated on {{ now()->format('M d, Y H:i A') }}</p>
        </div>
    </div>
</body>
</html>
