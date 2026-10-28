<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SystemLog;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class WorkerAllocationController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);
        $workers = Worker::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%')
                        ->orWhere('trade', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('is_active', $status === 'active'))
            ->orderBy('last_name')->orderBy('first_name')->orderBy('worker_id')
            ->paginate(20)->withQueryString();

        $assignments = DB::table('project_workers')
            ->join('projects', 'projects.project_id', '=', 'project_workers.project_id')
            ->whereIn('worker_id', $workers->pluck('worker_id'))
            ->where('project_workers.is_active', true)
            ->select('project_workers.worker_id', 'projects.project_name', 'projects.project_id')
            ->distinct()->orderBy('project_name')->get()->groupBy('worker_id');
        $projects = Project::query()->notArchived()->orderBy('project_name')->get(['project_id', 'project_name']);
        $routePrefix = $request->routeIs('admin.*') ? 'admin' : 'supervisor';

        return view('workers.index', compact('workers', 'assignments', 'projects', 'routePrefix'));
    }

    public function allocate(Request $request, int $workerId)
    {
        $data = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,project_id'],
            'mode' => ['required', 'in:assign,transfer'],
        ]);

        $this->saveAllocation($request, [$workerId], $data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Worker allocation saved.']);
        }

        return back()->with('success', 'Worker allocation saved.');
    }

    public function bulkAllocate(Request $request)
    {
        $data = $request->validate([
            'worker_ids' => ['required', 'array', 'min:1', 'max:100'],
            'worker_ids.*' => ['required', 'integer', 'distinct', 'exists:workers,worker_id'],
            'project_id' => ['required', 'integer', 'exists:projects,project_id'],
            'mode' => ['required', 'in:assign,transfer'],
        ]);
        $this->saveAllocation($request, $data['worker_ids'], $data);
        $count = count($data['worker_ids']);
        $message = $count.' '.($count === 1 ? 'worker' : 'workers').' allocated successfully.';

        if ($request->expectsJson()) {
            $request->session()->flash('success', $message);
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    private function saveAllocation(Request $request, array $workerIds, array $data): void
    {
        DB::transaction(function () use ($request, $workerIds, $data) {
            // Lock in a stable order and validate the entire group before changing any deployments.
            $workers = Worker::query()->whereIn('worker_id', $workerIds)->orderBy('worker_id')->lockForUpdate()->get();
            abort_unless($workers->count() === count($workerIds), 404);
            if ($workers->contains(fn ($worker) => ! $worker->is_active)) {
                throw ValidationException::withMessages(['worker_ids' => 'Inactive workers cannot be allocated. Refresh the roster and select active workers.']);
            }
            $project = Project::query()->notArchived()->lockForUpdate()->find($data['project_id']);
            if (! $project) {
                throw ValidationException::withMessages(['project_id' => 'Choose a project that is not archived.']);
            }

            foreach ($workers as $worker) {
                $workerId = $worker->worker_id;
                if ($data['mode'] === 'transfer') {
                    DB::table('project_workers')->where('worker_id', $workerId)
                        ->where('project_id', '!=', $project->project_id)->where('is_active', true)
                        ->update(['is_active' => false]);
                }

                $alreadyAssigned = DB::table('project_workers')->where('worker_id', $workerId)
                    ->where('project_id', $project->project_id)->where('is_active', true)->exists();
                if (! $alreadyAssigned) {
                    // Create a new deployment instead of moving historical attendance to a different project.
                    $deployment = [
                        'worker_id' => $workerId, 'project_id' => $project->project_id,
                        'is_active' => true, 'created_at' => now(),
                    ];
                    foreach (['deployed_date', 'start_date'] as $column) {
                        if (Schema::hasColumn('project_workers', $column)) {
                            $deployment[$column] = now()->toDateString();
                        }
                    }
                    DB::table('project_workers')->insert($deployment);
                }

                SystemLog::create([
                    'user_id' => $request->user()->user_id,
                    'action' => 'Worker Allocated',
                    'description' => "Worker #{$workerId} {$worker->full_name}: {$data['mode']} to project #{$project->project_id} {$project->project_name}",
                    'ip_address' => $request->ip(),
                ]);
            }
        });
    }
}
