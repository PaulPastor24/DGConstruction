<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Project;
use App\Models\ConstructionPhase;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
            'report_text' => 'required|string',
            'site_images' => 'nullable|array|max:5',
            'site_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
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
                'report_date' => now()->toDateString(),
                'report_text' => $validated['report_text'],
                'site_images' => $images,
                'approval_status' => 'pending',
            ]);

            $this->logAction(
                'Report Submitted',
                "Report submitted for phase '{$phase->phase_name}' in project '{$project->project_name}'"
            );

            DB::commit();

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

        $query = Report::whereHas('project', function ($q) use ($user) {
            $q->whereHas('supervisors', function ($sq) use ($user) {
                $sq->where('supervisor_id', $user->user_id);
            });
        })->with(['project', 'phase', 'submittedBy', 'approvedBy']);

        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('approval_status', $request->status);
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->only(['status']));

        return view('supervisor.reports.index', compact('reports'));
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
        if ($user->role === 'site_supervisor') {
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
        if (auth('web')->user()->role !== 'site_supervisor') {
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
