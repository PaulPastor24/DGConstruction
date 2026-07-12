<?php

namespace App\Http\Controllers;

use App\Models\ProjectArchive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectArchiveController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user || !in_array(strtolower((string) $user->role), ['engineer', 'admin', 'administrator'], true)) {
            abort(403);
        }

        $query = ProjectArchive::query()->with(['project', 'client.user', 'engineer'])->latest('archived_at');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                    ->orWhere('project_location', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('engineer_id')) {
            $query->where('engineer_id', $request->engineer_id);
        }

        $archives = $query->paginate(10)->withQueryString();
        $filterClients = ProjectArchive::query()
            ->whereNotNull('client_id')
            ->with('client.user')
            ->select('client_id')
            ->distinct()
            ->get()
            ->map(fn ($archive) => $archive->client)
            ->filter()
            ->sortBy(fn ($client) => $client->company_name ?? '')
            ->values();

        $filterEngineers = ProjectArchive::query()
            ->whereNotNull('engineer_id')
            ->with('engineer')
            ->select('engineer_id')
            ->distinct()
            ->get()
            ->map(fn ($archive) => $archive->engineer)
            ->filter()
            ->sortBy(fn ($engineer) => $engineer->full_name ?? $engineer->name ?? '')
            ->values();

        return view('admin.project_archives.index', compact('archives', 'filterClients', 'filterEngineers'));
    }
}
