<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\SupervisorController; 
use App\Http\Controllers\ClientController;
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

// ==================== ENGINEER / ADMIN MANAGEMENT ====================
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

// ==================== SUPERVISOR PORTAL ROUTES ====================
Route::middleware(['auth', 'role:site_supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', [SupervisorController::class, 'index'])->name('dashboard');
    Route::get('/timeline', [SupervisorController::class, 'timeline'])->name('timeline');
    
    // Attendance view and storage processing paths
    Route::get('/attendance', [SupervisorController::class, 'attendance'])->name('attendance');
    Route::post('/attendance', [SupervisorController::class, 'saveAttendance'])->name('attendance.save');
    
    // Material view and tracking update paths
    Route::get('/materials', [SupervisorController::class, 'materials'])->name('materials');
    Route::post('/materials', [SupervisorController::class, 'logDelivery'])->name('materials.log');
});

// ==================== CLIENT MANAGEMENT ====================
Route::middleware(['auth', 'role:client'])->prefix('client')->name('client.')->group(function () {
    
    // Client Dashboard Page
    Route::get('/dashboard', [ClientController::class, 'index'])->name('dashboard');
    
    // Timeline & Milestones Page (Maps to status.blade.php)
    Route::get('/timeline', [ClientController::class, 'timeline'])->name('timeline');
    
    // Site Updates Feed Page (Maps to update.blade.php)
    Route::get('/updates', [ClientController::class, 'updates'])->name('updates');
    
});

require __DIR__.'/auth.php';