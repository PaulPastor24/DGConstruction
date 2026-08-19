<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Report Images - {{ $project->project_name }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { margin: 14mm 15mm 16mm; }
        body { margin: 0; color: #263238; font-family: DejaVu Sans, Arial, sans-serif; font-size: 9.5px; line-height: 1.35; }
        .header { border-bottom: 3px solid #176b55; padding-bottom: 10px; margin-bottom: 13px; }
        .brand { color: #176b55; font-size: 10px; font-weight: bold; letter-spacing: 1.4px; text-transform: uppercase; }
        h1 { color: #123f35; font-size: 22px; letter-spacing: .4px; margin: 4px 0 2px; text-transform: uppercase; }
        .generated { color: #718096; font-size: 8.5px; }
        .meta-grid, .images-grid { border-collapse: collapse; width: 100%; }
        .meta-cell { border: 1px solid #dbe5e1; padding: 6px 8px; vertical-align: top; width: 50%; }
        .label { color: #718096; display: block; font-size: 7.5px; font-weight: bold; letter-spacing: .65px; margin-bottom: 2px; text-transform: uppercase; }
        .value { color: #172a24; font-size: 10px; font-weight: bold; }
        .report-section { border: 1px solid #dbe5e1; margin: 0 0 11px; page-break-inside: auto; }
        .report-section-header { background: #176b55; color: #fff; font-size: 10px; font-weight: bold; letter-spacing: .7px; padding: 5px 8px; text-transform: uppercase; }
        .report-section-body { padding: 7px 8px 8px; }
        .report-meta-row { color: #526861; margin-bottom: 5px; }
        .report-meta-item { display: inline-block; font-size: 8.5px; margin-right: 14px; }
        .report-meta-item strong { color: #172a24; }
        .report-description { background: #f7faf8; border-left: 2px solid #a9cbbb; color: #334e45; font-size: 9px; margin: 4px 0 7px; padding: 5px 7px; white-space: pre-wrap; }
        .images-grid { table-layout: fixed; margin-top: 5px; }
        .image-item { background: #f3f7f5; border: 1px solid #dbe5e1; padding: 3px; text-align: center; vertical-align: middle; width: 33.33%; }
        .image-item img { height: 112px; max-width: 100%; object-fit: contain; }
        .no-images { color: #899b95; font-size: 8.5px; font-style: italic; padding: 5px 0; }
        .footer { border-top: 1px solid #dbe5e1; color: #718096; font-size: 8px; margin-top: 13px; padding-top: 6px; text-align: center; }
    </style>
</head>
<body>
    <div>
        <div class="header">
            <div class="brand">D&amp;G Construction Management System</div>
            <h1>Project Report Images</h1>
            <div class="generated">Generated {{ now()->format('M d, Y h:i A') }}</div>
        </div>

        <table class="meta-grid">
            <tr class="meta-row">
                <td class="meta-cell">
                    <span class="label">Project Name</span><span class="value">{{ $project->project_name }}</span>
                </td>
                <td class="meta-cell">
                    <span class="label">Client</span><span class="value">{{ optional($project->client)->name ?? optional($project->client)->company_name ?? 'N/A' }}</span>
                </td>
            </tr>
            <tr class="meta-row">
                <td class="meta-cell">
                    <span class="label">Location</span><span class="value">{{ $project->project_location ?? $project->location ?? 'N/A' }}</span>
                </td>
                <td class="meta-cell">
                    <span class="label">Total Reports</span><span class="value">{{ count($reports) }}</span>
                </td>
            </tr>
        </table>

        @forelse($reports as $report)
            <div class="report-section">
                <div class="report-section-header">Report {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} / Accomplishment</div>
                <div class="report-section-body">
                    <div class="report-meta-row">
                        <div class="report-meta-item">
                            <span>Date: </span><strong>{{ $report->report_date?->format('M d, Y') ?? $report->created_at->format('M d, Y') }}</strong>
                        </div>
                        <div class="report-meta-item">
                            <span>Phase: </span><strong>{{ optional($report->phase)->phase_name ?? 'N/A' }}</strong>
                        </div>
                        <div class="report-meta-item">
                            <span>Status: </span><strong>{{ ucfirst($report->approval_status) }}</strong>
                        </div>
                    </div>
                    @if($report->report_text)
                        <div class="report-description"><span class="label">Accomplishments</span>{{ $report->report_text }}</div>
                    @endif
                    @if($report->admin_report_text)
                        <div class="report-description"><span class="label">Admin Notes</span>{{ $report->admin_report_text }}</div>
                    @endif

                    @php
                        $pdfImages = $reportPdfImages[$report->report_id] ?? collect();
                    @endphp

                    @if($pdfImages->isNotEmpty())
                        <table class="images-grid">
                            @foreach($pdfImages->chunk(3) as $imageRow)
                                <tr>
                                    @foreach($imageRow as $image)
                                        <td class="image-item"><img src="{{ $image }}" alt="Report Image"></td>
                                    @endforeach
                                    @for($empty = $imageRow->count(); $empty < 3; $empty++)<td class="image-item"></td>@endfor
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <div class="no-images">No images for this report.</div>
                    @endif
                </div>
            </div>
        @empty
            <div class="no-images" style="padding: 40px;">
                No report images found for this project.
            </div>
        @endforelse

        <div class="footer">
            D&amp;G Construction Management System &middot; Exported {{ now()->format('M d, Y H:i:s') }}
        </div>
    </div>
</body>
</html>
