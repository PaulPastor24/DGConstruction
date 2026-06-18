<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\SupervisorController; 
use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Default generic fallback dashboard route
Route::get('/dashboard', function () {
    $user = auth()->user();

    // Dynamically route users to their correct workspace matching web.php gates
    return match ($user->role) {
        'engineer'        => redirect()->route('admin.dashboard'),
        'site_supervisor' => redirect()->route('supervisor.dashboard'),
        'client'          => redirect()->route('client.dashboard'),
        default           => abort(403, 'Unauthorized role assignment.'),
    };
})->middleware(['auth'])->name('dashboard');

// Profile management paths (Requires authentication)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Only Lead Project Engineers can enter here
Route::middleware(['auth', 'role:engineer'])->group(function () {
    // Management Dashboard Base
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Sidebar Operational Framework Target Links (Routed to Controller Methods)
    Route::get('/admin/timeline', [AdminDashboardController::class, 'timeline'])->name('admin.timeline');
    Route::get('/admin/reports', [AdminDashboardController::class, 'reports'])->name('admin.reports.index');
    Route::get('/admin/phases', [AdminDashboardController::class, 'phases'])->name('admin.phases');
    Route::get('/admin/attendance', [AdminDashboardController::class, 'attendance'])->name('admin.attendance');
    Route::get('/admin/inventory', [AdminDashboardController::class, 'inventory'])->name('admin.inventory');
    Route::get('/admin/alerts', [AdminDashboardController::class, 'alerts'])->name('admin.alerts');

    // Project Actions & Management Paths
    Route::get('/admin/projects', function () { return 'Projects Index Page'; })->name('admin.projects.index');
    Route::get('/admin/projects/create', function () { return 'Create Project Form'; })->name('admin.projects.create');
    Route::get('/admin/projects/{id}', function ($id) { return 'Project Details Page for ID: ' . $id; })->name('admin.projects.show');
});

// Only Site Supervisors can enter here
Route::middleware(['auth', 'role:site_supervisor'])->group(function () {
    Route::get('/supervisor/dashboard', [SupervisorController::class, 'index'])->name('supervisor.dashboard');
});

// Only Clients can enter here
Route::middleware(['auth', 'role:client'])->group(function () {
    Route::get('/client/dashboard', [ClientController::class, 'index'])->name('client.dashboard');
});

require __DIR__.'/auth.php';