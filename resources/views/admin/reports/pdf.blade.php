<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Report - {{ $report->project->project_name }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { margin: 14mm 15mm 16mm; }
        body { margin: 0; color: #263238; font-family: DejaVu Sans, Arial, sans-serif; font-size: 9.5px; line-height: 1.35; }
        .header { border-bottom: 3px solid #176b55; padding: 0 0 10px; margin-bottom: 13px; }
        .brand { color: #176b55; font-size: 10px; font-weight: bold; letter-spacing: 1.4px; text-transform: uppercase; }
        h1 { color: #123f35; font-size: 22px; letter-spacing: .4px; margin: 4px 0 2px; text-transform: uppercase; }
        .generated { color: #718096; font-size: 8.5px; }
        .section { margin: 0 0 13px; page-break-inside: avoid; }
        .section-title { background: #176b55; color: #fff; font-size: 10px; font-weight: bold; letter-spacing: .8px; padding: 5px 8px; text-transform: uppercase; }
        .meta-grid, .data-grid, .approval-grid, .milestones, .images-grid { border-collapse: collapse; width: 100%; }
        .data-cell, .approval-cell { border: 1px solid #dbe5e1; padding: 6px 8px; vertical-align: top; width: 25%; }
        .data-cell.wide { width: 50%; }
        .label { color: #718096; display: block; font-size: 7.5px; font-weight: bold; letter-spacing: .65px; margin-bottom: 2px; text-transform: uppercase; }
        .value { color: #172a24; font-size: 10px; font-weight: bold; }
        .progress-box { border: 1px solid #dbe5e1; padding: 8px; }
        .progress-number { color: #176b55; font-size: 20px; font-weight: bold; }
        .progress-track { background: #e7efeb; height: 8px; margin: 5px 0 3px; width: 100%; }
        .progress-fill { background: #176b55; height: 8px; }
        .milestones th { background: #edf5f1; color: #45635a; font-size: 7.5px; padding: 5px; text-align: left; text-transform: uppercase; }
        .milestones td { border-bottom: 1px solid #e5ece8; padding: 5px; vertical-align: top; }
        .status { color: #176b55; font-weight: bold; text-transform: uppercase; }
        .text-block { background: #f7faf8; border-left: 3px solid #a9cbbb; padding: 8px 10px; white-space: pre-wrap; }
        .images-grid { margin-top: 6px; table-layout: fixed; }
        .image-cell { background: #f3f7f5; border: 1px solid #dbe5e1; padding: 4px; text-align: center; vertical-align: middle; width: 33.33%; }
        .image-cell img { height: 125px; max-width: 100%; object-fit: contain; }
        .approval-cell { width: 25%; }
        .footer { border-top: 1px solid #dbe5e1; color: #718096; font-size: 8px; margin-top: 14px; padding-top: 6px; text-align: center; }
    </style>
</head>
<body>
    <div>
        <div class="header">
            <div class="brand">D&amp;G Construction Management System</div>
            <h1>Project Progress Report</h1>
            <div class="generated">Generated {{ now()->format('M d, Y h:i A') }}</div>
        </div>

        <table class="meta-grid">
            <tr class="meta-row">
                <td class="data-cell wide">
                    <span class="label">Project Name</span><span class="value">{{ optional($report->project)->project_name ?? 'N/A' }}</span>
                </td>
                <td class="data-cell wide">
                    <span class="label">Client</span><span class="value">{{ optional($report->project->client)->name ?? optional($report->project->client)->company_name ?? 'N/A' }}</span>
                </td>
            </tr>
            <tr class="meta-row">
                <td class="data-cell wide">
                    <span class="label">Location</span><span class="value">{{ optional($report->project)->project_location ?? optional($report->project)->location ?? 'N/A' }}</span>
                </td>
                <td class="data-cell wide">
                    <span class="label">Report Date</span><span class="value">{{ $report->report_date?->format('M d, Y') ?? $report->created_at->format('M d, Y') }}</span>
                </td>
            </tr>
            <tr class="meta-row">
                <td class="data-cell wide">
                    <span class="label">Construction Phase</span><span class="value">{{ optional($report->phase)->phase_name ?? 'N/A' }}</span>
                </td>
                <td class="data-cell wide">
                    <span class="label">Report ID</span><span class="value">RPT-{{ optional($report->created_at)->format('Y') ?? now()->format('Y') }}-{{ str_pad($report->report_id, 4, '0', STR_PAD_LEFT) }}</span>
                </td>
            </tr>
        </table>

        <div class="section">
            <div class="section-title">Progress Summary</div>
            <div class="progress-box">
                <span class="label">Phase Progress</span>
                <span class="progress-number">{{ optional($report->phase)->completion_percentage ?? 0 }}%</span>
                <div class="progress-track">
                    <div class="progress-fill" style="width: {{ min(100, (int)(optional($report->phase)->completion_percentage ?? 0)) }}%;"></div>
                </div>
                <span class="label">Current Status</span><span class="status">{{ str_replace('_', ' ', optional($report->phase)->status ?? 'N/A') }}</span>
                <span class="label" style="margin-top:6px;">Overall Project Progress</span>
                <span class="value">{{ round((float) ($report->project?->phases?->avg('completion_percentage') ?? 0), 2) }}%</span>
            </div>
        </div>

        @if($report->phase && $report->phase->milestones && $report->phase->milestones->count() > 0)
            <div class="section">
                <div class="section-title">Milestones / Timeline</div>
                <table class="milestones">
                    <tr><th>Milestone</th><th>Date Range</th><th>Status</th></tr>
                    @foreach($report->phase->milestones as $milestone)
                        <tr><td><strong>{{ $milestone->milestone_name }}</strong></td><td>{{ $milestone->start_date?->format('M d, Y') }} &rarr; {{ $milestone->end_date?->format('M d, Y') }}</td><td class="status">{{ $milestone->is_completed ? 'Completed' : ($milestone->is_delayed ? 'Delayed' : 'Upcoming') }}</td></tr>
                    @endforeach
                </table>
            </div>
        @endif

        <div class="section">
            <div class="section-title">Accomplishments</div>
            <div class="text-block">{{ $report->report_text ?? 'No description provided.' }}</div>
        </div>

        @if($report->admin_report_text)
            <div class="section">
                <div class="section-title">Admin Notes</div>
                <div class="text-block">{{ $report->admin_report_text }}</div>
            </div>
        @endif

        @if($report->admin_explanation && trim($report->admin_explanation) !== trim((string) $report->admin_report_text))
            <div class="section">
                <div class="section-title">Explanation / Remarks</div>
                <div class="text-block">{{ $report->admin_explanation }}</div>
            </div>
        @endif

        @if($reportPdfImages->isNotEmpty())
            <div class="section images-section">
                <div class="section-title">Site Images</div>
                <table class="images-grid">
                    @foreach($reportPdfImages->chunk(3) as $imageRow)
                        <tr>
                            @foreach($imageRow as $image)
                                <td class="image-cell"><img src="{{ $image }}" alt="Site Image"></td>
                            @endforeach
                            @for($empty = $imageRow->count(); $empty < 3; $empty++)<td class="image-cell"></td>@endfor
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        <div class="section-title">Approval / Report Information</div>
        <table class="approval-grid">
            <tr>
                <td class="approval-cell">
                    <span class="label">Submitted By</span><span class="value">{{ optional($report->submittedBy)->name ?? 'N/A' }}</span>
                </td>
                <td class="approval-cell">
                    <span class="label">Report Period</span><span class="value">{{ $report->report_date?->format('M d, Y') ?? 'N/A' }}</span>
                </td>
            </tr>
            <tr>
                <td class="approval-cell">
                    <span class="label">Approved By</span><span class="value">{{ optional($report->approvedBy)->name ?? 'N/A' }}</span>
                </td>
                <td class="approval-cell">
                    <span class="label">Status</span><span class="value">{{ ucfirst($report->approval_status) }}</span>
                </td>
            </tr>
            <tr>
                <td class="approval-cell">
                    <span class="label">Reviewed At</span><span class="value">{{ $report->reviewed_at?->format('M d, Y h:i A') ?? 'Pending' }}</span>
                </td>
                <td class="approval-cell">
                    <span class="label">Approved At</span><span class="value">{{ $report->approved_at?->format('M d, Y h:i A') ?? 'Pending' }}</span>
                </td>
            </tr>
        </table>

        <div class="footer">
            <p>Generated by D&G Construction Management System</p>
            <p>Exported on {{ now()->format('M d, Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
