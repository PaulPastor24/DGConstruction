@extends('layouts.client')

@section('title', 'Client Portal - Project Progress Dashboard')
@section('mobileTitle', 'Dashboard')

@section('content')

    @include('client.partials.page-header', [
        'eyebrow' => 'Client Overview',
        'title' => 'Dashboard',
        'description' => 'Monitor your construction project in real time.',
    ])

<div class="container-fluid p-0">
    @php
        $primaryProject = $primaryProject ?? $projects->first();
        $primaryProjectName = $primaryProjectName ?? optional($primaryProject)->project_name ?? 'No Project Assigned';
        $primaryLocation = trim((string) ($primaryProject?->project_location ?? $primaryProject?->location ?? $primaryProject?->location_address ?? ''));
        $nextMilestone = optional($upcomingMilestones)->first();
        $currentPhaseName = optional($currentPhases->first())->phase_name ?? 'Phase pending';
        $nextMilestoneName = optional($nextMilestone)->milestone_name ?? 'Milestone pending';
        $overviewSummary = "Project: {$primaryProjectName}. Progress: {$stats['overall_completion']}%. Current phase: {$currentPhaseName}. Next milestone: {$nextMilestoneName}.";
    @endphp
    
    <div class="hero-card mb-4"
         id="currentProjectCard"
         data-carousel-projects='@json($carouselProjects)'
         data-carousel-index="{{ $primaryProjectIndex }}"
         data-snapshot-url-template="{{ url('/client/dashboard/project/__ID__/snapshot') }}">
        <div class="row align-items-center g-0">
            <div class="col-md-7 p-3 p-lg-4 hero-content">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge-project-status">CURRENT PROJECT</span>
                    <span class="project-status-pill {{ $stats['delayed_milestones_count'] > 0 ? 'status-delayed' : 'status-on-track' }}" id="heroStatusPill">{{ $stats['delayed_milestones_count'] > 0 ? 'Delayed' : 'On Track' }}</span>
                </div>

                <div class="project-title-carousel"
                     id="projectTitleCarousel"
                     role="group"
                     aria-roledescription="carousel"
                     aria-label="Current project selector"
                     tabindex="0">
                    <button type="button" class="carousel-arrow-btn carousel-arrow-prev" id="carouselArrowPrev" aria-label="View previous project">
                        <i class="bi bi-chevron-left"></i>
                    </button>

                    <div class="carousel-track" id="carouselTrack">
                        <button type="button" class="carousel-slide carousel-slide-prev" id="carouselSlidePrev" aria-label="Switch to previous project"></button>
                        <h1 class="carousel-slide carousel-slide-current project-title-text" id="carouselSlideCurrent" aria-live="polite">{{ $primaryProject?->project_name ?? '' }}</h1>
                        <button type="button" class="carousel-slide carousel-slide-next" id="carouselSlideNext" aria-label="Switch to next project"></button>
                    </div>

                    <button type="button" class="carousel-arrow-btn carousel-arrow-next" id="carouselArrowNext" aria-label="View next project">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div class="carousel-dots" id="carouselDots" aria-hidden="true"></div>

                <p class="project-subtitle-text text-muted mb-2" id="heroLocationText">{{ $primaryLocation !== '' ? $primaryLocation : 'Location Pending' }}</p>

                <div class="row mt-2 g-2">
                    <div class="col-6 col-sm-3">
                        <div class="meta-label"><i class="bi bi-calendar-check me-1"></i> Project Start</div>
                        <div class="meta-value" id="heroStartDate">{{ $primaryProject?->start_date?->format('M d, Y') ?? 'TBD' }}</div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="meta-label"><i class="bi bi-calendar-x me-1"></i> Est. Completion</div>
                        <div class="meta-value" id="heroEndDate">{{ $primaryProject?->target_end_date?->format('M d, Y') ?? 'TBD' }}</div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="meta-label"><i class="bi bi-person-workspace me-1"></i> Project Manager</div>
                        <div class="meta-value" id="heroManager">{{ $primaryProject?->engineer?->name ?? 'Unassigned' }}</div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="meta-label"><i class="bi bi-building-gear me-1"></i> Site Supervisor</div>
                        <div class="meta-value" id="heroSupervisor">{{ $primaryProject?->activeSupervisor?->name ?? 'Not assigned' }}</div>
                    </div>
                </div>

                <div class="project-progress-embedded mt-3">
                    <div class="project-progress-header">
                        <span class="project-progress-label">Overall Completion</span>
                        <span class="project-progress-value" id="heroProgressValue">{{ $stats['overall_completion'] }}%</span>
                    </div>
                    <div class="project-progress-track" aria-label="Project completion progress bar">
                        <span id="heroProgressBar" style="width: {{ $stats['overall_completion'] }}%"></span>
                    </div>
                    <div class="project-progress-meta">
                        <span><i class="bi bi-flag-fill me-1"></i><span id="heroPhaseText">{{ $currentPhaseName }}</span></span>
                        <span><i class="bi bi-calendar2-week me-1"></i><span id="heroMilestoneDate">{{ optional($nextMilestone)->start_date?->format('M d, Y') ?? 'Pending' }}</span></span>
                    </div>
                </div>
            </div>
            <div class="col-md-5 d-none d-md-flex structural-img-container align-items-center justify-content-center">
                <div class="hero-image-wrap">
                    <img id="heroProjectImage"
                         src="{{ $primaryProject?->image_url ?? 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=1600&q=80' }}"
                         alt="{{ $primaryProject?->project_name ?? 'Project' }} image"
                         class="hero-structural-image"
                         onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=1600&q=80';">
                    <div class="hero-image-overlay"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4" id="dashboardMetricsRow">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-status-card">
                <div class="metric-icon-box bg-mint-light"><i class="bi bi-building-gear text-success"></i></div>
                <div>
                    <div class="metric-title">Current Phase</div>
                    <div class="metric-main-val text-success" id="metricCurrentPhase" style="font-size: 1.25rem; font-weight:700; margin: 0.3rem 0;">
                        {{ optional($currentPhases->first())->phase_name ?? 'Structural Works' }}
                    </div>
                    <div class="metric-sub-text">In progress</div>
                </div>
            </div>
        </div>
        @php
            $scheduleHealth = $stats['delayed_milestones_count'] > 0 ? 'At Risk' : 'On Track';
            $scheduleHealthClass = $stats['delayed_milestones_count'] > 0 ? 'text-warning' : 'text-success';
            $scheduleHealthNote = $stats['delayed_milestones_count'] > 0 ? 'Delayed milestones detected' : 'No major delays';
        @endphp
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-status-card">
                <div class="metric-icon-box bg-mint-light"><i class="bi bi-shield-check {{ $scheduleHealthClass }}" id="metricScheduleHealthIcon"></i></div>
                <div>
                    <div class="metric-title">Schedule Health</div>
                    <span class="metric-status-pill {{ $scheduleHealth === 'At Risk' ? 'status-delayed' : 'status-on-track' }}" id="metricScheduleHealthPill">{{ $scheduleHealth }}</span>
                    <div class="metric-sub-text" id="metricScheduleHealthNote">{{ $scheduleHealthNote }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-status-card">
                <div class="metric-icon-box bg-gray-light"><i class="bi bi-flag-fill text-success"></i></div>
                <div>
                    <div class="metric-title">Next Milestone</div>
                    <div class="metric-main-val text-success" id="metricNextMilestoneName" style="font-size: 1.05rem; font-weight:700; line-height:1.2; margin:0.25rem 0;">
                        {{ optional($nextMilestone)->milestone_name ?? 'Next milestone pending' }}
                    </div>
                    <div class="metric-sub-text text-dark fw-semibold" id="metricNextMilestoneDate">{{ optional($nextMilestone)->start_date?->format('M d, Y') ?? 'Pending' }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-status-card">
                <div class="metric-icon-box bg-gray-light"><i class="bi bi-file-earmark-check text-success"></i></div>
                <div>
                    <div class="metric-title">Latest Report Status</div>
                    <div class="metric-main-val text-success" id="metricLatestReportStatus" style="font-size: 1.25rem; font-weight:700; margin: 0.3rem 0;">
                        {{ $recentReports->first()?->approval_status ?? 'Pending' }}
                    </div>
                    <div class="metric-sub-text" id="metricLatestReportNote">{{ $recentReports->count() > 0 ? 'Last uploaded report' : 'No report submitted' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-md-7 col-xl-8">
            <div class="dashboard-ui-panel">
                <div class="ui-panel-head d-flex justify-content-between align-items-center">
                    <h5 class="ui-panel-title">Recent Reports</h5>
                    <a href="{{ route('client.reports') }}" class="view-all-link">View All</a>
                </div>
                <div class="ui-panel-body p-0">
                    <div class="report-list-group" id="recentReportsList">
                        @forelse($recentReports as $report)
                            <div class="report-item-row">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="file-icon-frame"><i class="bi bi-file-earmark-text"></i></div>
                                    <div>
                                        <h6>{{ optional($report->phase)->phase_name ?? 'Project Update' }}</h6>
                                        <p>{{ Str::limit($report->report_text ?? 'Report available', 48) }} &bull; {{ optional($report->report_date)->format('M d, Y') ?? 'Unknown' }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('client.reports.downloadPdf', $report->report_id) }}" class="download-icon-btn" title="Download report"><i class="bi bi-download"></i></a>
                            </div>
                        @empty
                            <div class="text-center p-4 text-muted">
                                <div class="mb-2 fs-3"><i class="bi bi-folder-x"></i></div>
                                <p class="m-0">No recent reports are available for this client profile.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-5 col-xl-4">
            <div class="dashboard-ui-panel">
                <div class="ui-panel-head d-flex justify-content-between align-items-center">
                    <h5 class="ui-panel-title">Recent Activity</h5>
                    <a href="{{ route('client.reports') }}" class="view-all-link">View All</a>
                </div>
                <div class="ui-panel-body">
                    <div class="activity-timeline-container" id="recentActivityList">
                        @forelse($activityItems as $item)
                            <div class="activity-timeline-node">
                                <div class="node-icon {{ $item['variant'] }}">
                                    <i class="{{ $item['icon'] }}"></i>
                                </div>
                                <div class="node-content">
                                    <div class="d-flex justify-content-between align-items-baseline gap-2">
                                        <h6 class="activity-node-title mb-1">{{ $item['title'] }}</h6>
                                        <span class="node-time flex-shrink-0">{{ $item['time'] }}</span>
                                    </div>
                                    <p class="activity-node-desc mb-0">{{ $item['subtitle'] }} &bull; <span class="text-dark fw-medium">{{ $item['author'] }}</span></p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center p-4 text-muted">
                                <div class="mb-2 fs-3"><i class="bi bi-clock-history"></i></div>
                                <p class="m-0">No recent activities are available yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function initClientDashboardCarousel() {
        // --- Current Project card: interactive title carousel ---
        // Replaces the old dropdown project selector. Switching projects updates the
        // hero card instantly from locally embedded data, then fetches the real
        // per-project numbers from the server and refreshes the metric cards, Recent
        // Reports, and Recent Activity panels below it - all without a page reload.
        const card = document.getElementById('currentProjectCard');
        if (!card) {
            return;
        }

        let projects = [];
        try {
            projects = JSON.parse(card.dataset.carouselProjects || '[]');
        } catch (err) {
            projects = [];
        }

        if (!Array.isArray(projects) || projects.length === 0) {
            return;
        }

        let currentIndex = parseInt(card.dataset.carouselIndex, 10);
        if (Number.isNaN(currentIndex) || currentIndex < 0 || currentIndex >= projects.length) {
            currentIndex = 0;
        }

        // Restore the project the client last viewed (if any) before the first paint so
        // navigating back to the dashboard keeps their selection instead of the latest.
        restorePersistedSelection();

        const snapshotUrlTemplate = card.dataset.snapshotUrlTemplate || '';

        const carousel = document.getElementById('projectTitleCarousel');
        const track = document.getElementById('carouselTrack');
        const slidePrev = document.getElementById('carouselSlidePrev');
        const slideCurrent = document.getElementById('carouselSlideCurrent');
        const slideNext = document.getElementById('carouselSlideNext');
        const arrowPrev = document.getElementById('carouselArrowPrev');
        const arrowNext = document.getElementById('carouselArrowNext');
        const dotsWrap = document.getElementById('carouselDots');

        const heroStatusPill = document.getElementById('heroStatusPill');
        const heroLocationText = document.getElementById('heroLocationText');
        const heroStartDate = document.getElementById('heroStartDate');
        const heroEndDate = document.getElementById('heroEndDate');
        const heroManager = document.getElementById('heroManager');
        const heroSupervisor = document.getElementById('heroSupervisor');
        const heroProjectImage = document.getElementById('heroProjectImage');
        const heroProgressValue = document.getElementById('heroProgressValue');
        const heroProgressBar = document.getElementById('heroProgressBar');
        const heroPhaseText = document.getElementById('heroPhaseText');
        const heroMilestoneDate = document.getElementById('heroMilestoneDate');

        const metricsRow = document.getElementById('dashboardMetricsRow');
        const metricCurrentPhase = document.getElementById('metricCurrentPhase');
        const metricScheduleIcon = document.getElementById('metricScheduleHealthIcon');
        const metricSchedulePill = document.getElementById('metricScheduleHealthPill');
        const metricScheduleNote = document.getElementById('metricScheduleHealthNote');
        const metricNextMilestoneName = document.getElementById('metricNextMilestoneName');
        const metricNextMilestoneDate = document.getElementById('metricNextMilestoneDate');
        const metricLatestReportStatus = document.getElementById('metricLatestReportStatus');
        const metricLatestReportNote = document.getElementById('metricLatestReportNote');
        const recentReportsList = document.getElementById('recentReportsList');
        const recentActivityList = document.getElementById('recentActivityList');

        function wrapIndex(index) {
            return (index + projects.length) % projects.length;
        }

        function buildDots() {
            if (!dotsWrap) return;
            dotsWrap.innerHTML = '';
            if (projects.length <= 1) return;

            projects.forEach(function (project, index) {
                const dot = document.createElement('span');
                dot.className = 'carousel-dot' + (index === currentIndex ? ' is-active' : '');
                dotsWrap.appendChild(dot);
            });
        }

        function renderHeroDetails(project) {
            heroStatusPill.textContent = project.status_label;
            heroStatusPill.classList.remove('status-on-track', 'status-delayed');
            heroStatusPill.classList.add(project.status_class);

            heroLocationText.textContent = project.location;
            heroStartDate.textContent = project.start_date;
            heroEndDate.textContent = project.target_end_date;
            heroManager.textContent = project.manager;
            heroSupervisor.textContent = project.supervisor;
            heroProgressValue.textContent = project.progress + '%';
            heroProgressBar.style.width = Math.max(0, Math.min(100, project.progress)) + '%';
            heroPhaseText.textContent = project.phase;
            heroMilestoneDate.textContent = project.next_milestone_date;

            // Per-project cover image - swap instantly when the selected project changes.
            if (heroProjectImage) {
                const fallback = 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=1600&q=80';
                heroProjectImage.src = project.image || fallback;
                heroProjectImage.alt = (project.name || 'Project') + ' image';
            }
        }

        function renderMetrics(stats) {
            if (!stats) return;

            if (metricCurrentPhase) metricCurrentPhase.textContent = stats.current_phase;

            if (metricSchedulePill) {
                metricSchedulePill.textContent = stats.schedule_health_label;
                metricSchedulePill.classList.remove('status-on-track', 'status-delayed');
                metricSchedulePill.classList.add(stats.schedule_health_pill_class);
            }
            if (metricScheduleIcon) {
                metricScheduleIcon.classList.remove('text-success', 'text-warning');
                metricScheduleIcon.classList.add(stats.schedule_health_at_risk ? 'text-warning' : 'text-success');
            }
            if (metricScheduleNote) metricScheduleNote.textContent = stats.schedule_health_note;

            if (metricNextMilestoneName) metricNextMilestoneName.textContent = stats.next_milestone_name;
            if (metricNextMilestoneDate) metricNextMilestoneDate.textContent = stats.next_milestone_date;

            if (metricLatestReportStatus) metricLatestReportStatus.textContent = stats.latest_report_status;
            if (metricLatestReportNote) metricLatestReportNote.textContent = stats.latest_report_note;
        }

        function renderReports(reports) {
            if (!recentReportsList) return;
            recentReportsList.innerHTML = '';

            if (!reports || reports.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'text-center p-4 text-muted';
                empty.innerHTML = '<div class="mb-2 fs-3"><i class="bi bi-folder-x"></i></div><p class="m-0">No recent reports are available for this project.</p>';
                recentReportsList.appendChild(empty);
                return;
            }

            reports.forEach(function (report) {
                const row = document.createElement('div');
                row.className = 'report-item-row';

                const left = document.createElement('div');
                left.className = 'd-flex align-items-start gap-3';
                left.innerHTML = '<div class="file-icon-frame"><i class="bi bi-file-earmark-text"></i></div>';

                const textWrap = document.createElement('div');
                const heading = document.createElement('h6');
                heading.textContent = report.title;
                const desc = document.createElement('p');
                desc.textContent = report.subtitle + ' \u2022 ' + report.date;
                textWrap.appendChild(heading);
                textWrap.appendChild(desc);
                left.appendChild(textWrap);

                const link = document.createElement('a');
                link.className = 'download-icon-btn';
                link.href = report.download_url;
                link.innerHTML = '<i class="bi bi-download"></i>';

                row.appendChild(left);
                row.appendChild(link);
                recentReportsList.appendChild(row);
            });
        }

        function renderActivity(items) {
            if (!recentActivityList) return;
            recentActivityList.innerHTML = '';

            if (!items || items.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'text-center p-4 text-muted';
                empty.innerHTML = '<div class="mb-2 fs-3"><i class="bi bi-clock-history"></i></div><p class="m-0">No recent activities are available yet.</p>';
                recentActivityList.appendChild(empty);
                return;
            }

            items.forEach(function (item) {
                const node = document.createElement('div');
                node.className = 'activity-timeline-node';

                const iconBox = document.createElement('div');
                iconBox.className = 'node-icon ' + item.variant;
                iconBox.innerHTML = '<i class="' + item.icon + '"></i>';

                const content = document.createElement('div');
                content.className = 'node-content';

                const headRow = document.createElement('div');
                headRow.className = 'd-flex justify-content-between align-items-baseline gap-2';
                const title = document.createElement('h6');
                title.className = 'activity-node-title mb-1';
                title.textContent = item.title;
                const time = document.createElement('span');
                time.className = 'node-time flex-shrink-0';
                time.textContent = item.time;
                headRow.appendChild(title);
                headRow.appendChild(time);

                const desc = document.createElement('p');
                desc.className = 'activity-node-desc mb-0';
                desc.textContent = item.subtitle + ' \u2022 ' + item.author;

                content.appendChild(headRow);
                content.appendChild(desc);

                node.appendChild(iconBox);
                node.appendChild(content);
                recentActivityList.appendChild(node);
            });
        }

        function setLoadingState(isLoading) {
            [metricsRow, recentReportsList, recentActivityList].forEach(function (el) {
                if (!el) return;
                el.classList.toggle('is-loading-snapshot', isLoading);
            });
        }

        let abortController = null;
        let requestToken = 0;

        function fetchProjectSnapshot(project, skipLoading) {
            if (!snapshotUrlTemplate) {
                return;
            }

            if (abortController) {
                abortController.abort();
            }
            abortController = new AbortController();
            const myToken = ++requestToken;

            // When we already have the project's data embedded in the page payload we
            // render it instantly (see renderSlides) and only reconcile in the
            // background, so we skip the dimming "loading" state to avoid flicker.
            if (!skipLoading) {
                setLoadingState(true);
            }

            const url = snapshotUrlTemplate.replace('__ID__', encodeURIComponent(project.id));

            fetch(url, {
                headers: { 'Accept': 'application/json' },
                signal: abortController.signal,
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Snapshot request failed with status ' + response.status);
                    }
                    return response.json();
                })
                .then(function (data) {
                    if (myToken !== requestToken) {
                        // A newer switch happened while this request was in flight - ignore stale data.
                        return;
                    }
                    if (data.hero) renderHeroDetails(data.hero);
                    renderMetrics(data.stats);
                    renderReports(data.reports);
                    renderActivity(data.activity);
                })
                .catch(function (err) {
                    if (err.name !== 'AbortError') {
                        console.error('Could not load live data for this project:', err);
                    }
                })
                .finally(function () {
                    if (myToken === requestToken && !skipLoading) {
                        setLoadingState(false);
                    }
                });
        }

        // Keep the selected project reflected in the URL (so the layout's 15s silent
        // reload re-renders the same project) and in localStorage (so returning to the
        // dashboard from another page restores the exact project the client last viewed).
        const SELECTION_STORAGE_KEY = 'client_dashboard_project';

        // Server endpoint used to mirror the selected project into the session so the
        // choice survives full page navigations between Client pages (Reports, etc.).
        const SELECT_PROJECT_URL_TEMPLATE = "{{ route('client.dashboard.project.select', ['project' => '__ID__']) }}";
        const SELECT_PROJECT_CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        function persistSelection(projectId) {
            try {
                const url = new URL(window.location.href);
                url.searchParams.set('project_id', projectId);
                window.history.replaceState({}, '', url.toString());
            } catch (err) {
                // Non-fatal - the URL simply may not update.
            }
            try {
                localStorage.setItem(SELECTION_STORAGE_KEY, String(projectId));
            } catch (err) {
                // Non-fatal - selection still survives via URL/session.
            }
        }

        // Keep the session (and the sidebar navigation links) in sync with the active
        // project so returning to the Dashboard or jumping to Reports preserves the
        // same project without relying on a browser refresh or localStorage alone.
        function persistProjectSelection(projectId) {
            try {
                const links = document.querySelectorAll('.sidebar .nav-item');
                links.forEach(function (link) {
                    try {
                        const href = link.getAttribute('href');
                        if (!href || href.startsWith('#')) return;
                        const url = new URL(href, window.location.origin);
                        url.searchParams.set('project_id', projectId);
                        link.href = url.pathname + url.search + url.hash;
                    } catch (err) {
                        // Skip links we cannot parse safely.
                    }
                });
            } catch (err) {
                // Non-fatal.
            }

            const url = SELECT_PROJECT_URL_TEMPLATE.replace('__ID__', encodeURIComponent(projectId));

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': SELECT_PROJECT_CSRF,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).catch(function () {
                // Non-fatal - selection still survives via URL/localStorage.
            });
        }

        function restorePersistedSelection() {
            let savedId = null;
            try {
                savedId = localStorage.getItem(SELECTION_STORAGE_KEY);
            } catch (err) {
                return;
            }
            if (savedId === null) {
                return;
            }
            const idx = projects.findIndex(function (p) {
                return String(p.id) === String(savedId);
            });
            if (idx >= 0) {
                currentIndex = idx;
            }
        }

        function renderSlides() {
            const prevProject = projects[wrapIndex(currentIndex - 1)];
            const currentProject = projects[currentIndex];
            const nextProject = projects[wrapIndex(currentIndex + 1)];

            slidePrev.textContent = prevProject.name;
            slideCurrent.textContent = currentProject.name;
            slideNext.textContent = nextProject.name;

            // Mirror the active project into the URL for silent-reload consistency.
            if (currentProject && currentProject.id !== null && currentProject.id !== undefined) {
                persistSelection(currentProject.id);
            }

            const singleProject = projects.length <= 1;
            slidePrev.style.visibility = singleProject ? 'hidden' : 'visible';
            slideNext.style.visibility = singleProject ? 'hidden' : 'visible';
            slidePrev.disabled = singleProject;
            slideNext.disabled = singleProject;
            arrowPrev.disabled = singleProject;
            arrowNext.disabled = singleProject;

            slideCurrent.setAttribute('aria-label', 'Current project: ' + currentProject.name);

            // The full per-project snapshot (hero, stats, reports, activity) is embedded
            // in the page payload, so we paint everything instantly for a real-time feel...
            if (currentProject.snapshot) {
                if (currentProject.snapshot.hero) renderHeroDetails(currentProject.snapshot.hero);
                renderMetrics(currentProject.snapshot.stats);
                renderReports(currentProject.snapshot.reports);
                renderActivity(currentProject.snapshot.activity);
            } else {
                renderHeroDetails(currentProject);
            }
            buildDots();

            // ...then reconcile with live server data in the background (no loading flash).
            fetchProjectSnapshot(currentProject, !!currentProject.snapshot);

            // Mirror the active project to the session + sidebar links so the choice
            // stays selected after navigating to other Client pages and returning.
            persistProjectSelection(currentProject.id);
        }

        function goTo(index, direction) {
            if (projects.length <= 1) return;
            currentIndex = wrapIndex(index);

            track.classList.remove('slide-to-left', 'slide-to-right');
            // Force reflow so the animation restarts even on rapid clicks.
            void track.offsetWidth;
            track.classList.add(direction === 'next' ? 'slide-to-left' : 'slide-to-right');

            renderSlides();
        }

        function goPrev() { goTo(currentIndex - 1, 'prev'); }
        function goNext() { goTo(currentIndex + 1, 'next'); }

        arrowPrev.addEventListener('click', goPrev);
        arrowNext.addEventListener('click', goNext);
        slidePrev.addEventListener('click', goPrev);
        slideNext.addEventListener('click', goNext);

        carousel.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                goPrev();
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                goNext();
            }
        });

        // --- Touch swipe support ---
        // Tracks the finger for the whole gesture (not just start/end) so the slide can
        // follow the drag, and explicitly prevents the browser's ~300ms "ghost click"
        // that otherwise lands on whichever prev/next button is under the finger right
        // after touchend - that ghost click was immediately re-triggering navigation and
        // making the swipe you just made look like it "undid itself".
        let touchStartX = null;
        let touchStartY = null;
        let touchDeltaX = 0;
        let isDragging = false;
        let axisLocked = null;
        let suppressClicksUntil = 0;

        track.addEventListener('touchstart', function (event) {
            touchStartX = event.changedTouches[0].clientX;
            touchStartY = event.changedTouches[0].clientY;
            touchDeltaX = 0;
            isDragging = true;
            axisLocked = null;
            track.classList.add('is-dragging');
        }, { passive: true });

        track.addEventListener('touchmove', function (event) {
            if (!isDragging || touchStartX === null) return;

            const touch = event.changedTouches[0];
            const deltaX = touch.clientX - touchStartX;
            const deltaY = touch.clientY - touchStartY;

            if (axisLocked === null && (Math.abs(deltaX) > 6 || Math.abs(deltaY) > 6)) {
                axisLocked = Math.abs(deltaX) > Math.abs(deltaY) ? 'x' : 'y';
            }

            if (axisLocked === 'x') {
                // We own this gesture now - stop the page from scrolling and follow the finger.
                event.preventDefault();
                touchDeltaX = deltaX;
                slideCurrent.style.transform = 'translateX(' + (deltaX * 0.6) + 'px)';
            }
        }, { passive: false });

        function endTouch(event) {
            if (!isDragging) return;
            isDragging = false;
            track.classList.remove('is-dragging');
            slideCurrent.style.transform = '';

            const swipeThreshold = 40;
            const finalDeltaX = touchDeltaX;
            const wasHorizontal = axisLocked === 'x';

            touchStartX = null;
            touchStartY = null;
            touchDeltaX = 0;
            axisLocked = null;

            if (wasHorizontal && Math.abs(finalDeltaX) > swipeThreshold) {
                if (event.cancelable) {
                    event.preventDefault();
                }
                // Swallow the browser's compatibility click for a moment so it can't
                // immediately re-trigger navigation on the button under the finger.
                suppressClicksUntil = Date.now() + 400;

                if (finalDeltaX > 0) {
                    goPrev();
                } else {
                    goNext();
                }
            }
        }

        track.addEventListener('touchend', endTouch, { passive: false });
        track.addEventListener('touchcancel', function () {
            isDragging = false;
            axisLocked = null;
            touchStartX = null;
            touchStartY = null;
            touchDeltaX = 0;
            track.classList.remove('is-dragging');
            slideCurrent.style.transform = '';
        }, { passive: true });

        // Belt-and-suspenders guard against the ghost click, run in the capture phase so
        // it fires before the prev/next buttons' own click handlers.
        track.addEventListener('click', function (event) {
            if (Date.now() < suppressClicksUntil) {
                event.stopPropagation();
                event.preventDefault();
            }
        }, true);

        renderSlides();
    }

    window.initClientDashboardCarousel = initClientDashboardCarousel;
    document.addEventListener('DOMContentLoaded', initClientDashboardCarousel);
    // The layout replaces #silentReloadContent via innerHTML (which does NOT re-run
    // inline <script> tags). Re-run the carousel init after each reload so the project
    // switcher stays alive and the active project name never goes blank.
    document.addEventListener('silentReloadComplete', initClientDashboardCarousel);
</script>


<div class="modal fade" id="projectOverviewModal" tabindex="-1" aria-labelledby="projectOverviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-bold" id="projectOverviewModalLabel">{{ optional($primaryProject)->project_name ?? 'Project details' }}</h5>
                    <p class="text-muted small mb-0">Live project summary pulled from the database</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <div class="border rounded-3 p-3">
                            <div class="text-muted small text-uppercase">Overall progress</div>
                            <div class="fw-bold fs-4 text-success">{{ $stats['overall_completion'] }}%</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="border rounded-3 p-3">
                            <div class="text-muted small text-uppercase">Current phase</div>
                            <div class="fw-bold">{{ $currentPhaseName }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="border rounded-3 p-3">
                            <div class="text-muted small text-uppercase">Next milestone</div>
                            <div class="fw-bold">{{ $nextMilestoneName }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="border rounded-3 p-3">
                            <div class="text-muted small text-uppercase">Assigned manager</div>
                            <div class="fw-bold">{{ optional(optional($primaryProject)->engineer)->name ?? 'Unassigned' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="{{ route('client.timeline') }}" class="project-command-button-primary">View Timeline</a>
            </div>
        </div>
    </div>
</div>

<style>
    /* --- HERO CONTAINER ACCENTING --- */
    .dashboard-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.15rem 0 0.8rem;
        margin-bottom: 0.2rem;
    }
    .dashboard-page-heading {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }
    .dashboard-page-eyebrow {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #64748b;
    }
    .dashboard-page-title {
        font-family: 'DM Sans', sans-serif;
        font-size: 1.7rem;
        font-weight: 700;
        line-height: 1.15;
        margin: 0;
        color: #2a4028;
    }
    .dashboard-page-description {
        margin: 0.2rem 0 0;
        font-size: 0.92rem;
        font-weight: 500;
        color: #64748b;
        max-width: 420px;
    }
    .dashboard-page-tools {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-shrink: 0;
    }
    @media (max-width: 1024px) {
        .dashboard-page-header {
            padding: 0.35rem 0 0.75rem;
            align-items: flex-start;
            flex-direction: column;
        }
        .dashboard-page-tools {
            gap: 0.5rem;
            width: 100%;
            justify-content: space-between;
            flex-wrap: wrap;
        }
    }
    @media (max-width: 576px) {
        .dashboard-page-title {
            font-size: 1.45rem;
        }
        .dashboard-page-description {
            font-size: 0.85rem;
        }
    }
    .dashboard-notification-button {
        position: relative;
        width: 46px;
        height: 46px;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 8px 16px rgba(42, 64, 40, 0.08);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #334155;
        transition: all 0.2s ease, transform 0.2s ease;
    }
    .dashboard-notification-button:hover {
        background: #f8fafc;
        transform: translateY(-1px);
    }
    .dashboard-notification-button.notification-bell-animate {
        animation: bell-ring 1.2s ease-in-out infinite, pulse-soft 1.45s ease-out infinite;
        transform-origin: center top;
        color: #22c55e;
        background: #f0fdf4;
        border-color: #22c55e;
        position: relative;
    }
    .dashboard-notification-button.notification-bell-animate::before {
        content: '';
        position: absolute;
        inset: -3px;
        border-radius: 999px;
        border: 2px solid rgba(34, 197, 94, 0.28);
        animation: ring-pulse 1.45s ease-out infinite;
        pointer-events: none;
    }
    .dashboard-notification-button.notification-bell-animate .bi-bell {
        color: #22c55e;
        z-index: 1;
    }
    @keyframes bell-ring {
        0%, 100% { transform: rotate(0deg); }
        10% { transform: rotate(12deg); }
        20% { transform: rotate(-10deg); }
        30% { transform: rotate(8deg); }
        40% { transform: rotate(-6deg); }
        50% { transform: rotate(4deg); }
        60% { transform: rotate(-2deg); }
        70% { transform: rotate(2deg); }
        80%, 90% { transform: rotate(0deg); }
    }
    .notification-badge {
        position: absolute;
        top: 4px;
        right: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        padding: 0 0.25rem;
        border: 2px solid #ffffff;
        border-radius: 999px;
        background: #2a4028;
        color: #ffffff;
        font-size: 0.68rem;
        font-weight: 700;
        line-height: 1;
    }
    @keyframes pulse-soft {
        0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.24); }
        70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    @keyframes ring-pulse {
        0% { transform: scale(0.92); opacity: 0.9; }
        70% { transform: scale(1.12); opacity: 0; }
        100% { transform: scale(1.16); opacity: 0; }
    }
    .hero-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid var(--border-color);
        overflow: hidden;
        min-height: auto;
        box-shadow: 0 6px 24px rgba(15, 23, 42, 0.04);
        margin-bottom: 1.4rem;
    }
    .hero-card .row {
        min-height: auto;
    }
    .hero-card .col-md-7 {
        padding-top: 1.25rem;
        padding-bottom: 1.25rem;
    }
    .badge-project-status {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        color: #16a34a;
        background-color: #f0fdf4;
        padding: 0.25rem 0.6rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
    }
    .project-status-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.28rem 0.62rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .project-status-pill.status-on-track {
        background: #ecfdf3;
        color: #166534;
    }
    .project-status-pill.status-delayed {
        background: #fffbeb;
        color: #b45309;
    }
    .project-title-text {
        font-family: 'DM Sans', sans-serif;
        font-size: 1.7rem;
        font-weight: 700;
        color: #2a4028;
        margin-bottom: 0.35rem;
        line-height: 1.15;
        letter-spacing: -0.01em;
    }
    .project-subtitle-text {
        font-size: 0.9rem;
        margin-bottom: 0.9rem;
        max-width: 640px;
        line-height: 1.5;
    }
    .project-progress-embedded {
        background: linear-gradient(180deg, #f8fffb 0%, #f8fafc 100%);
        border: 1px solid #e2f8ea;
        border-radius: 18px;
        padding: 1rem;
    }
    .project-progress-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.55rem;
    }
    .project-progress-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #64748b;
    }
    .project-progress-value {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
    }
    .project-progress-track {
        height: 10px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }
    .project-progress-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%);
    }
    .project-progress-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-top: 0.65rem;
        font-size: 0.74rem;
        font-weight: 600;
        color: #475569;
    }
    .project-progress-meta span {
        display: inline-flex;
        align-items: center;
    }
    .hero-content { position: relative; z-index: 3; }

    /* --- CURRENT PROJECT TITLE CAROUSEL --- */
    .project-title-carousel {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin: 0.15rem 0 0.35rem;
        border-radius: 14px;
        outline: none;
    }
    .project-title-carousel:focus-visible {
        outline: 2px solid #16a34a;
        outline-offset: 6px;
    }
    .carousel-arrow-btn {
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 1px solid #dbe4dd;
        background: #ffffff;
        color: #2a4028;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
        transition: all 0.2s ease;
        cursor: pointer;
        padding: 0;
    }
    .carousel-arrow-btn:hover:not(:disabled),
    .carousel-arrow-btn:focus-visible:not(:disabled) {
        border-color: #2E7D32;
        background: #f0fdf4;
        color: #16a34a;
        transform: scale(1.08);
    }
    .carousel-arrow-btn:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }
    .carousel-track {
        position: relative;
        flex: 1 1 auto;
        min-width: 0;
        display: flex;
        align-items: baseline;
        justify-content: center;
        gap: 0.65rem;
        overflow: hidden;
        padding: 0.2rem 0;
        touch-action: pan-y;
    }
    .carousel-slide {
        border: none;
        background: none;
        padding: 0;
        margin: 0;
        font-family: 'DM Sans', sans-serif;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.3s ease, color 0.2s ease;
    }
    button.carousel-slide {
        cursor: pointer;
    }
    button.carousel-slide:disabled {
        cursor: default;
    }
    .carousel-slide-prev,
    .carousel-slide-next {
        font-size: 0.95rem;
        font-weight: 600;
        color: #94a3b8;
        opacity: 0.55;
        transform: scale(0.86);
        max-width: 30%;
        flex-shrink: 1;
    }
    .carousel-slide-prev:hover:not(:disabled),
    .carousel-slide-next:hover:not(:disabled),
    .carousel-slide-prev:focus-visible,
    .carousel-slide-next:focus-visible {
        opacity: 0.9;
        color: #2E7D32;
    }
    .carousel-slide-current {
        font-size: 1.7rem;
        font-weight: 700;
        color: #2a4028;
        letter-spacing: -0.01em;
        line-height: 1.15;
        max-width: 44%;
        flex-shrink: 0;
        text-align: center;
        cursor: default;
    }
    .carousel-track.slide-to-left .carousel-slide-current {
        animation: carousel-current-in-left 0.32s ease;
    }
    .carousel-track.slide-to-right .carousel-slide-current {
        animation: carousel-current-in-right 0.32s ease;
    }
    .carousel-track.slide-to-left .carousel-slide-prev,
    .carousel-track.slide-to-left .carousel-slide-next,
    .carousel-track.slide-to-right .carousel-slide-prev,
    .carousel-track.slide-to-right .carousel-slide-next {
        animation: carousel-side-fade-in 0.32s ease;
    }
    @keyframes carousel-current-in-left {
        0% { transform: translateX(26px) scale(0.9); opacity: 0; }
        100% { transform: translateX(0) scale(1); opacity: 1; }
    }
    @keyframes carousel-current-in-right {
        0% { transform: translateX(-26px) scale(0.9); opacity: 0; }
        100% { transform: translateX(0) scale(1); opacity: 1; }
    }
    @keyframes carousel-side-fade-in {
        0% { opacity: 0; transform: scale(0.75); }
        100% { opacity: 0.55; transform: scale(0.86); }
    }
    .carousel-dots {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        margin: 0 0 0.5rem 2.6rem;
        min-height: 6px;
    }
    .carousel-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: #d8e2dc;
        transition: all 0.2s ease;
    }
    .carousel-dot.is-active {
        width: 16px;
        border-radius: 3px;
        background: #2E7D32;
    }
    @media (max-width: 576px) {
        .carousel-slide-current { font-size: 1.15rem; max-width: 60%; }
        .carousel-slide-prev, .carousel-slide-next { font-size: 0.72rem; max-width: 18%; }
        .carousel-arrow-btn { width: 26px; height: 26px; font-size: 0.7rem; }
        .carousel-dots { margin-left: 1.8rem; }
    }

    .carousel-track.is-dragging .carousel-slide-current {
        transition: none;
    }
    .is-loading-snapshot {
        opacity: 0.45;
        pointer-events: none;
        transition: opacity 0.15s ease;
    }

    /* Hero CTA buttons */
    .hero-ctas { margin-top: 1rem; display:flex; gap:0.5rem; flex-wrap:wrap; }
    .hero-ctas .btn { padding: 0.6rem 1rem; border-radius: 10px; font-weight:700; }

    /* Hero image wrap + overlay (slide look) */
    .hero-image-wrap { position: relative; width: 100%; height: 100%; overflow: hidden; }
    .hero-image-overlay { position: absolute; inset: 0; background: linear-gradient(90deg, rgba(2,6,23,0.55) 0%, rgba(2,6,23,0.12) 40%, rgba(2,6,23,0.0) 100%); pointer-events: none; }
    .hero-structural-image { width: 130%; height: 100%; object-fit: cover; transform: translateX(10%); display:block; }

    @media (max-width: 991px) {
        .hero-structural-image { width: 150%; transform: translateX(20%); }
        .structural-img-container { display: none !important; }
    }
    .meta-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-bottom: 0.15rem;
    }
    .meta-value {
        font-size: 0.88rem;
        font-weight: 700;
        color: #1e293b;
    }
    .structural-img-container {
        position: relative;
        overflow: hidden;
        height: 100%;
        padding: 0.75rem;
        isolation: isolate;
        clip-path: polygon(18% 0, 100% 0, 100% 100%, 0% 100%);
    }
    .hero-image-wrap {
        position: relative;
        width: 100%;
        height: 100%;
        min-height: 240px;
        border-radius: 18px;
        overflow: hidden;
    }
    .hero-image-wrap::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(22, 163, 74, 0.20), transparent 35%, transparent 65%, rgba(15, 23, 42, 0.20));
        z-index: 1;
        pointer-events: none;
    }
    .hero-structural-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(2,6,23,0.08);
        transform: scale(1.02);
    }

    @media (min-width: 1400px) {
        .hero-structural-image { max-height: 300px; }
    }

    /* --- METRIC CARD GRID LOOKS --- */
    .metric-status-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 1.25rem;
        height: 100%;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.01);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .metric-status-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
    }
    .metric-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .bg-mint-light { background-color: #f0fdf4; }
    .bg-gray-light { background-color: #f8fafc; }
    .metric-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: capitalize;
    }
    .metric-status-pill {
        display: inline-flex;
        margin-top: 0.2rem;
        padding: 0.3rem 0.55rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    .metric-status-pill.status-on-track {
        background: #ecfdf3;
        color: #166534;
    }
    .metric-status-pill.status-delayed {
        background: #fffbeb;
        color: #b45309;
    }
    .metric-main-val {
        font-size: 1.75rem;
        font-weight: 800;
        margin: 0.15rem 0;
        line-height: 1.1;
    }
    .metric-sub-text {
        font-size: 0.78rem;
        color: var(--text-muted);
    }

    /* --- REUSABLE UI BOXES --- */
    .dashboard-ui-panel {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        height: 100%;
        box-shadow: 0 4px 16px rgba(0,0,0,0.01);
        display: flex;
        flex-direction: column;
    }
    .ui-panel-head {
        padding: 1.5rem 1.5rem 1rem 1.5rem;
        border-bottom: 0;
    }
    .ui-panel-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }
    .ui-panel-body {
        padding: 0 1.5rem 1.5rem 1.5rem;
        flex-grow: 1;
    }
    .view-all-link {
        font-size: 0.85rem;
        font-weight: 700;
        color: #16a34a;
        text-decoration: none;
    }

    /* --- CHART SYSTEM LOOK --- */
    .donut-wrapper {
        width: 180px;
        height: 180px;
    }
    .donut-center-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }
    .donut-center-text h3 {
        font-size: 1.75rem;
        font-weight: 800;
        margin: 0;
    }
    .donut-center-text span {
        font-size: 0.8rem;
        color: var(--text-muted);
    }
    .legend-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 0.5rem;
    }
    .text-sm { font-size: 0.85rem; }
    .font-semibold { font-weight: 600; }
    .rounded-xl { border-radius: 12px !important; }

    /* --- REPORT LISTS STREAMING --- */
    .report-list-group {
        display: flex;
        flex-direction: column;
    }
    .report-item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.2s ease;
        border-radius: 18px;
        margin-bottom: 0.75rem;
        background: #ffffff;
    }
    .report-item-row:last-child { border-bottom: 0; margin-bottom: 0; }
    .report-item-row:hover { background-color: #f8fafc; }
    .file-icon-frame {
        width: 40px;
        height: 40px;
        background-color: #f1f5f9;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: var(--text-muted);
    }
    .report-item-row h6 {
        font-size: 0.88rem;
        font-weight: 700;
        margin: 0 0 0.15rem 0;
    }
    .report-item-row p {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin: 0;
    }
    .download-icon-btn {
        color: var(--text-muted);
        font-size: 1.1rem;
        padding: 0.25rem;
    }
    .download-icon-btn:hover { color: var(--text-primary); }

    /* --- TIMELINE NODES LISTS (UNIFIED & CLEANED UP) --- */
    .activity-timeline-container {
        position: relative;
        padding-left: 2rem;
        margin-left: 0.75rem;
        margin-top: 0.75rem;
    }
    /* Creates the unified tracking background line */
    .activity-timeline-container::before {
        content: '';
        position: absolute;
        top: 8px;
        bottom: 8px;
        left: 11px; /* Centers the path behind the 24px wide circle indicator */
        width: 2px;
        background-color: #e2e8f0;
        border-radius: 2px;
    }
    .activity-timeline-node {
        position: relative;
        padding-bottom: 1.75rem;
    }
    .activity-timeline-node:last-child { 
        padding-bottom: 0; 
    }
    /* Fixed overlapping layouts by positioning the node metrics smoothly */
    .node-icon {
        position: absolute;
        left: -2rem;
        top: 2px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        z-index: 2;
        background-color: #ffffff;
        box-shadow: 0 0 0 3px #ffffff; /* Blocks out line under the icon path */
    }
    
    /* Activity node state modifiers */
    .node-icon.bg-light-green { background-color: #f0fdf4; color: #16a34a; border: 1px solid #16a34a; }
    .node-icon.bg-green-solid { background-color: #22c55e; color: #ffffff; border: 1px solid #22c55e; }
    
    /* Elegant variations case handlers if variables use custom colors */
    .node-icon:not(.bg-light-green):not(.bg-green-solid) {
        background-color: #f8fafc;
        color: #64748b;
        border: 1px solid #cbd5e1;
    }

    .node-content {
        padding-left: 0.5rem;
    }
    .activity-node-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1e293b;
    }
    .activity-node-desc {
        font-size: 0.8rem;
        color: #64748b;
    }
    .node-time {
        font-size: 0.75rem;
        color: #94a3b8;
        font-weight: 500;
    }


    @media (max-width: 768px) {
        .dashboard-page-header {
            align-items: stretch;
            flex-direction: column;
        }
        .dashboard-page-tools {
            width: 100%;
            justify-content: space-between;
        }
        .dashboard-date-pill {
            flex: 1;
            justify-content: center;
        }
        .modal-footer.flex-wrap.gap-2 {
            justify-content: space-between;
        }
        .modal-footer.flex-wrap.gap-2 .btn,
        .modal-footer.flex-wrap.gap-2 .project-command-button-primary {
            flex: 1 1 auto;
            text-align: center;
        }
    }
</style>
@endsection