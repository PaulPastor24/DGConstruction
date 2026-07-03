<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Project;
use App\Models\ConstructionPhase;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Services\NotificationService;

class ReportController extends Controller
{
    /**
     * Display all reports (Admin view)
     */
    public function index(Request $request)
    {
        $query = Report::with(['project', 'phase', 'submittedBy', 'approvedBy']);

        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('approval_status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('project', function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%");
            });
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->only(['status', 'project_id', 'search']));

        $hasApprovalStatus = Schema::hasColumn('accomplishment_reports', 'approval_status');

        $stats = [
            'total' => (int) Report::query()->count('*'),
            'pending' => (int) ($hasApprovalStatus
                ? Report::query()->where('approval_status', 'pending')->count('*')
                : Report::query()->count('*')),
            'approved' => (int) ($hasApprovalStatus
                ? Report::query()->where('approval_status', 'approved')->count('*')
                : 0),
            'rejected' => (int) ($hasApprovalStatus
                ? Report::query()->where('approval_status', 'rejected')->count('*')
                : 0),
        ];

        return view('admin.reports.index', compact('reports', 'stats'));
    }

    /**
     * Show a specific report (Supervisor/Admin view)
     */
    public function show($reportId)
    {
        $report = Report::with(['project', 'phase', 'submittedBy', 'approvedBy'])->findOrFail($reportId);

        $this->authorizeViewReport($report);

        return view('admin.reports.show', compact('report'));
    }

    /**
     * Approve a report (Admin only)
     */
    public function approve(Request $request, $reportId)
    {
        $report = Report::findOrFail($reportId);

        // Only engineers can approve
        if (auth('web')->user()->role !== 'engineer') {
            abort(403, 'Only engineers can approve reports');
        }

        $validated = $request->validate([
            'approval_remarks' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $report->update([
                'approval_status' => 'approved',
                'approved_by' => auth('web')->user()->user_id,
                'approved_at' => now(),
                'approval_remarks' => $validated['approval_remarks'],
            ]);

            // Update phase completion percentage if provided
            if ($request->filled('completion_percentage')) {
                $completionPercentage = min(100, max(0, (float)$request->completion_percentage));
                $report->phase->update(['completion_percentage' => $completionPercentage]);
            }

            $this->logAction(
                'Report Approved',
                "Report #{$report->report_id} from project '{$report->project->project_name}' approved"
            );

            // Notify the supervisor(s) assigned to this project
            $projectSupervisors = $report->project->supervisors()->wherePivot('is_active', true)->get();
            foreach ($projectSupervisors as $sup) {
                NotificationService::notifySupervisor($sup->user_id, [
                    'type' => 'report',
                    'title' => 'Report Approved',
                    'message' => "Your accomplishment report for '{$report->project->project_name}' has been approved.",
                    'data' => ['module' => 'supervisor.reports', 'report_id' => $report->report_id],
                    'related_id' => $report->report_id,
                    'related_type' => 'report',
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Report approved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Report approval failed: ' . $e->getMessage());
            return back()->withErrors('Failed to approve report');
        }
    }

    /**
     * Reject a report (Admin only)
     */
    public function reject(Request $request, $reportId)
    {
        $report = Report::findOrFail($reportId);

        // Only engineers can reject
        if (auth('web')->user()->role !== 'engineer') {
            abort(403, 'Only engineers can reject reports');
        }

        $validated = $request->validate([
            'approval_remarks' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $report->update([
                'approval_status' => 'rejected',
                'reviewed_by' => auth('web')->user()->user_id,
                'rejected_at' => now(),
                'approval_remarks' => $validated['approval_remarks'],
            ]);

            $this->logAction(
                'Report Rejected',
                "Report #{$report->report_id} from project '{$report->project->project_name}' rejected. Reason: {$validated['approval_remarks']}"
            );

            // Notify supervisor(s)
            $projectSupervisors = $report->project->supervisors()->wherePivot('is_active', true)->get();
            foreach ($projectSupervisors as $sup) {
                NotificationService::notifySupervisor($sup->user_id, [
                    'type' => 'report',
                    'title' => 'Report Requires Revision',
                    'message' => "Your accomplishment report for '{$report->project->project_name}' requires revision: {$validated['approval_remarks']}",
                    'data' => ['module' => 'supervisor.reports', 'report_id' => $report->report_id],
                    'related_id' => $report->report_id,
                    'related_type' => 'report',
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Report rejected. Supervisor will see feedback.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Report rejection failed: ' . $e->getMessage());
            return back()->withErrors('Failed to reject report');
        }
    }

    /**
     * Supervisor submits a report
     */
    public function submitReport(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'phase_id' => 'required|exists:construction_phases,phase_id',
            'report_date' => 'required|date',
            'report_text' => 'required|string|max:5000',
            'site_images' => 'nullable|array|max:5',
            'site_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $phase = ConstructionPhase::findOrFail($validated['phase_id']);

        // Verify supervisor is assigned to this project
        $this->authorizeSupervisor($project);

        try {
            DB::beginTransaction();

            $images = [];
            if ($request->hasFile('site_images')) {
                foreach ($request->file('site_images') as $image) {
                    $path = $image->store('reports', 'public');
                    $images[] = $path;
                }
            }

            $report = Report::create([
                'project_id' => $validated['project_id'],
                'phase_id' => $validated['phase_id'],
                'submitted_by' => auth('web')->user()->user_id,
                'report_date' => $validated['report_date'],
                'report_text' => $validated['report_text'],
                'site_images' => $images,
                'approval_status' => 'pending',
                'ai_status' => 'pending',
            ]);

            $this->logAction(
                'Report Submitted',
                "Report submitted for phase '{$phase->phase_name}' in project '{$project->project_name}'"
            );

            // Notify project engineer / managers - find project engineer
            if ($project->engineer) {
                NotificationService::notifySupervisor($project->engineer->user_id ?? $project->engineer->user_id, [
                    'type' => 'report',
                    'title' => 'New Report Submitted',
                    'message' => "A new report has been submitted for project '{$project->project_name}' by {$report->submittedBy->name}",
                    'data' => ['module' => 'admin.reports', 'report_id' => $report->report_id],
                    'related_id' => $report->report_id,
                    'related_type' => 'report',
                ]);
            }

            // Notify the submitting supervisor
            NotificationService::notifySupervisor(auth('web')->user()->user_id, [
                'type' => 'report',
                'title' => 'Report Submitted',
                'message' => 'Accomplishment Report submitted successfully.',
                'data' => ['module' => 'supervisor.reports', 'report_id' => $report->report_id],
                'related_id' => $report->report_id,
                'related_type' => 'report',
            ]);

            DB::commit();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'report_id' => $report->report_id]);
            }

            return redirect()->back()->with('success', 'Report submitted for approval');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Report submission failed: ' . $e->getMessage());
            return back()->withErrors('Failed to submit report');
        }
    }

    /**
     * Get pending reports for a supervisor
     */
    public function supervisorReports(Request $request)
    {
        $user = auth('web')->user();

        // Get all projects assigned to supervisor
        $assignedProjects = Project::whereHas('supervisors', function ($q) use ($user) {
            $q->where('supervisor_id', $user->user_id);
        })->orderBy('project_name')->get();

        if ($assignedProjects->isEmpty()) {
            $emptyReports = new LengthAwarePaginator([], 0, 15);
            return view('supervisor.reports.index-new', compact('assignedProjects'))
                ->with('reports', $emptyReports)
                ->with('stats', ['total' => 0, 'pending' => 0, 'approved' => 0, 'approved_percent' => 0, 'rejected' => 0, 'rejected_percent' => 0, 'pending_percent' => 0])
                ->with('selectedProject', null)
                ->with('projectPhases', collect())
                ->with('filterPhases', collect());
        }

        // Determine selected project. Default is all assigned projects.
        $assignedProjectIds = $assignedProjects->pluck('project_id')->toArray();
        $selectedProject = null;

        if ($request->query->has('project_id')) {
            if ($request->query('project_id') !== '') {
                $selectedProject = $assignedProjects->firstWhere('project_id', $request->query('project_id'));
            } else {
                $selectedProject = null;
            }
        }

        if (!$request->query->has('project_id') || $request->query('project_id') === null) {
            $selectedProject = $assignedProjects->firstWhere('project_id', session('supervisor_selected_project_id')) ?? $assignedProjects->first();
        }

        $modalProject = $selectedProject ?? $assignedProjects->first();

        if ($selectedProject) {
            session(['supervisor_selected_project_id' => $selectedProject->project_id]);
        }

        if ($modalProject) {
            $projectPhases = ConstructionPhase::query()
                ->where('project_id', $modalProject->project_id)
                ->orderBy('phase_order')
                ->get();
        } else {
            $projectPhases = collect();
        }

        if ($selectedProject) {
            $filterPhases = ConstructionPhase::query()
                ->where('project_id', $selectedProject->project_id)
                ->orderBy('phase_order')
                ->get();
        } else {
            $filterPhases = ConstructionPhase::whereIn('project_id', $assignedProjectIds, 'and', false)
                ->orderBy('project_id')
                ->orderBy('phase_order')
                ->get();
        }

        // Build query for reports scoped to assigned projects only.
        $query = Report::with(['project', 'phase', 'submittedBy', 'reviewedBy', 'approvedBy'])
            ->whereIn('project_id', $assignedProjectIds, 'and', false);

        // Apply project filter when a specific project has been selected.
        if ($selectedProject) {
            $query->where('project_id', $selectedProject->project_id);
        }

        // Apply phase filter
        if ($request->filled('phase_id')) {
            $query->where('phase_id', $request->phase_id);
        }

        // Apply approval status filter
        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('approval_status', $request->status);
        }

        // Apply date range filter
        if ($request->filled('report_date_from')) {
            $query->whereDate('report_date', '>=', $request->report_date_from);
        }
        if ($request->filled('report_date_to')) {
            $query->whereDate('report_date', '<=', $request->report_date_to);
        } elseif ($request->filled('report_date')) {
            $query->whereDate('report_date', $request->report_date);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('project', function ($pq) use ($search) {
                    $pq->where('project_name', 'like', "%{$search}%");
                })->orWhereHas('phase', function ($fq) use ($search) {
                    $fq->where('phase_name', 'like', "%{$search}%");
                });
            });
        }

        // Get paginated reports
        $reports = $query->orderBy('report_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->only(['project_id', 'phase_id', 'status', 'report_date', 'report_date_from', 'report_date_to', 'search']));

        // Calculate statistics for selected project
        $statsQuery = Report::whereHas('project', function ($q) use ($selectedProject, $user) {
            if ($selectedProject) {
                $q->where('project_id', $selectedProject->project_id);
            }
            $q->whereHas('supervisors', function ($sq) use ($user) {
                $sq->where('supervisor_id', $user->user_id);
            });
        });

        $totalReports = (clone $statsQuery)->count();
        $pendingReports = (clone $statsQuery)->where('approval_status', 'pending')->count();
        $approvedReports = (clone $statsQuery)->where('approval_status', 'approved')->count();
        $rejectedReports = (clone $statsQuery)->where('approval_status', 'rejected')->count();

        $stats = [
            'total' => $totalReports,
            'pending' => $pendingReports,
            'pending_percent' => $totalReports > 0 ? round(($pendingReports / $totalReports) * 100, 1) : 0,
            'approved' => $approvedReports,
            'approved_percent' => $totalReports > 0 ? round(($approvedReports / $totalReports) * 100, 1) : 0,
            'rejected' => $rejectedReports,
            'rejected_percent' => $totalReports > 0 ? round(($rejectedReports / $totalReports) * 100, 1) : 0,
        ];

        return view('supervisor.reports.index-new', compact('reports', 'assignedProjects', 'selectedProject', 'modalProject', 'projectPhases', 'filterPhases', 'stats'));
    }

    /**
     * Get phases for a project (AJAX)
     */
    public function getProjectPhases($projectId)
    {
        $user = auth('web')->user();

        $project = Project::query()->where('project_id', $projectId)
            ->whereHas('supervisors', function ($q) use ($user) {
                $q->where('supervisor_id', $user->user_id);
            })->firstOrFail();

        $phases = ConstructionPhase::query()->where('project_id', $projectId)
            ->orderBy('phase_order')
            ->get(['phase_id', 'phase_name', 'phase_order', 'status']);

        return response()->json(['success' => true, 'phases' => $phases]);
    }

    /**
     * Get report details (AJAX)
     */
    public function getReportDetails($reportId)
    {
        $user = auth('web')->user();

        $report = Report::with(['project', 'phase', 'submittedBy', 'approvedBy', 'reviewedBy'])
            ->where('report_id', $reportId)
            ->whereHas('project', function ($q) use ($user) {
                $q->whereHas('supervisors', function ($sq) use ($user) {
                    $sq->where('supervisor_id', $user->user_id);
                });
            })->firstOrFail();

        return response()->json([
            'success' => true,
            'report' => [
                'id' => $report->report_id,
                'report_id' => 'RPT-2026-' . str_pad($report->report_id, 4, '0', STR_PAD_LEFT),
                'project_name' => $report->project->project_name,
                'phase_name' => $report->phase->phase_name,
                'report_date' => $report->report_date->format('M d, Y h:i A'),
                'submitted_by' => $report->submittedBy->name,
                'submitted_by_avatar' => strtoupper(substr($report->submittedBy->name, 0, 1)),
                'approval_status' => $report->approval_status,
                'reviewed_by' => $report->reviewedBy->name ?? '-',
                'approved_by' => $report->approvedBy->name ?? '-',
                'approval_remarks' => $report->approval_remarks ?? 'No remarks',
                'report_text' => $report->report_text,
                'site_images' => $report->site_images ?? [],
                'submitted_at' => $report->created_at->format('M d, Y'),
                'reviewed_at' => $report->reviewed_at ? $report->reviewed_at->format('M d, Y') : '-',
                'approved_at' => $report->approved_at ? $report->approved_at->format('M d, Y') : '-',
                'rejected_at' => $report->rejected_at ? $report->rejected_at->format('M d, Y') : '-',
            ]
        ]);
    }

    /**
     * Download report as PDF
     */
    public function downloadReportPdf($reportId)
    {
        $user = auth('web')->user();

        $report = Report::with(['project', 'phase', 'submittedBy', 'reviewedBy', 'approvedBy'])
            ->where('report_id', $reportId)
            ->whereHas('project', function ($q) use ($user) {
                $q->whereHas('supervisors', function ($sq) use ($user) {
                    $sq->where('supervisor_id', $user->user_id);
                });
            })->firstOrFail();

        $html = view('supervisor.reports.pdf', compact('report'))->render();

        try {
            if (class_exists('\Mpdf\Mpdf')) {
                $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 10, 'margin_bottom' => 10]);
                $mpdf->WriteHTML($html);
                $fileName = 'report_' . $report->report_id . '_' . date('Y-m-d') . '.pdf';
                return $mpdf->Output($fileName, 'D');
            }
        } catch (\Exception $e) {
            Log::error('PDF generation failed: ' . $e->getMessage());
        }

        // Fallback: return HTML as a downloadable attachment when mPDF is unavailable.
        $fileName = 'report_' . $report->report_id . '_' . date('Y-m-d') . '.html';
        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Delete a report
     */
    public function destroy($reportId)
    {
        $report = Report::findOrFail($reportId);

        // Only can delete if pending and user is the submitter or admin
        if ($report->approval_status !== 'pending' && auth('web')->user()->role !== 'engineer') {
            abort(403, 'Cannot delete approved or rejected reports');
        }

        try {
            DB::beginTransaction();

            $reportDetails = "Report #{$report->report_id}";
            $report->delete();

            $this->logAction('Report Deleted', $reportDetails . ' deleted');

            DB::commit();

            return redirect()->back()->with('success', 'Report deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Report deletion failed: ' . $e->getMessage());
            return back()->withErrors('Failed to delete report');
        }
    }

    /**
     * Authorize that user can view this report
     */
    private function authorizeViewReport(Report $report)
    {
        $user = auth('web')->user();

        // Engineer can view all reports
        if ($user->role === 'engineer') {
            return true;
        }

        // Supervisor can view reports from their assigned projects
        if ($user->role === 'supervisor') {
            if ($report->project->supervisors()->where('supervisor_id', $user->user_id)->exists()) {
                return true;
            }
        }

        abort(403, 'Unauthorized to view this report');
    }

    /**
     * Authorize supervisor is assigned to project
     */
    private function authorizeSupervisor(Project $project)
    {
        if (auth('web')->user()->role !== 'supervisor') {
            abort(403, 'Only supervisors can submit reports');
        }

        if (!$project->supervisors()->where('supervisor_id', auth('web')->user()->user_id)->exists()) {
            abort(403, 'You are not assigned to this project');
        }
    }

    /**
     * Log system action
     */
    private function logAction($action, $description)
    {
        SystemLog::create([
            'user_id' => auth('web')->user()->user_id,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
