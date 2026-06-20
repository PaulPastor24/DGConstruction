<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;


Route::get('/', function () {
    return view('welcome');
});

// Default generic fallback dashboard route
Route::get('/dashboard', function () {
    $user = Auth::user();

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


Route::middleware(['auth', 'role:engineer'])->group(function () {

    // Management Dashboard Base
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Sidebar Operational Framework Target Links (Routed to Controller Methods)
    Route::get('/admin/timeline', [TimelineController::class, 'adminTimeline'])->name('admin.timeline');
    Route::get('/admin/reports', [AdminDashboardController::class, 'reports'])->name('admin.reports.index');
    Route::get('/admin/phases', [AdminDashboardController::class, 'phases'])->name('admin.phases');
    Route::get('/admin/attendance', [AdminDashboardController::class, 'attendance'])->name('admin.attendance');
    Route::get('/admin/inventory', [AdminDashboardController::class, 'inventory'])->name('admin.inventory');
    Route::get('/admin/alerts', [AdminDashboardController::class, 'alerts'])->name('admin.alerts');

    // ==================== PROJECT MANAGEMENT ====================
    Route::prefix('admin/projects')->name('admin.projects.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::get('/create', [ProjectController::class, 'create'])->name('create');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('edit');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy');
        Route::patch('/{project}/archive', [ProjectController::class, 'archive'])->name('archive');
    });

    // ==================== USER MANAGEMENT ====================
    Route::prefix('admin/users')->name('admin.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggleStatus');
    });
});

// Only Site Supervisors can enter here
Route::middleware(['auth', 'role:site_supervisor'])->group(function () {
    Route::get('/supervisor/dashboard', [SupervisorController::class, 'index'])->name('supervisor.dashboard');
    Route::get('/supervisor/timeline', [TimelineController::class, 'supervisorTimeline'])->name('supervisor.timeline');
    Route::get('/supervisor/reports', [ReportController::class, 'supervisorReports'])->name('supervisor.reports');
    Route::post('/supervisor/reports/submit', [ReportController::class, 'submitReport'])->name('supervisor.reports.submit');
});

// Only Clients can enter here
Route::middleware(['auth', 'role:client'])->group(function () {
    Route::get('/client/dashboard', [ClientController::class, 'index'])->name('client.dashboard');
    Route::get('/client/timeline', [TimelineController::class, 'clientTimeline'])->name('client.timeline');
});

require __DIR__.'/auth.php';