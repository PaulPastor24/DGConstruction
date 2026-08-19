<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function timeline(Request $request): View
    {
        return $this->staffView('staff.timeline', app(TimelineController::class)->adminTimeline($request));
    }

    public function reports(Request $request): View
    {
        return $this->staffView('staff.reports', app(AdminDashboardController::class)->reports($request));
    }

    public function attendance(Request $request): View
    {
        return $this->staffView('staff.attendance', app(AdminDashboardController::class)->attendance($request));
    }

    public function inventory(Request $request): View
    {
        return $this->staffView('staff.inventory', app(AdminDashboardController::class)->inventory($request));
    }

    public function projects(Request $request): View
    {
        return $this->staffView('staff.projects.index', app(ProjectController::class)->index($request));
    }

    public function createProject(): View
    {
        return $this->staffView('staff.projects.create', app(ProjectController::class)->create());
    }

    private function staffView(string $view, mixed $response): View
    {
        return view($view, $response instanceof View ? $response->getData() : []);
    }
}
