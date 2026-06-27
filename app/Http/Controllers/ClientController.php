<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project; 
use App\Models\Report; // Using accomplishment_reports as the update log table

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $projects = collect();
        $project = null;
        $latest_update = null;

        // 1. Fetch ALL projects for this client
        if ($user && $user->client) {
            $projects = Project::where('client_id', $user->client->client_id)
                ->orderBy('created_at', 'desc')
                ->get();
                
            // Check if the client selector sent a specific project ID via form submit
            if ($request->filled('project_id')) {
                $project = $projects->firstWhere('project_id', $request->project_id);
            }
            
            // Fallback to the first project if no ID was sent or found
            if (!$project) {
                $project = $projects->first();
            }
        }

        // 2. Fetch the latest report tracking across the currently selected project context
        if ($project) {
            $latest_update = Report::where('project_id', $project->project_id)
                ->latest('report_date')
                ->first();
                
            // Format dynamic snippet field for the site update paragraph view expectation
            if ($latest_update) {
                $latest_update->content = $latest_update->report_text;
                $latest_update->log_date = $latest_update->report_date;
            }
        }

        // 3. Pass both the collection and the single project fallback to the dashboard view
        return view('client.dashboard', compact('projects', 'project', 'latest_update'));
    }

    /**
     * Display the timeline and phases for the client portal.
     */
    public function timeline(Request $request)
    {
        $user = Auth::user();

        $projects = collect();
        $project = null;
        $latest_update = null;

        if ($user && $user->client) {
            $projects = Project::where('client_id', $user->client->client_id)
                ->orderBy('created_at', 'desc')
                ->get();
                
            if ($request->filled('project_id')) {
                $project = $projects->firstWhere('project_id', $request->project_id);
            }
            
            if (!$project) {
                $project = $projects->first();
            }
        }

        if ($project) {
            $latest_update = Report::where('project_id', $project->project_id)
                ->latest('report_date')
                ->first();
                
            if ($latest_update) {
                $latest_update->content = $latest_update->report_text;
                $latest_update->log_date = $latest_update->report_date;
            }
        }

        return view('client.status', compact('projects', 'project', 'latest_update')); 
    }

    /**
     * Display the continuous feed of site updates.
     */
    public function updates(Request $request)
    {
        $user = Auth::user();
        
        $projects = collect();
        $project = null;
        $updates = collect();

        if ($user && $user->client) {
            $projects = Project::where('client_id', $user->client->client_id)
                ->orderBy('created_at', 'desc')
                ->get();
                
            if ($request->filled('project_id')) {
                $project = $projects->firstWhere('project_id', $request->project_id);
            }
            
            if (!$project) {
                $project = $projects->first();
            }
        }

        if ($project) {
            $updates = Report::where('project_id', $project->project_id)
                ->latest('report_date')
                ->get();
        }
        
        return view('client.update', compact('projects', 'project', 'updates')); 
    }
}