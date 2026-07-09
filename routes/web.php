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
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BiometricController;

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
    Route::get('/admin/timeline/data/{project}', [TimelineController::class, 'timelineData'])->name('admin.timeline.data');
    Route::get('/admin/reports', [AdminDashboardController::class, 'reports'])->name('admin.reports.index');
    Route::get('/admin/reports/data', [AdminDashboardController::class, 'reportsData'])->name('admin.reports.data');
    Route::get('/admin/reports/{id}/details', [AdminDashboardController::class, 'reportDetails'])->name('admin.reports.details');
    Route::get('/admin/reports/{id}/download-pdf', [AdminDashboardController::class, 'downloadReportPdf'])->name('admin.reports.downloadPdf');
    Route::get('/admin/phases', [ProjectController::class, 'phaseManagement'])->name('admin.phases');
    Route::post('/admin/reports/{id}/evaluate', [ReportController::class, 'evaluate'])->name('admin.reports.evaluate');
    Route::post('/admin/reports/{id}/approve', [ReportController::class, 'approve'])->name('admin.reports.approve');
    Route::post('/admin/reports/{id}/revise', [ReportController::class, 'revise'])->name('admin.reports.revise');
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

    Route::post('/admin/milestones', [MilestoneController::class, 'store'])->name('admin.milestones.store');
    Route::get('/admin/projects/{project}/phases/{phase}/milestones', [MilestoneController::class, 'index'])->name('admin.milestones.index');
    Route::get('/admin/projects/{project}/phases/{phase}/milestones/create', [MilestoneController::class, 'create'])->name('admin.milestones.create');
    Route::get('/admin/projects/{project}/phases/{phase}/milestones/{milestone}/edit', [MilestoneController::class, 'edit'])->name('admin.milestones.edit');
    Route::put('/admin/projects/{project}/phases/{phase}/milestones/{milestone}', [MilestoneController::class, 'update'])->name('admin.milestones.update');
    Route::delete('/admin/projects/{project}/phases/{phase}/milestones/{milestone}', [MilestoneController::class, 'destroy'])->name('admin.milestones.destroy');
    Route::post('/admin/projects/{project}/phases/{phase}/milestones/{milestone}/complete', [MilestoneController::class, 'complete'])->name('admin.milestones.complete');
    Route::post('/admin/projects/{project}/phases/{phase}/milestones/{milestone}/delay', [MilestoneController::class, 'markDelayed'])->name('admin.milestones.delay');
    Route::patch('/admin/projects/{project}/phases/{phase}/milestones/{milestone}/complete', [MilestoneController::class, 'complete'])->name('admin.milestones.complete');
    Route::patch('/admin/projects/{project}/phases/{phase}/milestones/{milestone}/mark-delayed', [MilestoneController::class, 'markDelayed'])->name('admin.milestones.mark-delayed');
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
    // --- BIOMETRIC FINGERPRINT API ROUTES ---
    Route::post('/passkeys/register/options', [BiometricController::class, 'registerOptions'])->name('passkeys.register.options');
    Route::post('/passkeys/login/options', [BiometricController::class, 'loginOptions'])->name('passkeys.login.options');
    Route::post('/passkeys/login', [BiometricController::class, 'login'])->name('passkeys.login');
});

Route::middleware(['auth', 'role:client'])->group(function () {
    Route::get('/client/dashboard', [ClientController::class, 'index'])->name('client.dashboard');
    Route::get('/client/myprojects', [ClientController::class, 'myProjects'])->name('client.myprojects');
    Route::get('/client/projects/{project}', [ClientController::class, 'projectDetails'])->name('client.project.show');
    Route::get('/client/timeline', [TimelineController::class, 'clientTimeline'])->name('client.timeline');
    Route::get('/client/milestones', [TimelineController::class, 'clientTimeline'])->name('client.milestones');
    Route::get('/client/reports', [ClientController::class, 'updates'])->name('client.reports');
    Route::get('/client/reports/{id}/download-pdf', [ClientController::class, 'downloadReportPdf'])->name('client.reports.downloadPdf');
    Route::get('/client/updates', [ClientController::class, 'updates'])->name('client.updates');
    Route::get('/client/notifications', [ClientController::class, 'notifications'])->name('client.notifications');
    Route::post('/client/notifications/{id}/mark-read', [ClientController::class, 'markNotificationRead'])->name('client.notifications.markRead');
    Route::get('/client/notifications/{id}/mark-read-redirect', [ClientController::class, 'markNotificationReadAndRedirect'])->name('client.notifications.markReadRedirect');
    Route::post('/client/notifications/mark-all-read', [ClientController::class, 'markAllNotificationsRead'])->name('client.notifications.markAllRead');
});

require __DIR__.'/auth.php';