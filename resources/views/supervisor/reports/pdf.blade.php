<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accomplishment Report - {{ $report->report_id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif;
            background: #f1f5f9;
            color: #1f2937;
            line-height: 1.6;
            padding: 20px;
        }
        .page {
            max-width: 940px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
        }
        .report-header {
            padding: 36px 44px;
            background: linear-gradient(135deg, #0b6054 0%, #16a085 100%);
            color: #ffffff;
        }
        .report-header h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .report-header p {
            font-size: 14px;
            opacity: 0.92;
        }
        .content {
            padding: 36px 44px 42px;
        }
        .overview-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .overview-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 22px 24px;
        }
        .overview-title {
            display: block;
            font-size: 11px;
            letter-spacing: 0.13em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 10px;
        }
        .overview-value {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.4;
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-approved { background: #dcfce7; color: #166534; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .section-heading h2 {
            font-size: 18px;
            color: #0f172a;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .text-block {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 22px;
            font-size: 14px;
            color: #334155;
            white-space: pre-wrap;
            line-height: 1.75;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }
        .info-block {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
        }
        .info-label {
            display: block;
            font-size: 11px;
            letter-spacing: 0.13em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 8px;
        }
        .info-text {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }
        .images-section {
            margin-top: 20px;
        }
        .images-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-top: 16px;
        }
        .image-item {
            min-height: 140px;
            border-radius: 16px;
            overflow: hidden;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .image-item img {
            object-fit: cover;
            width: 100%;
            height: 100%;
        }
        .footer {
            padding: 24px 44px 30px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 12px;
        }
        @media print {
            body { background: white; padding: 0; }
            .page { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="report-header">
            <h1>Accomplishment Report</h1>
            <p>Generated for <strong>{{ $report->project->project_name }}</strong> on {{ now()->format('M d, Y h:i A') }}.</p>
        </div>

        <div class="content">
            <div class="overview-grid">
                <div class="overview-card">
                    <span class="overview-title">Report ID</span>
                    <span class="overview-value">RPT-2026-{{ str_pad($report->report_id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="overview-card">
                    <span class="overview-title">Status</span>
                    <span class="overview-value"><span class="status-pill status-{{ $report->approval_status }}">{{ ucfirst($report->approval_status) }}</span></span>
                </div>
                <div class="overview-card">
                    <span class="overview-title">Report Date</span>
                    <span class="overview-value">{{ $report->report_date->format('M d, Y') }}</span>
                </div>
                <div class="overview-card">
                    <span class="overview-title">Submitted By</span>
                    <span class="overview-value">{{ $report->submittedBy->name }}</span>
                </div>
            </div>

            <div class="section-heading">
                <h2>Project & Phase</h2>
            </div>
            <div class="info-grid">
                <div class="info-block">
                    <span class="info-label">Project</span>
                    <span class="info-text">{{ $report->project->project_name }}</span>
                </div>
                <div class="info-block">
                    <span class="info-label">Phase</span>
                    <span class="info-text">{{ $report->phase->phase_name }}</span>
                </div>
                <div class="info-block">
                    <span class="info-label">Created At</span>
                    <span class="info-text">{{ $report->created_at->format('M d, Y h:i A') }}</span>
                </div>
                <div class="info-block">
                    <span class="info-label">Approved By</span>
                    <span class="info-text">{{ $report->approvedBy->name ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="section-heading">
                <h2>Construction Accomplishment</h2>
            </div>
            <div class="text-block">{{ $report->report_text ?? 'No description provided.' }}</div>

            @if($report->site_images && is_array($report->site_images) && count($report->site_images) > 0)
                <div class="images-section">
                    <div class="section-heading">
                        <h2>Site Images</h2>
                    </div>
                    <div class="images-grid">
                        @foreach($report->site_images as $image)
                            <div class="image-item">
                                <img src="{{ asset('storage/' . ltrim($image, '/')) }}" alt="Site Image">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="section-heading" style="margin-top: 32px;">
                <h2>Approval Details</h2>
            </div>
            <div class="info-grid">
                <div class="info-block">
                    <span class="info-label">Reviewed At</span>
                    <span class="info-text">{{ $report->reviewed_at ? $report->reviewed_at->format('M d, Y h:i A') : 'Pending' }}</span>
                </div>
                <div class="info-block">
                    <span class="info-label">Approved At</span>
                    <span class="info-text">{{ $report->approved_at ? $report->approved_at->format('M d, Y h:i A') : 'Pending' }}</span>
                </div>
                <div class="info-block" style="grid-column: span 2;">
                    <span class="info-label">Remarks</span>
                    <span class="info-text">{{ $report->approval_remarks ?: 'No remarks provided.' }}</span>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Generated by D&G Construction Management System</p>
            <p>Exported on {{ now()->format('M d, Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
