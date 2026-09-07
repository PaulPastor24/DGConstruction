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
        $filterClients = Client::query()
            ->whereIn('client_id', function ($q) {
                $q->select('client_id')->from('project_archives')->whereNotNull('client_id')->distinct();
            })
            ->with('user')
            ->orderBy('company_name')
            ->get();

        $filterEngineers = User::query()
            ->whereIn('user_id', function ($q) {
                $q->select('engineer_id')->from('project_archives')->whereNotNull('engineer_id')->distinct();
            })
            ->orderBy('first_name')
            ->get(['user_id', 'first_name', 'last_name', 'email']);

        return view('admin.project_archives.index', compact('archives', 'filterClients', 'filterEngineers'));
    }
}
