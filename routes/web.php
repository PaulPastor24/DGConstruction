<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PhasesExportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = Auth::user();

    return match ($user->role) {
        'engineer'        => redirect()->route('admin.dashboard'),
        'supervisor'      => redirect()->route('supervisor.dashboard'),
        'client'          => redirect()->route('client.dashboard'),
        default           => abort(403, 'Unauthorized role assignment.'),
    };
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==================== ENGINEER / ADMIN MANAGEMENT ====================
Route::middleware(['auth', 'role:engineer'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/timeline', [TimelineController::class, 'adminTimeline'])->name('admin.timeline');
    Route::get('/admin/reports', [AdminDashboardController::class, 'reports'])->name('admin.reports.index');
    Route::get('/admin/phases', [ProjectController::class, 'phaseManagement'])->name('admin.phases');
    Route::post('/admin/reports/{id}/approve', [ProjectController::class, 'approveReport'])->name('admin.reports.approve');
    Route::post('/admin/reports/{id}/revise', [ProjectController::class, 'reviseReport'])->name('admin.reports.revise');
    Route::get('/admin/attendance', [AdminDashboardController::class, 'attendance'])->name('admin.attendance');
    Route::get('/admin/inventory', [AdminDashboardController::class, 'inventory'])->name('admin.inventory');
    Route::post('/admin/inventory/store-delivery', [AdminDashboardController::class, 'storeDelivery'])->name('admin.inventory.store-delivery');
    Route::get('/admin/alerts', [AdminDashboardController::class, 'alerts'])->name('admin.alerts');
    Route::put('/admin/alerts/settings', [AdminDashboardController::class, 'updateSettings'])->name('admin.alerts.update-settings');

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

// ==================== SUPERVISOR GROUP ROUTING LAYER ====================
Route::middleware(['auth', 'role:supervisor'])->group(function () {
    Route::get('/supervisor/dashboard', [SupervisorController::class, 'index'])->name('supervisor.dashboard');
    Route::get('/supervisor/timeline', [SupervisorController::class, 'timeline'])->name('supervisor.timeline');
    Route::get('/supervisor/phases', [SupervisorController::class, 'phases'])->name('supervisor.phases');
    
    // AJAX API endpoints for phases
    Route::get('/supervisor/api/phases/{id}/details', [SupervisorController::class, 'getPhaseDetails'])->name('supervisor.api.phases.details');
    Route::post('/supervisor/api/phases/{id}/update-progress', [SupervisorController::class, 'updatePhaseProgress'])->name('supervisor.api.phases.updateProgress');
    Route::post('/supervisor/api/phases/{id}/update-status', [SupervisorController::class, 'updatePhaseStatus'])->name('supervisor.api.phases.updateStatus');
    Route::post('/supervisor/api/phases/export-pdf', [SupervisorController::class, 'exportPhasesPdf'])->name('supervisor.api.phases.exportPdf');
    
    // Export routes for phases
    Route::get('/supervisor/phases/export/csv', [PhasesExportController::class, 'exportCsv'])->name('supervisor.phases.export.csv');
    Route::get('/supervisor/phases/export/pdf', [PhasesExportController::class, 'exportPdf'])->name('supervisor.phases.export.pdf');
    
    Route::get('/supervisor/attendance', [SupervisorController::class, 'attendance'])->name('supervisor.attendance');
    Route::post('/supervisor/attendance', [SupervisorController::class, 'saveAttendance'])->name('supervisor.attendance.save');
    Route::get('/supervisor/materials', [SupervisorController::class, 'materials'])->name('supervisor.materials');
    Route::post('/supervisor/materials', [SupervisorController::class, 'logDelivery'])->name('supervisor.materials.log');
    Route::get('/supervisor/reports', [ReportController::class, 'supervisorReports'])->name('supervisor.reports');
    
    // Reports API endpoints
    Route::get('/supervisor/api/projects/{project_id}/phases', [ReportController::class, 'getProjectPhases'])->name('supervisor.api.reports.phases');
    Route::get('/supervisor/api/reports/{id}/details', [ReportController::class, 'getReportDetails'])->name('supervisor.api.reports.details');
    Route::get('/supervisor/api/reports/{id}/download-pdf', [ReportController::class, 'downloadReportPdf'])->name('supervisor.api.reports.downloadPdf');
    
    Route::get('/supervisor/reports/{id}', [ReportController::class, 'show'])->name('supervisor.reports.show');
    Route::post('/supervisor/reports/submit', [ReportController::class, 'submitReport'])->name('supervisor.reports.submit');
    Route::get('/supervisor/profile', [SupervisorController::class, 'profile'])->name('supervisor.profile');
    Route::put('/supervisor/profile', [SupervisorController::class, 'updateProfile'])->name('supervisor.profile.update');
    Route::put('/supervisor/profile/password', [SupervisorController::class, 'updatePassword'])->name('supervisor.profile.password');
    Route::get('/supervisor/notifications', [SupervisorController::class, 'notifications'])->name('supervisor.notifications');

    // ◄ ADDED EXPLICIT FAST BIOMETRIC ENROLLMENT PROCESSING DATA PIPELINE LINK
    Route::post('/supervisor/workers/register-biometric', [SupervisorController::class, 'registerWorkerBiometric'])
        ->name('supervisor.workers.register_biometric');
    Route::post('/supervisor/notifications/{id}/mark-read', [SupervisorController::class, 'markNotificationRead'])->name('supervisor.notifications.markRead');
    Route::post('/supervisor/notifications/mark-all-read', [SupervisorController::class, 'markAllNotificationsRead'])->name('supervisor.notifications.markAllRead');
});

Route::middleware(['auth', 'role:client'])->group(function () {
    Route::get('/client/dashboard', [ClientController::class, 'index'])->name('client.dashboard');
    Route::get('/client/myprojects', [ClientController::class, 'myProjects'])->name('client.myprojects');
    Route::get('/client/projects/{project}', [ClientController::class, 'projectDetails'])->name('client.project.show');
    Route::get('/client/timeline', [TimelineController::class, 'clientTimeline'])->name('client.timeline');
    Route::get('/client/reports', [ClientController::class, 'updates'])->name('client.reports');
    Route::get('/client/updates', [ClientController::class, 'updates'])->name('client.updates');
});

require __DIR__.'/auth.php';