<div class="project-selector-wrap">
    <button type="button" class="project-selector-button" id="projectSelectorButton" aria-expanded="false" aria-controls="projectSelectorMenu" @if($allProjects->count() <= 1) disabled @endif>
        <span class="project-selector-icon"><i class="bi bi-building"></i></span>
        <span class="project-selector-label">{{ $primaryProjectName }}</span>
        @if($allProjects->count() > 1)
            <i class="bi bi-chevron-down project-selector-caret"></i>
        @endif
    </button>
    <div class="project-selector-menu" id="projectSelectorMenu" hidden>
        @foreach($allProjects as $project)
            <a class="project-selector-item {{ $project->project_id == ($primaryProject?->project_id ?? null) ? 'active' : '' }}" href="{{ route('client.dashboard', ['project_id' => $project->project_id]) }}">
                <span class="project-selector-item-icon"><i class="bi bi-building"></i></span>
                <span>{{ $project->project_name }}</span>
                @if($project->project_id == ($primaryProject?->project_id ?? null))
                    <i class="bi bi-check2 project-selector-check"></i>
                @endif
            </a>
        @endforeach
    </div>
</div>
