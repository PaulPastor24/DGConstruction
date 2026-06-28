@extends('layouts.supervisor')

@section('title', 'Supervisor Dashboard - Field Operations Command')
@section('page_title', 'Supervisor Dashboard')

@section('content')
<div class="d-flex flex-column gap-4">
    <section class="page-card">
        <div class="page-hero">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div>
                    <div class="eyebrow">Good Morning, {{ $user->name ?? 'Supervisor' }}</div>
                    <h1 class="page-title mb-2">Today&apos;s Site Priorities</h1>
                    <p class="page-subtitle mb-0">Operational summary for your assigned construction project.</p>
                </div>
                <div class="d-flex align-items-center gap-2 text-muted fw-semibold">
                    <span class="badge rounded-pill bg-soft text-primary">
                        <i class="bi bi-calendar3 me-2"></i>{{ now()->format('M d, Y') }}
                    </span>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-12 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-title">Workforce Readiness</div>
                        <div class="stat-value">{{ $attendancePresentCount }} / {{ $projectWorkersCount }}</div>
                        <div class="stat-meta">Workers present</div>
                        <span class="badge rounded-pill bg-success-subtle text-success-emphasis mt-2">{{ $attendancePresentCount >= $projectWorkersCount ? 'Ready' : 'Pending' }}</span>
                    </div>
                </div>
                <div class="col-12 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-title">Current Site Phase</div>
                        <div class="stat-value">{{ $primaryPhase->phase_name ?? 'No active phase' }}</div>
                        <div class="stat-meta">{{ $projectProgress }}% complete</div>
                        <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis mt-2">{{ $primaryPhase ? 'Active' : 'Pending start' }}</span>
                    </div>
                </div>
                <div class="col-12 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-title">Today&apos;s Site Tasks</div>
                        <div class="stat-value">{{ $pendingTasksCount }}</div>
                        <div class="stat-meta">Items to complete</div>
                        <span class="badge rounded-pill bg-success-subtle text-success-emphasis mt-2">Attendance, reports, follow-up</span>
                    </div>
                </div>
                <div class="col-12 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-title">Upcoming Deadline</div>
                        <div class="stat-value">{{ $upcomingMilestone ? $upcomingMilestone->planned_date->format('M d') : 'No date' }}</div>
                        <div class="stat-meta">{{ $upcomingMilestone->milestone_name ?? 'No upcoming milestone' }}</div>
                        <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis mt-2">{{ $upcomingMilestone ? 'High priority' : 'All clear' }}</span>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
                <a href="{{ route('supervisor.attendance') }}" class="btn btn-primary-soft px-4 py-2">
                    <i class="bi bi-person-check me-2"></i>Record Attendance
                </a>
                <a href="{{ route('supervisor.reports') }}" class="btn btn-outline-soft px-4 py-2">
                    <i class="bi bi-file-earmark-text me-2"></i>Submit Report
                </a>
                <a href="{{ route('supervisor.timeline') }}" class="btn btn-outline-soft px-4 py-2">
                    <i class="bi bi-calendar3 me-2"></i>Project Timeline
                </a>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-12 col-xl-7">
            <section class="section-card h-100">
                <div class="section-card-body">
                    <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom">
                        <div>
                            <h5 class="fw-bold mb-1">Assigned Project</h5>
                            <p class="text-muted mb-0 small">Your current supervision assignment</p>
                        </div>
                        <span class="badge rounded-pill bg-success-subtle text-success-emphasis">{{ $primaryProject->status ?? 'Active' }}</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="small fw-bold text-muted text-uppercase mb-2">Project Name</div>
                            <div class="fw-semibold">{{ $primaryProject->project_name ?? 'No project assigned' }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="small fw-bold text-muted text-uppercase mb-2">Project Location</div>
                            <div class="fw-semibold">{{ $primaryProject->project_location ?? 'Not available' }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="small fw-bold text-muted text-uppercase mb-2">Current Status</div>
                            <div class="fw-semibold">{{ ucfirst($primaryProject->status ?? 'Active') }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="small fw-bold text-muted text-uppercase mb-2">Target Completion</div>
                            <div class="fw-semibold">{{ optional($primaryProject->target_end_date)->format('M d, Y') ?? 'Pending' }}</div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-muted text-uppercase">Overall Progress</span>
                            <span class="fs-4 fw-bold text-primary">{{ $projectProgress }}%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $projectProgress }}%"></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section-card h-100">
                <div class="section-card-body">
                    <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom">
                        <div>
                            <h5 class="fw-bold mb-1">Current Site Phase</h5>
                            <p class="text-muted mb-0 small">What phase is currently active?</p>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @foreach ($assignedProjects->take(4) as $project)
                            @php($phase = $project->phases->first())
                            <div class="d-flex align-items-center gap-3 p-3 rounded-4 bg-light">
                                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-check2"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $project->project_name }}</div>
                                    <div class="small text-muted">{{ $phase->phase_name ?? 'No active phase' }} • {{ round($project->phases->avg('completion_percentage') ?? 0, 1) }}%</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-5">
            <section class="section-card mb-4">
                <div class="section-card-body">
                    <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom">
                        <div>
                            <h5 class="fw-bold mb-1">Daily Accomplishment Report</h5>
                            <p class="text-muted mb-0 small">Have I completed today&apos;s report?</p>
                        </div>
                    </div>

                    <div class="alert alert-light border-0 mb-3" style="background: #f7fdf8; color: var(--supervisor-primary);">
                        <i class="bi bi-exclamation-triangle me-2"></i>Remember to submit today&apos;s report before 5 PM.
                    </div>

                    <div class="border-bottom py-2">
                        <div class="small text-uppercase text-muted fw-bold mb-1">Today&apos;s Report</div>
                        <div class="fw-semibold">{{ $pendingReports->isNotEmpty() ? 'Pending submission' : 'Ready to submit' }}</div>
                    </div>
                    <div class="border-bottom py-2">
                        <div class="small text-uppercase text-muted fw-bold mb-1">Submission Deadline</div>
                        <div class="fw-semibold">5:00 PM Today</div>
                    </div>
                    <div class="py-2">
                        <div class="small text-uppercase text-muted fw-bold mb-1">Latest Report Date</div>
                        <div class="fw-semibold">{{ $pendingReports->first()?->created_at?->format('M d, Y') ?? now()->format('M d, Y') }}</div>
                    </div>

                    <a href="{{ route('supervisor.reports') }}" class="btn btn-outline-soft w-100 mt-3">View Reports</a>
                </div>
            </section>

            <section class="section-card">
                <div class="section-card-body">
                    <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom">
                        <div>
                            <h5 class="fw-bold mb-1">Site Activity Log</h5>
                            <p class="text-muted mb-0 small">What happened recently?</p>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @forelse ($pendingReports->take(4) as $report)
                            <div class="d-flex gap-3 align-items-start p-2 rounded-3">
                                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $report->project->project_name ?? 'Project report' }}</div>
                                    <div class="small text-muted">{{ $report->created_at->format('M d • h:i A') }}</div>
                                    <div class="small text-muted">{{ $report->phase->phase_name ?? 'Progress update' }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">No recent activity available.</div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection