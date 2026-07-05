<?php

namespace App\Http\Controllers;

use App\Models\ConstructionPhase;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PhasesExportController extends Controller
{
    /**
     * Export phases as CSV
     */
    public function exportCsv(Request $request)
    {
        try {
            $projectId = $request->input('project_id');
            $status = $request->input('status');
            $search = $request->input('search');

            $query = ConstructionPhase::query();

            if ($projectId) {
                $query->where('project_id', $projectId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            if ($search) {
                $query->where('phase_name', 'like', '%' . $search . '%');
            }

            $phases = $query->orderBy('phase_order')->get();

            if ($phases->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No phases found to export'
                ], 404);
            }

            // Create CSV content
            $csvContent = "Phase Name,Status,Progress,Start Date,End Date,Project\n";

            foreach ($phases as $phase) {
                $csvContent .= sprintf(
                    '"%s","%s","%s%%","%s","%s","%s"' . "\n",
                    $phase->phase_name,
                    ucfirst(str_replace('_', ' ', $phase->status)),
                    (float)($phase->completion_percentage ?? 0),
                    $phase->planned_start_date ? $phase->planned_start_date->format('M d, Y') : 'Pending',
                    $phase->planned_end_date ? $phase->planned_end_date->format('M d, Y') : 'Pending',
                    $phase->project?->project_name ?? 'N/A'
                );
            }

            $fileName = 'phases_export_' . date('Y-m-d_H-i-s') . '.csv';

            return Response::make($csvContent, 200, [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting phases: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export phases as PDF (HTML-based)
     */
    public function exportPdf(Request $request)
    {
        try {
            $projectId = $request->input('project_id');
            $status = $request->input('status');
            $search = $request->input('search');

            $query = ConstructionPhase::query();

            if ($projectId) {
                $query->where('project_id', $projectId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            if ($search) {
                $query->where('phase_name', 'like', '%' . $search . '%');
            }

            $phases = $query->orderBy('phase_order')->get();

            if ($phases->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No phases found to export'
                ], 404);
            }

            // Calculate statistics
            $totalPhases = $phases->count();
            $completedCount = $phases->where('status', 'completed')->count();
            $inProgressCount = $phases->where('status', 'in_progress')->count();
            $delayedCount = $phases->where('status', 'delayed')->count();
            $overallProgress = $totalPhases > 0
                ? round((float)$phases->avg('completion_percentage'), 0)
                : 0;

            // HTML content for PDF
            $html = $this->generatePdfHtml($phases, [
                'total' => $totalPhases,
                'completed' => $completedCount,
                'inProgress' => $inProgressCount,
                'delayed' => $delayedCount,
                'overall' => $overallProgress,
            ]);

            $fileName = 'phases_export_' . date('Y-m-d_H-i-s') . '.html';

            return Response::make($html, 200, [
                'Content-Type' => 'text/html; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting phases: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF HTML content
     */
    private function generatePdfHtml($phases, $stats)
    {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Construction Phases Export</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .stat-card h3 {
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .stat-card .value {
            font-size: 28px;
            font-weight: 800;
            color: #10b981;
        }
        .table-section {
            margin-top: 30px;
        }
        .table-section h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #1f2937;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        thead {
            background: #f3f4f6;
            border-bottom: 2px solid #d1d5db;
        }
        th {
            padding: 12px;
            text-align: left;
            font-weight: 700;
            font-size: 13px;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }
        tbody tr:nth-child(odd) {
            background: #f9fafb;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
        }
        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        .status-in-progress {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-delayed {
            background: #fee2e2;
            color: #991b1b;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .progress-bar {
            width: 100px;
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            display: inline-block;
            margin-right: 5px;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #34d399);
            border-radius: 3px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
        }
        @media print {
            body { background: white; }
            .container { max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Construction Phases Report</h1>
            <p>Generated on {$this->formatDate(now())}</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Phases</h3>
                <div class="value">{$stats['total']}</div>
            </div>
            <div class="stat-card">
                <h3>In Progress</h3>
                <div class="value">{$stats['inProgress']}</div>
            </div>
            <div class="stat-card">
                <h3>Completed</h3>
                <div class="value">{$stats['completed']}</div>
            </div>
            <div class="stat-card">
                <h3>Delayed</h3>
                <div class="value">{$stats['delayed']}</div>
            </div>
        </div>

        <div class="table-section">
            <h2>Phase Details</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width: 18%">Phase Name</th>
                        <th style="width: 12%">Status</th>
                        <th style="width: 14%">Progress</th>
                        <th style="width: 13%">Start Date</th>
                        <th style="width: 13%">End Date</th>
                        <th style="width: 15%">Project</th>
                    </tr>
                </thead>
                <tbody>
HTML;

        foreach ($phases as $phase) {
            $statusClass = 'status-' . $phase->status;
            $statusLabel = ucfirst(str_replace('_', ' ', $phase->status));
            $progress = (float)($phase->completion_percentage ?? 0);
            $startDate = $phase->planned_start_date 
                ? $phase->planned_start_date->format('M d, Y') 
                : 'Pending';
            $endDate = $phase->planned_end_date 
                ? $phase->planned_end_date->format('M d, Y') 
                : 'Pending';
            $projectName = $phase->project?->project_name ?? 'N/A';

            $html .= <<<HTML
                    <tr>
                        <td>{$phase->phase_name}</td>
                        <td><span class="status-badge {$statusClass}">{$statusLabel}</span></td>
                        <td>
                            <span class="progress-bar"><span class="progress-fill" style="width: {$progress}%"></span></span>
                            {$progress}%
                        </td>
                        <td>{$startDate}</td>
                        <td>{$endDate}</td>
                        <td>{$projectName}</td>
                    </tr>
HTML;
        }

        $html .= <<<HTML
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>This report was automatically generated by the D&G Construction Management System</p>
        </div>
    </div>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Format date helper
     */
    private function formatDate($date)
    {
        return $date->format('F d, Y \a\t h:i A');
    }
}
