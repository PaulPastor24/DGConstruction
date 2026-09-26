@extends('layouts.admin')

@section('title', 'Landing Page Gallery - D&G Construction Monitor')
@section('page_title', 'Landing Page Gallery')

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 mb-1">Add a completed project</h2>
            <p class="text-muted small mb-3">Choose a project from the system or add a past featured project not in the system.</p>
            <form action="{{ route('admin.landing-gallery.store') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                @csrf
                <div class="col-12 col-md-5">
                    <label for="project_id" class="form-label">Project</label>
                    <select id="project_id" name="project_id" class="form-select">
                        <option value="">Select a completed project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->project_id }}">{{ $project->project_name }}</option>
                        @endforeach
                    </select>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" value="1" id="is_external" name="is_external">
                        <label class="form-check-label small" for="is_external">
                            This is an external / past featured project not in the system
                        </label>
                    </div>
                </div>
                <div class="col-12 col-md-5">
                    <label for="image" class="form-label">Carousel image</label>
                    <input id="image" name="image" type="file" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp" required>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-plus-lg me-1"></i>Add image</button>
                </div>
            </form>
            @error('project_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
            @error('image') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

            <div id="externalProjectFields" class="row g-3 mt-2" style="display: none;">
                <div class="col-12 col-md-6">
                    <label for="external_project_name" class="form-label">External project name</label>
                    <input id="external_project_name" name="external_project_name" type="text" class="form-control" maxlength="255">
                </div>
                <div class="col-12 col-md-6">
                    <label for="external_project_location" class="form-label">Location</label>
                    <input id="external_project_location" name="external_project_location" type="text" class="form-control" maxlength="255">
                </div>
                <div class="col-12">
                    <label for="external_project_description" class="form-label">Description</label>
                    <textarea id="external_project_description" name="external_project_description" class="form-control" rows="3" maxlength="2000"></textarea>
                </div>
                <div class="col-12">
                    <label for="external_project_url" class="form-label">Project URL (optional)</label>
                    <input id="external_project_url" name="external_project_url" type="url" class="form-control" maxlength="2000" placeholder="https://...">
                </div>
            </div>
        </div>
    </div>

    <form id="galleryReorderForm" action="{{ route('admin.landing-gallery.reorder') }}" method="POST">
        @csrf
        @method('PATCH')
    </form>
    <div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Current carousel entries</span>
                <button type="submit" form="galleryReorderForm" class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-down-up me-1"></i>Save order</button>
            </div>
            <div class="list-group list-group-flush">
                @forelse($galleryImages as $galleryImage)
                    <div class="list-group-item d-flex flex-wrap gap-3 align-items-center" data-gallery-row>
                        <img src="{{ asset('storage/' . ltrim($galleryImage->image_path, '/')) }}" alt="{{ $galleryImage->display_name }}" style="width:96px;height:64px;object-fit:cover;border-radius:8px;">
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $galleryImage->display_name }}</div>
                            <div class="small text-muted">{{ $galleryImage->display_location ?: 'No location' }} · Position {{ $loop->iteration }} · {{ $galleryImage->is_active ? 'Visible' : 'Hidden' }} · {{ $galleryImage->is_external ? 'External' : 'System' }}</div>
                        </div>
                        <input type="hidden" name="order[]" value="{{ $galleryImage->id }}" form="galleryReorderForm">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Change gallery position">
                            <button type="button" class="btn btn-outline-secondary" data-move-gallery="up" title="Move up"><i class="bi bi-chevron-up"></i></button>
                            <button type="button" class="btn btn-outline-secondary" data-move-gallery="down" title="Move down"><i class="bi bi-chevron-down"></i></button>
                        </div>
                        <form action="{{ route('admin.landing-gallery.toggle', $galleryImage) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $galleryImage->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                                <i class="bi bi-eye{{ $galleryImage->is_active ? '-slash' : '' }} me-1"></i>{{ $galleryImage->is_active ? 'Hide' : 'Show' }}
                            </button>
                        </form>
                        <form action="{{ route('admin.landing-gallery.destroy', $galleryImage) }}" method="POST" onsubmit="return confirm('Remove this image from the landing page?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
                        </form>
                    </div>
                @empty
                    <div class="list-group-item text-muted">No landing page images have been added yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-move-gallery]').forEach(button => {
    button.addEventListener('click', () => {
        const row = button.closest('[data-gallery-row]');
        const sibling = button.dataset.moveGallery === 'up' ? row.previousElementSibling : row.nextElementSibling;

        if (!row || !sibling || !sibling.matches('[data-gallery-row]')) return;
        if (button.dataset.moveGallery === 'up') {
            row.parentElement.insertBefore(row, sibling);
        } else {
            row.parentElement.insertBefore(sibling, row);
        }
    });
});

document.getElementById('is_external').addEventListener('change', function () {
    const fields = document.getElementById('externalProjectFields');
    const projectSelect = document.getElementById('project_id');

    if (this.checked) {
        fields.style.display = 'flex';
        projectSelect.value = '';
    } else {
        fields.style.display = 'none';
    }
});
</script>
@endpush