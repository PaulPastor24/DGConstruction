<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupervisorController extends Controller
{
    /**
     * Supervisor Dashboard / Submit Report View
     */
    public function index()
    {
        return view('supervisor.dashboard'); // Change this if your dashboard view is inside a folder (e.g., 'supervisor.dashboard')
    }

    /**
     * Project Timeline View
     */
    public function timeline()
    {
        // Points to resources/views/supervisor/timeline.blade.php
        return view('supervisor.timeline'); 
    }

    public function attendance()
    {
        // Points to resources/views/supervisor/attendance.blade.php
        return view('supervisor.attendance'); 
    }

    public function materials()
    {
        // Points to resources/views/supervisor/materials.blade.php
        // Note: Use the exact lowercase/uppercase name matching your file system
        return view('supervisor.material'); 
    }

    // Placeholder handlers for forms to prevent future routing errors
    public function saveAttendance(Request $request)
    {
        // Add database saving logic later
        return back()->with('success', 'Attendance saved successfully.');
    }

    public function logDelivery(Request $request)
    {
        // Add material logging logic later
        return back()->with('success', 'Delivery logged successfully.');
    }
}