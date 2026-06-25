@extends('layouts.admin')

@section('title', 'System Alerts - D&G Construction Monitor')
@section('page_title', 'System Alerts')

@section('content')
<div class="container-fluid px-0" id="pg-alerts">
    <div class="row g-4">
        
        <!-- Left Side: Critical Alerts Dynamic List -->
        <div class="col-12 col-xl-7 col-xxl-8">
            <div class="d-flex flex-column gap-3">
                <h5 class="fw-bold text-dark mb-1">Critical Alerts</h5>
                
                @forelse($alerts ?? [] as $alert)
                    @php
                        // Dynamically assign theme styles based on alert severity level
                        $severity = strtolower($alert->severity ?? 'info');
                        $bgClass = 'bg-info-subtle';
                        $borderStyle = 'border: 1px solid rgba(13,110,253,0.25); border-left: 5px solid #0d6efd !important;';
                        $badgeText = 'Info';
                        
                        if ($severity === 'danger' || $severity === 'critical') {
                            $bgClass = 'bg-danger-subtle';
                            $borderStyle = 'border: 1px solid rgba(220,53,69,0.25); border-left: 5px solid #dc3545 !important;';
                            $badgeText = 'Danger';
                        } elseif ($severity === 'warning') {
                            $bgClass = 'bg-warning-subtle';
                            $borderStyle = 'border: 1px solid rgba(255,193,7,0.35); border-left: 5px solid #ffc107 !important;';
                            $badgeText = 'Warning';
                        }
                    @endphp

                    <div class="card border-0 shadow-sm rounded-3 {{ $bgClass }}" style="{{ $borderStyle }}">
                        <div class="card-body p-4 d-flex align-items-start gap-4">
                            <!-- Severity Identifier -->
                            <div class="fw-bold text-dark text-uppercase tracking-wider pt-1 flex-shrink-0" style="font-size: 13px; width: 70px;">
                                {{ $badgeText }}
                            </div>
                            
                            <!-- Content Details Pipeline -->
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-dark mb-2" style="font-size: 15px;">{{ $alert->title }}</h6>
                                <p class="text-secondary mb-2" style="font-size: 13px; line-height: 1.5;">{{ $alert->message }}</p>
                                <div class="text-muted d-flex gap-2 flex-wrap align-items-center" style="font-size: 11px;">
                                    <span>{{ $alert->created_at ? $alert->created_at->format('M d, Y - h:i A') : '' }}</span>
                                    @if(!empty($alert->source))
                                        <span class="text-secondary opacity-50">&middot;</span>
                                        <span>{{ $alert->source }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card border-0 shadow-sm bg-white p-5 text-center text-muted">
                        <span class="d-block display-5 text-light mb-3">🛡️</span>
                        <p class="mb-0 fw-medium">All active project nodes are reporting normal parameters. No structural anomalies or shortages flagged.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Side: Analytics Metrics Summary & Controls -->
        <div class="col-12 col-xl-5 col-xxl-4">
            <div class="card border-0 shadow-sm rounded-3 bg-light p-4 h-100 border">
                
                <!-- Section 1: Dynamic Counter Badges -->
                <h5 class="fw-bold text-dark mb-3" style="font-size: 16px;">Alert Summary</h5>
                <div class="d-flex flex-column gap-2 mb-4">
                    <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-danger-subtle border border-danger-subtle">
                        <span class="fw-semibold text-dark" style="font-size: 14px;">Critical</span>
                        <span class="h5 fw-bold text-danger mb-0">{{ $summary['critical_count'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-warning-subtle border border-warning-subtle">
                        <span class="fw-semibold text-dark" style="font-size: 14px;">Warning</span>
                        <span class="h5 fw-bold text-warning mb-0">{{ $summary['warning_count'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-info-subtle border border-info-subtle">
                        <span class="fw-semibold text-dark" style="font-size: 14px;">Info</span>
                        <span class="h5 fw-bold text-primary mb-0">{{ $summary['info_count'] ?? 0 }}</span>
                    </div>
                </div>

                <!-- Section 2: Real-time System Form Preferences -->
                <hr class="text-secondary opacity-25 my-4">
                
                <h5 class="fw-bold text-dark mb-3" style="font-size: 14px;">Notification Settings</h5>
                <form action="{{ route('admin.alerts.update-settings') }}" method="POST" class="m-0">
                    @csrf
                    @method('PUT')
                    
                    <div class="d-flex flex-column gap-3">
                        <div class="form-check form-check-custom">
                            <input class="form-check-input border-secondary" type="checkbox" name="low_stock_alerts" id="setting-stock" value="1" onchange="this.form.submit()" {{ ($settings['low_stock_alerts'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label text-dark fw-medium ms-1" for="setting-stock" style="font-size: 13px;">
                                Low stock alerts (email)
                            </label>
                        </div>
                        
                        <div class="form-check form-check-custom">
                            <input class="form-check-input border-secondary" type="checkbox" name="milestone_deviation_flags" id="setting-milestone" value="1" onchange="this.form.submit()" {{ ($settings['milestone_deviation_flags'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label text-dark fw-medium ms-1" for="setting-milestone" style="font-size: 13px;">
                                Milestone deviation flags
                            </label>
                        </div>
                        
                        <div class="form-check form-check-custom">
                            <input class="form-check-input border-secondary" type="checkbox" name="pending_report_reminders" id="setting-reports" value="1" onchange="this.form.submit()" {{ ($settings['pending_report_reminders'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label text-dark fw-medium ms-1" for="setting-reports" style="font-size: 13px;">
                                Pending report reminders
                            </label>
                        </div>
                        
                        <div class="form-check form-check-custom">
                            <input class="form-check-input border-secondary" type="checkbox" name="daily_attendance_summary" id="setting-attendance" value="1" onchange="this.form.submit()" {{ ($settings['daily_attendance_summary'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label text-dark fw-medium ms-1" for="setting-attendance" style="font-size: 13px;">
                                Daily attendance summary
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<style>
    /* Styling adjustments mimicking image_0664e7.png aesthetics */
    .bg-danger-subtle { background-color: #fdf2f2 !important; }
    .bg-warning-subtle { background-color: #fdfaf2 !important; }
    .bg-info-subtle { background-color: #f2f7fd !important; }
    
    .text-danger { color: #dc3545 !important; }
    .text-warning { color: #d97706 !important; }
    
    .form-check-input:checked {
        background-color: #198754 !important;
        border-color: #198754 !important;
    }
</style>
@endsection