import 'dhtmlx-gantt/codebase/dhtmlxgantt.css';
import gantt from 'dhtmlx-gantt';

const statusPalette = {
    completed: '#166534',
    'in-progress': '#1565C0',
    upcoming: '#B7791F',
    delayed: '#C62828',
    pending: '#6B7280'
};

gantt.config.xml_date = '%Y-%m-%d';
gantt.config.columns = [
    { name: 'text', label: 'Phases', tree: true, width: 280 },
    { name: 'start_date', label: 'Start', align: 'center', width: 110 },
    { name: 'end_date', label: 'End', align: 'center', width: 110 },
    {
        name: 'progress',
        label: 'Progress',
        align: 'center',
        width: 100,
        template: function(task) {
            return `${Math.round((Number(task.progress) || 0) * 100)}%`;
        }
    }
];
gantt.config.scale_unit = 'week';
gantt.config.step = 1;
gantt.config.date_scale = '%W';
gantt.config.subscales = [{ unit: 'day', step: 1, date: '%d' }];
gantt.config.scale_height = 54;
gantt.config.row_height = 96;
gantt.config.task_height = 56;
gantt.config.start_on_monday = false;
gantt.config.show_progress = true;
gantt.config.show_today = true;
gantt.config.readonly = true;
gantt.config.drag_move = false;
gantt.config.drag_resize = false;
gantt.config.drag_progress = false;
gantt.config.drag_links = false;
gantt.config.fit_tasks = true;
gantt.config.autofit = true;
gantt.config.open_tree_initially = true;
gantt.config.min_column_width = 46;
gantt.config.scroll_size = 20;
gantt.config.round_dnd_dates = false;
gantt.config.order_branch = true;

if (!document.getElementById('gantt-compact-styles')) {
    const compactStyles = document.createElement('style');
    compactStyles.id = 'gantt-compact-styles';
    compactStyles.textContent = `
        .gantt_task_line {
            height: 56px !important;
            min-height: 56px !important;
            line-height: 56px !important;
            margin-top: 20px !important;
            overflow: visible !important;
            border-radius: 10px !important;
        }
        .gantt_task_progress {
            position: relative !important;
            overflow: visible !important;
            height: 100% !important;
            min-height: 56px !important;
            top: 0 !important;
            bottom: 0 !important;
            border-radius: 10px !important;
            box-sizing: border-box !important;
        }
        .gantt_task_progress::after {
            content: '' !important;
            position: absolute !important;
            inset: 0 !important;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 45%, rgba(255,255,255,0) 100%) !important;
            transform: translateX(-100%) !important;
            animation: gantt-progress-glow 2.4s ease-in-out infinite !important;
        }
        @keyframes gantt-progress-glow {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(120%); }
        }
        .gantt_task_row {
            height: 96px !important;
        }
        .gantt_task_content {
            line-height: 56px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            padding: 0 8px !important;
        }
        .gantt_task_content {
            color: #f8fafc !important;
            font-weight: 500 !important;
            font-size: 12px !important;
            text-shadow: 0 1px 2px rgba(15, 23, 42, 0.25) !important;
        }
        .gantt_progress_overlay {
            position: absolute !important;
            inset: 0 !important;
            display: flex !important;
            align-items: flex-start !important;
            justify-content: flex-start !important;
            pointer-events: none !important;
            z-index: 8 !important;
            overflow: visible !important;
        }
        .gantt-progress-flag {
            position: absolute !important;
            top: -10px !important;
            transform: translate(-50%, -50%) !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 30px !important;
            height: 24px !important;
            padding: 0 7px !important;
            border-radius: 999px !important;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%) !important;
            color: #166534 !important;
            font-weight: 800 !important;
            font-size: 10px !important;
            letter-spacing: 0.02em !important;
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.18), 0 0 0 1px rgba(22, 101, 52, 0.12) !important;
            border: 1px solid rgba(22, 101, 52, 0.16) !important;
            white-space: nowrap !important;
            cursor: pointer !important;
            pointer-events: auto !important;
            z-index: 12 !important;
            transition: transform 180ms ease, box-shadow 180ms ease, filter 180ms ease !important;
            filter: drop-shadow(0 4px 8px rgba(34, 197, 94, 0.14)) !important;
            overflow: visible !important;
        }
        .gantt-progress-flag:hover,
        .gantt-progress-flag.is-active {
            transform: translate(-50%, -50%) translateY(-2px) !important;
            box-shadow: 0 12px 24px rgba(34, 197, 94, 0.22), 0 0 0 1px rgba(22, 101, 52, 0.16) !important;
            filter: drop-shadow(0 6px 10px rgba(34, 197, 94, 0.18)) !important;
        }
        .gantt-progress-flag::after {
            content: '' !important;
            position: absolute !important;
            bottom: -6px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            width: 0 !important;
            height: 0 !important;
            border-left: 6px solid transparent !important;
            border-right: 6px solid transparent !important;
            border-top: 6px solid #d1fae5 !important;
            opacity: 0.95 !important;
        }
        .gantt-progress-flag .gantt-flag-icon {
            font-size: 10px !important;
            line-height: 1 !important;
        }
        .gantt-flag-popover {
            position: absolute !important;
            left: 50% !important;
            top: calc(100% + 10px) !important;
            transform: translateX(-50%) !important;
            min-width: 220px !important;
            max-width: 260px !important;
            padding: 10px 12px !important;
            border-radius: 12px !important;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
            color: #0f172a !important;
            font-size: 11px !important;
            line-height: 1.45 !important;
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12) !important;
            border: 1px solid #dcfce7 !important;
            pointer-events: none !important;
            z-index: 99999 !important;
            display: none !important;
        }
        .gantt-flag-popover .popover-title {
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
            margin-bottom: 7px !important;
            font-size: 12px !important;
            font-weight: 800 !important;
            color: #166534 !important;
        }
        .gantt-flag-popover .popover-title::before {
            content: '' !important;
            width: 8px !important;
            height: 8px !important;
            border-radius: 999px !important;
            background: linear-gradient(135deg, #86efac 0%, #22c55e 100%) !important;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.16) !important;
        }
        .gantt-flag-popover .popover-meta {
            display: flex !important;
            justify-content: space-between !important;
            gap: 8px !important;
            margin-top: 3px !important;
            opacity: 0.95 !important;
        }
        .gantt-flag-popover .popover-meta .label {
            color: #64748b !important;
        }
        .gantt-flag-popover .popover-meta .value {
            color: #0f172a !important;
            text-align: right !important;
            font-weight: 600 !important;
        }
        .gantt-progress-flag:hover .gantt-flag-popover,
        .gantt-progress-flag.is-active .gantt-flag-popover {
            display: block !important;
        }
    `;
    document.head.appendChild(compactStyles);
}

gantt.templates.task_class = function (start, end, task) {
    return `gantt-task-${task.custom_class || 'upcoming'}`;
};

gantt.templates.grid_row_class = function (start, end, task) {
    return task.type === 'milestone' ? 'gantt-milestone-row' : '';
};

gantt.templates.progress_text = function (start, end, task) {
    return '';
};

gantt.templates.task_text = function (start, end, task) {
    return task.text || task.name || '';
};

gantt.templates.tooltip_text = function (start, end, task) {
    return `<div style="font-weight:700;margin-bottom:6px">${task.text}</div>
            <div>Start: ${task.start_date || 'Not set'}</div>
            <div>End: ${task.end_date || 'Not set'}</div>
            <div>Status: ${task.custom_class || 'upcoming'}</div>
            <div>Progress: ${Math.round((task.progress || 0) * 100)}%</div>`;
};

let initialized = false;
let activeScale = 'week';
let currentGanttTasks = [];
let ganttContainer = null;
let eventsAttached = false;
let milestoneFlagElements = new Map();
let ganttScrollHooksAttached = false;
let taskStylingTimer = null;

const zoomLevels = [
    { name: 'day', scale_height: 54, min_column_width: 48, scales: [{ unit: 'day', step: 1, date: '%d %M' }] },
    { name: 'week', scale_height: 54, min_column_width: 56, scales: [{ unit: 'month', step: 1, date: '%F %Y' }, { unit: 'week', step: 1, date: '%W' }] },
    { name: 'month', scale_height: 54, min_column_width: 60, scales: [{ unit: 'year', step: 1, date: '%Y' }, { unit: 'month', step: 1, date: '%F' }] },
    { name: 'quarter', scale_height: 54, min_column_width: 70, scales: [{ unit: 'year', step: 1, date: '%Y' }, { unit: 'quarter', step: 1, date: '%M %Y' }] },
    { name: 'year', scale_height: 54, min_column_width: 70, scales: [{ unit: 'year', step: 1, date: '%Y' }] }
];

function applyScalePreset(scale) {
    activeScale = scale || 'month';
    if (gantt.ext?.zoom && typeof gantt.ext.zoom.setLevel === 'function') {
        try {
            gantt.ext.zoom.setLevel(activeScale);
            return;
        } catch (err) {
            console.warn('Gantt zoom level switch failed', err);
        }
    }

    const preset = zoomLevels.find((level) => level.name === activeScale) || zoomLevels[2];
    gantt.config.scale_unit = preset.scales?.[preset.scales.length - 1]?.unit || 'month';
    gantt.config.step = preset.scales?.[preset.scales.length - 1]?.step || 1;
    gantt.config.date_scale = preset.scales?.[preset.scales.length - 1]?.date || '%b %Y';
    gantt.config.subscales = preset.scales.slice(0, -1).map((scaleItem) => ({ unit: scaleItem.unit, step: scaleItem.step || 1, date: scaleItem.date })) || [];
    gantt.config.scale_height = preset.scale_height || 54;
    gantt.config.min_column_width = preset.min_column_width || 60;
}

function syncGanttHeight() {
    const container = document.getElementById('dhtmlxGantt');
    if (!container || !initialized) return;

    const rowCount = Math.max(4, (currentGanttTasks || []).length || 0);
    const rowHeight = gantt.config.row_height || 40;
    const scaleHeight = gantt.config.scale_height || 54;
    const desiredHeight = Math.max(420, scaleHeight + (rowCount * rowHeight) + 24);

    container.style.height = `${desiredHeight}px`;
    container.style.minHeight = `${desiredHeight}px`;

    try {
        gantt.setSizes();
    } catch (err) {
        gantt.render();
    }
}

function scheduleTaskStylingRefresh() {
    // Throttle with rAF instead of a "wait until scrolling stops" debounce so
    // flags get repositioned on (near) every frame while the user is actively
    // scrolling, instead of vanishing until the scroll comes to a rest.
    if (taskStylingTimer) return;
    taskStylingTimer = window.requestAnimationFrame(() => {
        taskStylingTimer = null;
        applyTaskStyling();
    });
}

function attachGanttScrollHooks() {
    if (ganttScrollHooksAttached) return;

    // Primary hook: DHTMLX's own public scroll event. This fires for both
    // horizontal and vertical scrolling of the timeline regardless of which
    // internal DOM elements/classes the installed dhtmlx-gantt version uses
    // internally, so it doesn't rely on guessing class names that may not
    // exist (which is why flags previously stopped refreshing on scroll).
    gantt.attachEvent('onGanttScroll', function () {
        scheduleTaskStylingRefresh();
        return true;
    });

    gantt.attachEvent('onGanttRender', function () {
        scheduleTaskStylingRefresh();
        return true;
    });

    // Secondary/backup hooks: also listen on the raw DOM containers in case
    // they exist, and on the outer scroll wrapper from the Blade template.
    const attachRawScrollListeners = () => {
        const root = document.getElementById('dhtmlxGantt');
        if (!root) return;

        const scrollTargets = [
            root.querySelector('.gantt_task_scroll'),
            root.querySelector('.gantt_data_area'),
            root.querySelector('.gantt_grid_data'),
            root.querySelector('.gantt_hor_scroll'),
            root.querySelector('.gantt_ver_scroll'),
            root.closest('.gantt-scroll-shell')
        ].filter(Boolean);

        scrollTargets.forEach((target) => {
            target.addEventListener('scroll', scheduleTaskStylingRefresh, { passive: true });
        });
    };

    attachRawScrollListeners();
    // DHTMLX (re)builds parts of its internal DOM on init/data load, so try
    // again shortly after in case the containers weren't present yet.
    window.setTimeout(attachRawScrollListeners, 200);

    window.addEventListener('resize', scheduleTaskStylingRefresh);
    ganttScrollHooksAttached = true;
}

function refreshGanttView() {
    if (!initialized) return;
    applyScalePreset(activeScale);
    gantt.render();
    try { gantt.refreshData(); } catch (err) { console.warn(err); }
    attachGanttScrollHooks();
    scheduleTaskStylingRefresh();
    syncGanttHeight();
}

function mapTasks(tasks) {
    return (tasks || []).map((task, index) => ({
        id: String(task.id ?? task.phase_id ?? `${task.project_id ?? 'project'}-${index}`),
        text: task.text || task.name || 'Milestone',
        start_date: task.start_date || task.start || '',
        end_date: task.end_date || task.end || '',
        progress: Number(task.progress) || 0,
        custom_class: task.custom_class || task.status || 'upcoming',
        type: task.type || (task.end_date || task.end ? 'task' : 'milestone'),
        parent: task.parent ? String(task.parent) : 0,
        open: true,
        color: task.color || statusPalette[task.custom_class || task.status] || statusPalette.upcoming,
        milestones: Array.isArray(task.milestones) ? task.milestones : []
    })).filter(task => task.start_date);
}

function adjustColor(color, amount) {
    const safeColor = color || '#1565C0';
    const hex = safeColor.replace('#', '');
    if (!/^([0-9a-f]{3}|[0-9a-f]{6})$/i.test(hex)) {
        return safeColor;
    }

    const fullHex = hex.length === 3 ? hex.split('').map((value) => value + value).join('') : hex;
    const intValue = parseInt(fullHex, 16);

    const clamp = (value) => Math.max(0, Math.min(255, value));
    const r = clamp((intValue >> 16) + amount);
    const g = clamp(((intValue >> 8) & 0x00ff) + amount);
    const b = clamp((intValue & 0x0000ff) + amount);

    return `#${[r, g, b].map((value) => value.toString(16).padStart(2, '0')).join('')}`;
}

function parseMilestoneDate(value) {
    if (!value) return null;
    if (typeof value === 'string') {
        const trimmed = value.trim();
        const dateOnly = trimmed.match(/^(\d{4}-\d{2}-\d{2})/);
        if (dateOnly) {
            const [year, month, day] = dateOnly[1].split('-').map(Number);
            return new Date(year, month - 1, day);
        }
    }
    const parsed = new Date(value);
    return Number.isNaN(parsed.getTime()) ? null : parsed;
}

function formatMilestoneDate(value) {
    const parsed = parseMilestoneDate(value);
    if (!parsed) return 'Not set';
    return parsed.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function getMilestoneColor(milestone, milestoneIndex, phaseIndex) {
    if (milestone?.color) return milestone.color;
    const palette = ['#ef4444', '#0ea5e9', '#8b5cf6', '#f59e0b', '#14b8a6', '#ec4899', '#6366f1', '#f97316', '#22c55e', '#a855f7'];
    const seed = ((phaseIndex + 1) * 31) + (milestoneIndex + 1) * 7;
    return palette[seed % palette.length];
}

function buildMilestonePopoverContent(milestone) {
    const name = milestone?.milestone_name || milestone?.name || 'Milestone';
    const startDate = formatMilestoneDate(milestone?.planned_date || milestone?.start_date || milestone?.scheduled_date);
    const endDate = formatMilestoneDate(milestone?.actual_date || milestone?.end_date || milestone?.completed_date || milestone?.actual_end_date);
    return `
        <div class="popover-title">${name}</div>
        <div class="popover-meta"><span class="label">Start date</span><span class="value">${startDate}</span></div>
        <div class="popover-meta"><span class="label">End date</span><span class="value">${endDate}</span></div>
    `;
}

function showMilestonePopover(flag, milestone) {
    if (!flag) return;
    let popover = flag.querySelector('.gantt-flag-popover');
    if (!popover) {
        popover = document.createElement('div');
        popover.className = 'gantt-flag-popover';
        flag.appendChild(popover);
    }
    popover.innerHTML = buildMilestonePopoverContent(milestone);
    popover.style.display = 'block';
    flag.classList.add('is-active');
}

function hideMilestonePopover(flag) {
    if (!flag) return;
    const popover = flag.querySelector('.gantt-flag-popover');
    if (popover) {
        popover.style.display = 'none';
    }
    flag.classList.remove('is-active');
}

function applyTaskStyling() {
    setTimeout(() => {
        const barHeight = gantt.config.task_height || 36;
        const activeFlagKeys = new Set();

        document.querySelectorAll('.gantt_task_line').forEach((taskLine) => {
            const id = taskLine.getAttribute('data-id') || taskLine.getAttribute('data-task-id');
            if (!id) return;
            let task = null;
            try {
                task = gantt.getTask(id);
            } catch (err) {
                return;
            }
            if (!task) return;
            const color = task.color || statusPalette[task.custom_class] || statusPalette.upcoming;
            const progressPercent = Math.min(100, Math.max(0, Number(task.progress || 0) * 100));
            const accentColor = adjustColor(color, -10);
            // Track (the full bar) uses a muted/lightened tint of the status color.
            const lineGradient = `linear-gradient(135deg, ${adjustColor(color, 70)} 0%, ${adjustColor(color, 55)} 100%)`;
            // Fill (the completed portion) uses the vivid, saturated status color so it
            // reads clearly against the lighter track instead of blending into it.
            const progressGradient = `linear-gradient(135deg, ${color} 0%, ${accentColor} 100%)`;
            const statusText = [task.custom_class, task.status, task.display_status, task.state, task.phase_status, task.current_status]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();
            const isCompleted = ['completed', 'complete', 'done', 'finished', 'closed'].some((value) => statusText.includes(value))
                || task.is_completed === true
                || task.completed === true
                || task.isComplete === true
                || task.complete === true
                || progressPercent >= 100;

            taskLine.style.background = lineGradient;
            taskLine.style.backgroundImage = lineGradient;
            taskLine.style.borderColor = color;
            taskLine.style.borderRadius = '10px';
            taskLine.style.boxShadow = '0 8px 18px rgba(15, 23, 42, 0.12)';
            taskLine.style.height = `${barHeight}px`;
            taskLine.style.transition = 'all 180ms ease';

            const progressEl = taskLine.querySelector('.gantt_task_progress');
            if (progressEl) {
                progressEl.style.background = progressGradient;
                progressEl.style.backgroundImage = progressGradient;
                progressEl.style.borderColor = color;
                progressEl.style.borderRadius = '10px';
            }

            let overlay = taskLine.querySelector('.gantt_progress_overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'gantt_progress_overlay';
                taskLine.appendChild(overlay);
            }

            overlay.innerHTML = '';
            overlay.style.display = isCompleted ? 'none' : 'block';

            const milestones = Array.isArray(task.milestones) ? task.milestones : [];
            const taskStart = parseMilestoneDate(task.start_date);
            const taskEnd = parseMilestoneDate(task.end_date);
            const taskRangeDays = taskStart && taskEnd ? (taskEnd - taskStart) / 86400000 : 0;

            milestones.forEach((milestone, milestoneIndex) => {
                const milestoneStart = parseMilestoneDate(milestone.planned_date || milestone.actual_date || milestone.start || milestone.start_date);
                if (!milestoneStart || !taskStart || !taskEnd) return;
                const elapsedDays = (milestoneStart - taskStart) / 86400000;
                const ratio = taskRangeDays > 0 ? Math.min(1, Math.max(0, elapsedDays / taskRangeDays)) : 0;
                const leftPercent = `${(ratio * 100).toFixed(2)}%`;
                const flagKey = `${task.id}-${milestoneIndex}`;
                activeFlagKeys.add(flagKey);

                let flag = milestoneFlagElements.get(flagKey);
                if (!flag) {
                    flag = document.createElement('button');
                    flag.type = 'button';
                    flag.className = 'gantt-progress-flag';
                    flag.innerHTML = '<i class="gantt-flag-icon bi bi-flag-fill"></i>';
                    milestoneFlagElements.set(flagKey, flag);
                }

                flag.style.left = leftPercent;
                flag.style.background = 'linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%)';
                flag.style.color = '#166534';
                flag.setAttribute('title', milestone.milestone_name || 'Milestone');
                flag.setAttribute('aria-label', milestone.milestone_name || 'Milestone');
                flag.onmouseenter = () => showMilestonePopover(flag, milestone);
                flag.onmousemove = () => showMilestonePopover(flag, milestone);
                flag.onmouseleave = () => hideMilestonePopover(flag);
                flag.onfocus = () => showMilestonePopover(flag, milestone);
                flag.onblur = () => hideMilestonePopover(flag);
                flag.onclick = (event) => {
                    event.stopPropagation();
                    showMilestonePopover(flag, milestone);
                };

                if (!flag.isConnected) {
                    overlay.appendChild(flag);
                }
            });
        });

        milestoneFlagElements.forEach((flag, key) => {
            if (!activeFlagKeys.has(key)) {
                flag.remove();
                milestoneFlagElements.delete(key);
            }
        });

        document.querySelectorAll('.gantt_task_content').forEach((el) => {
            el.style.lineHeight = `${barHeight}px`;
        });
    }, 16);
}

function attachGanttEvents() {
    if (eventsAttached) return;
    gantt.attachEvent('onTaskClick', function (id) {
        try { window.openPhaseModal(String(id), false); } catch (err) { console.warn(err); }
        setTimeout(() => applyTaskStyling(), 80);
        return true;
    });
    gantt.attachEvent('onTaskDblClick', function (id) {
        try { window.openPhaseModal(String(id), true); } catch (err) { console.warn(err); }
        setTimeout(() => applyTaskStyling(), 80);
        return false;
    });
    eventsAttached = true;
}

window.initDhtmlxGantt = function (tasks, project) {
    const container = document.getElementById('dhtmlxGantt');
    if (!container) return;

    container.style.minHeight = container.style.minHeight || '560px';

    const needsReinit = !initialized || ganttContainer !== container;
    if (needsReinit) {
        applyScalePreset(activeScale);
        gantt.init(container);
        if (gantt.ext?.zoom && typeof gantt.ext.zoom.init === 'function') {
            try {
                gantt.ext.zoom.init({
                    levels: zoomLevels,
                    activeLevelIndex: zoomLevels.findIndex((level) => level.name === activeScale),
                    trigger: null,
                    minColumnWidth: 48,
                    maxColumnWidth: 120,
                    widthStep: 8
                });
            } catch (err) {
                console.warn('Gantt zoom init failed', err);
            }
        }
        attachGanttEvents();
        attachGanttScrollHooks();
        ganttContainer = container;
        initialized = true;
    }

    currentGanttTasks = Array.isArray(tasks) ? tasks : [];
    const data = { data: mapTasks(currentGanttTasks) };
    gantt.clearAll();
    gantt.parse(data);
    refreshGanttView();
};

window.refreshDhtmlxGantt = function (tasks) {
    if (!initialized) return window.initDhtmlxGantt(tasks);
    currentGanttTasks = Array.isArray(tasks) ? tasks : [];
    const data = { data: mapTasks(currentGanttTasks) };
    gantt.clearAll();
    gantt.parse(data);
    refreshGanttView();
};

window.setDhtmlxScale = function (scale) {
    applyScalePreset(scale);
    if (!initialized) {
        return;
    }
    const data = { data: mapTasks(currentGanttTasks) };
    gantt.clearAll();
    gantt.parse(data);
    refreshGanttView();
};

window.setDhtmlxZoom = function (direction) {
    const current = gantt.config.min_column_width || 46;
    const next = direction === 'in' ? Math.min(120, current + 8) : Math.max(32, current - 8);
    gantt.config.min_column_width = next;
    gantt.render();
    applyTaskStyling();
};

window.gantt = gantt;

function tryAutoInit() {
    try {
        if (window.initialGanttTasks && Array.isArray(window.initialGanttTasks)) {
            window.initDhtmlxGantt(window.initialGanttTasks, window.selectedProject || null);
            window.initialGanttTasks = null;
        }
    } catch (e) { console.warn('DHTMLX auto-init failed', e); }
}

document.addEventListener('DOMContentLoaded', tryAutoInit);
if (document.readyState !== 'loading') {
    tryAutoInit();
}

window.addEventListener('resize', () => {
    if (initialized) {
        gantt.render();
        applyTaskStyling();
    }
});

console.log('DHTMLX Gantt module loaded');