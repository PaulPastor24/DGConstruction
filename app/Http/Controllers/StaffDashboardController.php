<?php

namespace App\Http\Controllers;

class StaffDashboardController extends Controller
{
    public function index()
    {
        // Build the same dashboard data as Admin, but render the independent
        // Staff Blade template so Staff UI can evolve without changing Admin.
        $adminView = app(AdminDashboardController::class)->index();

        return view('staff.dashboard', $adminView->getData());
    }
}
