@extends('layouts.admin')

@section('title', 'Landing Page Gallery - D&G Construction Monitor')
@section('page_title', 'Landing Page Gallery')

@section('content')
<div class="landing-gallery-page">
        @php
        $formErrorMessage = $errors->first();
        $successMessage = session('success');
        $flashErrorMessage = session('error');
    @endphp

    @if($successMessage)
        <div class="alert alert-success mb-4 d-none" data-swal-success="{{ $successMessage }}">{{ $successMessage }}</div>
    @endif
    @if($flashErrorMessage)
        <div class="alert alert-danger mb-4 d-none" data-swal-error="{{ $flashErrorMessage }}">{{ $flashErrorMessage }}</div>
    @endif
    @if($formErrorMessage)
        <div class="alert alert-danger mb-4 d-none" data-swal-error="{{ $formErrorMessage }}">{{ $formErrorMessage }}</div>
    @endif

    <div class="ug-hero-card landing-gallery-hero mb-4" aria-label="Landing page gallery header">
        <div class="dashboard-title-area">
            <h2>Manage Landing Page Gallery</h2>
            <p class="text-muted small mb-0">Upload and organize the images shown on the public landing page of your project. Add new images and keep your gallery up to date.</p>
        </div>

        <button type="button" class="ug-add-user-btn" data-bs-toggle="modal" data-bs-target="#addFeaturedProjectPanel" aria-controls="addFeaturedProjectPanel">
            <span class="ug-add-icon"><i class="bi bi-plus-lg"></i></span>
            <span>Add Featured Project</span>
        </button>
    </div>

    <section class="landing-preview-panel mb-4" aria-labelledby="landingPreviewTitle">
        <div class="landing-preview-panel__header">
            <div>
                <h3 id="landingPreviewTitle">Landing page preview</h3>
                <p>Click the hero image or a featured project image in the preview to edit that specific item. Adding a project creates a new item and does not replace existing projects.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#editHeroModal">
                    <i class="bi bi-image me-1"></i>Replace Hero Image
                </button>
                <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Open public page
                </a>
            </div>
        </div>
        <div class="landing-preview-frame">
            <iframe id="landingPagePreview" src="{{ url('/') }}?admin_gallery_preview=1" title="Live landing page preview"></iframe>
            <div class="landing-preview-loading" id="landingPreviewLoading">
                <span class="spinner-border spinner-border-sm me-2"></span>Loading landing page preview...
            </div>
        </div>
    </section>

    <div class="modal fade" id="editHeroModal" tabindex="-1" aria-labelledby="editHeroModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="editHeroModalLabel">Replace hero image</h5>
                        <p class="text-muted small mb-0">This changes only the hero image. Featured projects will not be removed or replaced.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.landing-gallery.hero.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        @if($landingPageSetting?->hero_image_path)
                            <div class="edit-gallery-preview mb-3">
                                <img src="{{ asset('storage/' . ltrim($landingPageSetting->hero_image_path, '/')) }}" alt="Current landing page hero image">
                            </div>
                        @endif
                        <label for="hero_image" class="form-label fw-semibold">New hero image</label>
                        <input id="hero_image" name="hero_image" type="file" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp" required>
                        <div class="form-text">JPG, PNG, or WebP. Maximum 5 MB.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-check2 me-1"></i>Save hero image</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addFeaturedProjectPanel" tabindex="-1" aria-labelledby="addFeaturedProjectPanelLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
        <div class="modal-header">
            <div class="panel-icon">
                <i class="bi bi-folder2-open"></i>
            </div>
            <div class="landing-gallery-panel__title-copy">
                <h5 class="modal-title" id="addFeaturedProjectPanelLabel">Add Featured Project</h5>
                <p class="text-muted small mb-0">Add a new project without replacing existing featured projects.</p>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form id="galleryCreateForm" action="{{ route('admin.landing-gallery.store') }}" method="POST" enctype="multipart/form-data" class="landing-gallery-form">
            @csrf
            <div class="modal-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" value="1" id="is_external" name="is_external">
                    <label class="form-check-label fw-semibold" for="is_external">External / past project not in the system</label>
                </div>

            <div class="form-grid">
                <div class="field-group field-group--project">
                    <label for="project_id">Project</label>
                    <select id="project_id" name="project_id" class="form-select" required>
                        <option value="">Select a completed project</option>
                        @foreach($projects as $project)
                        <option
                            value="{{ $project->project_id }}"
                            data-project-location="{{ $project->location }}"
                            data-project-description="{{ $project->description }}"
                            data-project-bedrooms="{{ $project->bedrooms }}"
                            data-project-bathrooms="{{ $project->bathrooms }}"
                            data-project-lot-area="{{ $project->lot_area }}"
                            data-project-highlights='@json($project->highlights ?? [])'
                            data-project-features='@json($project->features ?? [])'
                        >{{ $project->project_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group field-group--image">
                    <label for="image">Carousel Image</label>
                    <div class="upload-field-wrap">
                        <span class="upload-field-icon"><i class="bi bi-upload"></i></span>
                        <input id="image" name="image" type="file" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp" required>
                        <span class="file-name-display">No file chosen</span>
                    </div>
                </div>

                <div id="systemProjectInfo" class="project-info-preview mt-3" hidden>
                    <div class="project-info-preview__header">
                        <strong>Project information</strong>
                        <span>Loaded from the selected system project</span>
                    </div>
                    <div class="project-info-preview__grid">
                        <div><span>Location</span><strong data-project-info="location">-</strong></div>
                        <div><span>Bedrooms</span><strong data-project-info="bedrooms">-</strong></div>
                        <div><span>Bathrooms</span><strong data-project-info="bathrooms">-</strong></div>
                        <div><span>Lot area</span><strong data-project-info="lot_area">-</strong></div>
                    </div>
                    <p data-project-info="description" class="mb-2">Select a project to view its description.</p>
                    <div class="project-info-preview__lists">
                        <div><span>Highlights</span><ul data-project-info-list="highlights"><li>-</li></ul></div>
                        <div><span>Features</span><ul data-project-info-list="features"><li>-</li></ul></div>
                    </div>
                    <a href="{{ url('/admin/projects') }}" target="_blank" rel="noopener" class="small">Edit this project’s information in Project Management</a>
                </div>

                <div class="field-group mt-3">
                    <label for="project_gallery_images">Project gallery images (optional)</label>
                    <input id="project_gallery_images" name="project_gallery_images[]" type="file" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp" multiple>
                    <div class="form-text">These images appear in the expanded Project Gallery. You can add up to 12 images.</div>
                </div>

                <div id="externalProjectFields" class="external-project-fields mt-3" hidden>
                    <div class="form-grid">
                        <div class="field-group">
                            <label for="external_project_name">Featured project name</label>
                            <input id="external_project_name" name="external_project_name" type="text" class="form-control" maxlength="255" placeholder="e.g. Modern Residential Home">
                        </div>
                        <div class="field-group">
                            <label for="external_project_location">Location</label>
                            <input id="external_project_location" name="external_project_location" type="text" class="form-control" maxlength="255" placeholder="e.g. Quezon City">
                        </div>
                    </div>
                    <div class="field-group mt-3">
                        <label for="external_project_description">Description</label>
                        <textarea id="external_project_description" name="external_project_description" class="form-control" rows="3" maxlength="2000" placeholder="Information clients should see about this project"></textarea>
                    </div>
                    <div class="field-group mt-3">
                        <label for="external_project_url">Project link (optional)</label>
                        <input id="external_project_url" name="external_project_url" type="url" class="form-control" maxlength="2000" placeholder="https://example.com/project">
                    </div>
                    <div class="form-grid mt-3">
                        <div class="field-group">
                            <label for="external_bedrooms">Bedrooms</label>
                            <input id="external_bedrooms" name="external_bedrooms" type="number" class="form-control" min="0" max="99" placeholder="e.g. 4">
                        </div>
                        <div class="field-group">
                            <label for="external_bathrooms">Bathrooms</label>
                            <input id="external_bathrooms" name="external_bathrooms" type="number" class="form-control" min="0" max="99" placeholder="e.g. 3">
                        </div>
                        <div class="field-group">
                            <label for="external_lot_area">Lot area (sqm)</label>
                            <input id="external_lot_area" name="external_lot_area" type="number" class="form-control" min="0" step="0.01" placeholder="e.g. 250">
                        </div>
                    </div>
                    <div class="field-group mt-3">
                        <label for="external_highlights">Project highlights</label>
                        <textarea id="external_highlights" name="external_highlights" class="form-control" rows="3" placeholder="One highlight per line"></textarea>
                    </div>
                    <div class="field-group mt-3">
                        <label for="external_features">Features</label>
                        <textarea id="external_features" name="external_features" class="form-control" rows="3" placeholder="One feature per line"></textarea>
                    </div>
                </div>
            </div>

            @error('project_id')
                <div class="field-error">{{ $message }}</div>
            @enderror
            @error('image')
                <div class="field-error">{{ $message }}</div>
            @enderror
            @foreach(['external_project_name', 'external_project_location', 'external_project_description', 'external_project_url', 'external_bedrooms', 'external_bathrooms', 'external_lot_area', 'external_highlights', 'external_features'] as $externalField)
                @error($externalField)
                    <div class="field-error">{{ $message }}</div>
                @enderror
            @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i>Add Featured Project</button>
            </div>
        </form>
            </div>
        </div>
    </div>

    <form id="galleryReorderForm" action="{{ route('admin.landing-gallery.reorder') }}" method="POST">
        @csrf
        @method('PATCH')
    </form>

    <section class="landing-gallery-panel landing-gallery-panel--list">
        <div class="landing-gallery-panel__header">
            <div class="landing-gallery-panel__title-wrap">
                <div class="panel-icon panel-icon--sm">
                    <i class="bi bi-card-image"></i>
                </div>
                <h3>Current carousel entries</h3>
            </div>
            <button type="submit" form="galleryReorderForm" class="btn btn-sm btn-outline-success">
                <i class="bi bi-check2 me-1"></i>Save order
            </button>
        </div>

        @if($galleryImages->count())
            <div class="gallery-row-list">
                @foreach($galleryImages as $galleryImage)
                    <div class="gallery-row-item" data-gallery-row>
                        <button type="button" class="gallery-image-button" data-bs-toggle="modal" data-bs-target="#editGalleryModal{{ $galleryImage->id }}" aria-label="Edit {{ $galleryImage->display_name }}">
                            <img src="{{ asset('storage/' . ltrim($galleryImage->image_path, '/')) }}" alt="{{ $galleryImage->display_name }}">
                            <span class="gallery-image-edit-hint"><i class="bi bi-pencil"></i> Edit</span>
                        </button>
                        <div class="gallery-row-copy">
                            <div class="gallery-row-name">{{ $galleryImage->display_name }}</div>
                            <div class="gallery-row-meta">Position {{ $loop->iteration }} · {{ $galleryImage->is_active ? 'Visible' : 'Hidden' }}</div>
                        </div>
                        <input type="hidden" name="order[]" value="{{ $galleryImage->id }}" form="galleryReorderForm">
                        <div class="gallery-row-actions">
                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#editGalleryModal{{ $galleryImage->id }}">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                            <div class="btn-group btn-group-sm" role="group" aria-label="Change gallery position">
                                <button type="button" class="btn btn-outline-secondary" data-move-gallery="up" title="Move up"><i class="bi bi-chevron-up"></i></button>
                                <button type="button" class="btn btn-outline-secondary" data-move-gallery="down" title="Move down"><i class="bi bi-chevron-down"></i></button>
                            </div>
                            <form action="{{ route('admin.landing-gallery.toggle', $galleryImage) }}" method="POST" class="d-inline-block">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $galleryImage->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                                    <i class="bi bi-eye{{ $galleryImage->is_active ? '-slash' : '' }} me-1"></i>{{ $galleryImage->is_active ? 'Hide' : 'Show' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.landing-gallery.destroy', $galleryImage) }}" method="POST" onsubmit="return confirm('Remove this image from the landing page?');" class="d-inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
                            </form>
                        </div>
                    </div>

                    <div class="modal fade" id="editGalleryModal{{ $galleryImage->id }}" tabindex="-1" aria-labelledby="editGalleryModalLabel{{ $galleryImage->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <h5 class="modal-title" id="editGalleryModalLabel{{ $galleryImage->id }}">Edit landing page item</h5>
                                        <p class="text-muted small mb-0">Replace the image or update the information clients see.</p>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('admin.landing-gallery.update', $galleryImage) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="edit-gallery-preview mb-3">
                                            <img src="{{ asset('storage/' . ltrim($galleryImage->image_path, '/')) }}" alt="{{ $galleryImage->display_name }}">
                                        </div>
                                        <div class="form-grid">
                                            <div class="field-group">
                                                <label for="edit_project_id_{{ $galleryImage->id }}">Project</label>
                                                <select id="edit_project_id_{{ $galleryImage->id }}" name="project_id" class="form-select edit-project-select" data-external-target="edit-external-{{ $galleryImage->id }}" {{ $galleryImage->is_external ? '' : 'required' }}>
                                                    <option value="">Select a completed project</option>
                                                    @foreach($projects as $project)
                                                        <option value="{{ $project->project_id }}" @selected((string) $galleryImage->project_id === (string) $project->project_id)>{{ $project->project_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="field-group">
                                                <label for="edit_image_{{ $galleryImage->id }}">Replace image (optional)</label>
                                                <input id="edit_image_{{ $galleryImage->id }}" name="image" type="file" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp">
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mt-3">
                                            <input class="form-check-input edit-external-toggle" type="checkbox" value="1" id="edit_is_external_{{ $galleryImage->id }}" name="is_external" data-fields-target="edit-external-{{ $galleryImage->id }}" @checked($galleryImage->is_external)>
                                            <label class="form-check-label" for="edit_is_external_{{ $galleryImage->id }}">This is an external / past featured project</label>
                                        </div>
                                        @if(!$galleryImage->is_external && $galleryImage->project)
                                            <div class="alert alert-info d-flex align-items-center justify-content-between gap-2 mt-3 mb-0">
                                                <span><i class="bi bi-info-circle me-1"></i>Project information is managed in Project Management.</span>
                                                <a href="{{ route('admin.projects.show', $galleryImage->project) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success flex-shrink-0">
                                                    <i class="bi bi-pencil me-1"></i>Edit project info
                                                </a>
                                            </div>
                                        @endif
                                        <div id="edit-external-{{ $galleryImage->id }}" class="external-project-fields mt-3" @if(!$galleryImage->is_external) hidden @endif>
                                            <div class="form-grid">
                                                <div class="field-group">
                                                    <label for="edit_external_name_{{ $galleryImage->id }}">Featured project name</label>
                                                    <input id="edit_external_name_{{ $galleryImage->id }}" name="external_project_name" type="text" class="form-control" maxlength="255" value="{{ $galleryImage->external_project_name }}" @required($galleryImage->is_external)>
                                                </div>
                                                <div class="field-group">
                                                    <label for="edit_external_location_{{ $galleryImage->id }}">Location</label>
                                                    <input id="edit_external_location_{{ $galleryImage->id }}" name="external_project_location" type="text" class="form-control" maxlength="255" value="{{ $galleryImage->external_project_location }}">
                                                </div>
                                            </div>
                                            <div class="field-group mt-3">
                                                <label for="edit_external_description_{{ $galleryImage->id }}">Description</label>
                                                <textarea id="edit_external_description_{{ $galleryImage->id }}" name="external_project_description" class="form-control" rows="3" maxlength="2000">{{ $galleryImage->external_project_description }}</textarea>
                                            </div>
                                            <div class="field-group mt-3">
                                                <label for="edit_external_url_{{ $galleryImage->id }}">Project link (optional)</label>
                                                <input id="edit_external_url_{{ $galleryImage->id }}" name="external_project_url" type="url" class="form-control" maxlength="2000" value="{{ $galleryImage->external_project_url }}">
                                            </div>
                                        </div>
                                        <div class="field-group mt-3">
                                            <label for="edit_project_gallery_images_{{ $galleryImage->id }}">Add project gallery images (optional)</label>
                                            <input id="edit_project_gallery_images_{{ $galleryImage->id }}" name="project_gallery_images[]" type="file" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp" multiple>
                                            <div class="form-text">New images are added to the existing project gallery.</div>
                                            @if($galleryImage->projectGalleryImages->count())
                                                <div class="project-gallery-admin-grid mt-2">
                                                    @foreach($galleryImage->projectGalleryImages as $projectImage)
                                                        <img src="{{ asset('storage/' . ltrim($projectImage->image_path, '/')) }}" alt="Project gallery image">
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success"><i class="bi bi-check2 me-1"></i>Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif($fallbackFeaturedProjects->count())
            <div class="gallery-row-list">
                <div class="alert alert-info mb-1">
                    These completed projects are currently shown from the project records. Add them to the carousel to manage their landing-page image separately.
                </div>
                @foreach($fallbackFeaturedProjects as $project)
                    <div class="gallery-row-item" data-gallery-row>
                        <img src="{{ asset('storage/' . ltrim($project->project_image, '/')) }}" alt="{{ $project->project_name }}">
                        <div class="gallery-row-copy">
                            <div class="gallery-row-name">{{ $project->project_name }}</div>
                            <div class="gallery-row-meta">
                                {{ $project->location ?: 'No location provided' }} · Current project image
                            </div>
                            @if($project->description)
                                <div class="gallery-row-description">{{ $project->description }}</div>
                            @endif
                        </div>
                        <div class="gallery-row-actions">
                            <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-pencil me-1"></i>Edit information
                            </a>
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addFeaturedProjectPanel">
                                <i class="bi bi-plus-lg me-1"></i>Add to carousel
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="gallery-empty-state">
                <div class="gallery-empty-illustration" aria-hidden="true">
                    <span class="gallery-empty-frame"></span>
                    <span class="gallery-empty-plus">+</span>
                </div>
                <h4>No images yet</h4>
                <p>Add a completed project and upload images to display them in the landing page gallery.</p>
            </div>
        @endif
    </section>
</div>
@endsection

@push('styles')
<style>
    .landing-gallery-page {
        width: 100%;
        max-width: 100%;
        padding: 6px 0 32px;
    }

    .landing-preview-panel {
        overflow: hidden;
        border: 1px solid rgba(23, 79, 49, 0.12);
        border-radius: 18px;
        background: #f3f7f4;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.045);
    }

    .landing-preview-panel__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem;
        background: #fff;
    }

    .landing-preview-panel__header h3 {
        margin: 0;
        color: #1b3d30;
        font-size: 1.1rem;
        font-weight: 800;
    }

    .landing-preview-panel__header p {
        margin: 0.35rem 0 0;
        color: #62766a;
        font-size: 0.82rem;
    }

    .landing-preview-frame {
        position: relative;
        height: min(72vh, 760px);
        min-height: 520px;
        background: #fff;
    }

    .landing-preview-frame iframe {
        display: block;
        width: 100%;
        height: 100%;
        border: 0;
        background: #fff;
    }

    .landing-preview-loading {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        color: #315c35;
        font-size: 0.9rem;
        transition: opacity 0.2s ease;
    }

    .landing-preview-loading.is-ready {
        pointer-events: none;
        opacity: 0;
    }

    .ug-hero-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 1rem;
        padding: 1.05rem 1.15rem;
        border: 1px solid rgba(22, 101, 52, 0.10);
        border-radius: 18px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fdf9 100%);
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.045);
    }

    .dashboard-title-area h2,
    .dashboard-title-area h2 * {
        font-family: 'Syne', 'Plus Jakarta Sans', 'Helvetica Neue', Arial, sans-serif !important;
        font-size: 28px !important;
        font-weight: 600 !important;
        color: #111827 !important;
        letter-spacing: -0.02em !important;
        margin-bottom: 6px !important;
    }

    .dashboard-title-area p {
        max-width: 760px;
        margin-top: 12px;
        margin-bottom: 0;
        color: #64748b;
        font-size: 0.8rem;
        line-height: 1.5;
    }

    .ug-add-user-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.65rem;
        min-height: 46px;
        padding: 0.78rem 1.05rem;
        border: 0;
        border-radius: 14px;
        background: #1d7b4c;
        color: #ffffff;
        font-weight: 800;
        line-height: 1.15;
        box-shadow: 0 12px 22px rgba(22, 101, 52, 0.20);
        transition: transform 0.16s ease, box-shadow 0.16s ease;
    }

    .ug-add-user-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 28px rgba(22, 101, 52, 0.24);
    }

    .ug-add-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.14);
        flex-shrink: 0;
    }

    .landing-gallery-panel {
        background: #f6f8f7;
        border: 1px solid rgba(30, 76, 49, 0.08);
        border-radius: 18px;
        box-shadow: none;
        padding: 18px 18px 16px;
        margin-top: 0;
    }

    .landing-gallery-panel + .landing-gallery-panel {
        margin-top: 22px;
    }

    .landing-gallery-panel__title-wrap,
    .panel-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .landing-gallery-panel__title-copy {
        display: flex;
        align-items: center;
        min-width: 0;
    }

    .external-project-toggle {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
        padding: 10px 12px;
        border: 1px solid rgba(30, 76, 49, 0.12);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.72);
        color: #234936;
        font-size: 0.82rem;
        font-weight: 600;
        line-height: 1.35;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .external-project-toggle:hover {
        border-color: rgba(30, 76, 49, 0.22);
        box-shadow: 0 8px 18px rgba(20, 61, 44, 0.06);
    }

    .external-project-toggle .form-check-input {
        width: 18px;
        height: 18px;
        margin-top: 0;
        border-color: rgba(28, 118, 77, 0.45);
        background-color: #fff;
        box-shadow: none;
    }

    .external-project-toggle .form-check-input:checked {
        background-color: #1d7b4c;
        border-color: #1d7b4c;
    }

    .external-project-toggle span {
        color: #2b4d41;
        font-weight: 600;
    }

    .panel-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: rgba(20, 119, 67, 0.12);
        color: #1b6548;
        font-size: 1rem;
    }

    .panel-icon--sm {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        font-size: 0.95rem;
    }

    .landing-gallery-panel__title-wrap h2,
    .landing-gallery-panel__title-wrap h3,
    .landing-gallery-panel h2,
    .landing-gallery-panel h3 {
        margin: 0;
        color: #1b3d30;
        font-size: 1.15rem;
        line-height: 1.2;
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .landing-gallery-panel__title-wrap h3,
    .landing-gallery-panel h3 {
        font-size: 1.05rem;
        letter-spacing: -0.02em;
        font-weight: 800;
    }

    .landing-gallery-form {
        margin-top: 10px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(0, 1.35fr);
        gap: 18px;
        align-items: end;
    }

    #addFeaturedProjectPanel .modal-dialog {
        max-width: 900px;
        max-height: calc(100vh - 2rem);
    }

    #addFeaturedProjectPanel .modal-content {
        max-height: calc(100vh - 2rem);
    }

    #addFeaturedProjectPanel .landing-gallery-form {
        display: flex;
        flex: 1 1 auto;
        min-height: 0;
        flex-direction: column;
    }

    #addFeaturedProjectPanel .landing-gallery-form > .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
    }

    #addFeaturedProjectPanel .landing-gallery-form > .modal-body > .form-grid > #externalProjectFields {
        grid-column: 1 / -1;
    }

    #addFeaturedProjectPanel textarea.form-control {
        height: auto;
        min-height: 92px;
        resize: vertical;
    }

    .field-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .field-group label {
        color: #1f3b2d;
        font-size: 0.92rem;
        font-weight: 700;
    }

    .form-select,
    .form-control {
        height: 54px;
        border: 1px solid rgba(146, 167, 156, 0.65);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.7);
        color: #123120;
        font-size: 1rem;
        box-shadow: none;
    }

    .form-select:focus,
    .form-control:focus {
        border-color: rgba(47, 117, 78, 0.7);
        box-shadow: 0 0 0 0.2rem rgba(47, 117, 78, 0.12);
    }

    .upload-field-wrap {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }

    .upload-field-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(25, 82, 58, 0.8);
        font-size: 1.1rem;
        z-index: 1;
    }

    .upload-field-wrap .form-control {
        width: 100%;
        padding-left: 42px;
        padding-right: 128px;
    }

    .upload-field-wrap .file-name-display {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        max-width: 110px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: rgba(50, 66, 58, 0.78);
        font-size: 0.8rem;
        font-weight: 600;
        pointer-events: none;
    }

    .field-error {
        margin-top: 10px;
        color: #d13d3d;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .landing-gallery-panel--list {
        padding-bottom: 0;
    }

    .landing-gallery-panel__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
    }

    .gallery-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 430px;
        text-align: center;
        background: rgba(230, 239, 235, 0.62);
        border: 1px solid rgba(42, 79, 58, 0.08);
        border-radius: 16px;
        padding: 30px 20px;
    }

    .gallery-empty-illustration {
        position: relative;
        width: 142px;
        height: 118px;
        margin-bottom: 18px;
    }

    .gallery-empty-frame {
        position: absolute;
        inset: 16px 18px 18px 16px;
        border-radius: 12px;
        border: 3px solid rgba(18, 93, 64, 0.9);
        background: linear-gradient(135deg, rgba(160, 200, 171, 0.48) 0%, rgba(231, 244, 236, 0.85) 100%);
        box-shadow: inset 0 0 0 8px rgba(19, 95, 69, 0.05);
    }

    .gallery-empty-frame::before,
    .gallery-empty-frame::after {
        content: "";
        position: absolute;
        background: rgba(20, 120, 82, 0.28);
    }

    .gallery-empty-frame::before {
        left: 18px;
        right: 18px;
        bottom: 18px;
        height: 18px;
        border-radius: 6px;
    }

    .gallery-empty-frame::after {
        left: 24px;
        top: 22px;
        width: 40px;
        height: 26px;
        border-radius: 8px;
        background: rgba(20, 120, 82, 0.36);
    }

    .gallery-empty-plus {
        position: absolute;
        right: 3px;
        bottom: 0;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(180deg, #1b7d56 0%, #166b49 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.1rem;
        line-height: 1;
        box-shadow: 0 10px 18px rgba(18, 99, 69, 0.2);
    }

    .gallery-empty-state h4 {
        margin: 0;
        color: #153b2b;
        font-size: 1.1rem;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .gallery-empty-state p {
        max-width: 430px;
        margin: 10px auto 0;
        color: #587065;
        font-size: 0.96rem;
        line-height: 1.6;
    }

    .gallery-row-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .gallery-row-item {
        display: flex;
        align-items: center;
        gap: 18px;
        background: rgba(255, 255, 255, 0.76);
        border: 1px solid rgba(23, 79, 49, 0.09);
        border-radius: 14px;
        padding: 14px 16px;
    }

    .gallery-image-button {
        position: relative;
        flex: 0 0 auto;
        padding: 0;
        border: 0;
        border-radius: 12px;
        background: transparent;
        overflow: hidden;
        cursor: pointer;
    }

    .gallery-image-button img {
        display: block;
        width: 132px;
        height: 94px;
        object-fit: cover;
        border-radius: 12px;
    }

    .gallery-image-edit-hint {
        position: absolute;
        inset: auto 0 0;
        padding: 0.3rem;
        background: rgba(16, 61, 39, 0.86);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .gallery-image-button:hover .gallery-image-edit-hint,
    .gallery-image-button:focus-visible .gallery-image-edit-hint {
        opacity: 1;
    }

    .external-project-fields {
        padding: 1rem;
        border: 1px solid rgba(23, 79, 49, 0.12);
        border-radius: 14px;
        background: rgba(244, 250, 246, 0.82);
    }

    .project-info-preview {
        padding: 1rem;
        border: 1px solid rgba(23, 79, 49, 0.14);
        border-radius: 14px;
        background: #f4faf6;
    }

    .project-info-preview__header {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 0.8rem;
    }

    .project-info-preview__header span,
    .project-info-preview__grid span,
    .project-info-preview__lists > div > span {
        color: #61756a;
        font-size: 0.78rem;
    }

    .project-info-preview__grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 0.8rem;
    }

    .project-info-preview__grid div {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .project-info-preview__lists {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 0.8rem;
    }

    .project-info-preview ul {
        margin: 0.25rem 0 0;
        padding-left: 1.1rem;
    }

    .edit-gallery-preview {
        overflow: hidden;
        border-radius: 14px;
        background: #edf4ef;
    }

    .edit-gallery-preview img {
        display: block;
        width: 100%;
        max-height: 280px;
        object-fit: cover;
    }

    .project-gallery-admin-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 8px;
    }

    .project-gallery-admin-grid img {
        width: 100%;
        height: 72px;
        border-radius: 8px;
        object-fit: cover;
    }

    .gallery-row-item img {
        width: 118px;
        height: 82px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid rgba(17, 62, 42, 0.08);
        flex-shrink: 0;
    }

    .gallery-row-copy {
        flex: 1 1 auto;
        min-width: 0;
    }

    .gallery-row-name {
        color: #16392d;
        font-size: 1rem;
        font-weight: 700;
    }

    .gallery-row-meta {
        margin-top: 2px;
        color: #64776b;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .gallery-row-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    @media (max-width: 1200px) {
        .ug-hero-card {
            grid-template-columns: 1fr;
            align-items: flex-start;
        }

        .ug-add-user-btn {
            justify-self: flex-start;
        }
    }

    @media (max-width: 900px) {
        .ug-hero-card {
            min-height: auto !important;
            padding: 0.7rem 0.9rem;
            gap: 0.45rem;
        }

        .dashboard-title-area {
            min-width: 0;
            max-width: 100%;
        }

        .dashboard-title-area h2,
        .dashboard-title-area h2 * {
            font-size: 1.45rem !important;
            line-height: 1.25 !important;
            word-break: break-word;
            overflow-wrap: anywhere;
            white-space: normal;
        }

        .dashboard-title-area p {
            font-size: 0.74rem;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .gallery-row-item {
            flex-wrap: wrap;
        }

        .gallery-row-actions {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media (max-width: 640px) {
        #addFeaturedProjectPanel .modal-dialog {
            width: calc(100% - 1rem);
            max-width: none;
            margin: 0.5rem auto;
        }

        #addFeaturedProjectPanel .landing-gallery-form > .modal-body > .form-grid > #externalProjectFields {
            grid-column: auto;
        }

        #addFeaturedProjectPanel .modal-content {
            max-height: calc(100dvh - 1rem);
            border-radius: 16px;
        }

        #addFeaturedProjectPanel .landing-gallery-form > .modal-body {
            -webkit-overflow-scrolling: touch;
        }

        #addFeaturedProjectPanel .modal-body {
            padding: 1rem;
        }

        #addFeaturedProjectPanel .project-info-preview__grid,
        #addFeaturedProjectPanel .project-info-preview__lists {
            grid-template-columns: 1fr 1fr;
        }

        #addFeaturedProjectPanel .modal-footer {
            padding: 0.8rem 1rem;
        }

        #addFeaturedProjectPanel .modal-footer .btn {
            flex: 1 1 0;
        }

        .landing-gallery-page {
            padding-top: 4px;
        }

        .ug-hero-card {
            min-height: auto !important;
            padding: 0.7rem 0.8rem;
        }

        .dashboard-title-area {
            width: 100%;
        }

        .dashboard-title-area h2,
        .dashboard-title-area h2 * {
            font-size: 1.05rem !important;
            line-height: 1.35 !important;
            word-break: break-word;
            overflow-wrap: anywhere;
            white-space: normal;
        }

        .dashboard-title-area p {
            font-size: 0.72rem;
        }

        .ug-add-user-btn {
            width: 100%;
        }

        .landing-gallery-panel {
            padding: 14px 12px 12px;
        }

        .gallery-empty-state {
            min-height: 300px;
            padding: 20px 16px;
        }

        .gallery-row-item {
            display: grid;
            grid-template-columns: 96px 1fr;
            align-items: center;
            gap: 12px;
            padding: 12px;
        }

        .gallery-row-item img {
            width: 96px;
            height: 72px;
        }

        .gallery-row-copy {
            min-width: 0;
        }

        .gallery-row-actions {
            grid-column: 1 / -1;
            justify-content: flex-start;
        }
    }

    @media (max-width: 575px) {
        .ug-hero-card {
            grid-template-columns: 1fr !important;
        }

        .dashboard-title-area {
            width: 100%;
            min-width: 0;
            max-width: 100%;
            overflow: visible !important;
        }

        .dashboard-title-area h2,
        .dashboard-title-area h2 * {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            font-size: 0.9rem !important;
            line-height: 1.45 !important;
            letter-spacing: -0.02em !important;
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
    }

    @media (max-width: 480px) {
        .upload-field-wrap {
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        .upload-field-icon {
            display: none;
        }

        .upload-field-wrap .form-control {
            padding-left: 14px;
            padding-right: 14px;
        }

        .upload-field-wrap .file-name-display {
            position: static;
            transform: none;
            display: block;
            width: 100%;
            max-width: none;
            margin-top: 10px;
            padding-left: 4px;
            text-align: left;
        }

        .gallery-row-item {
            grid-template-columns: 1fr;
        }

        .gallery-row-item img {
            width: 100%;
            height: 180px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('image');
        const externalToggle = document.getElementById('is_external');
        const projectSelect = document.getElementById('project_id');
        const externalProjectFields = document.getElementById('externalProjectFields');
        const externalProjectName = document.getElementById('external_project_name');
        const systemProjectInfo = document.getElementById('systemProjectInfo');
        const landingPagePreview = document.getElementById('landingPagePreview');
        const landingPreviewLoading = document.getElementById('landingPreviewLoading');

        const syncProjectRequirement = () => {
            if (!externalToggle || !projectSelect) {
                return;
            }

            projectSelect.required = !externalToggle.checked;
            projectSelect.disabled = externalToggle.checked;
            if (externalProjectFields) {
                externalProjectFields.hidden = !externalToggle.checked;
            }
            if (externalProjectName) {
                externalProjectName.required = externalToggle.checked;
            }
            if (externalToggle.checked) {
                projectSelect.value = '';
            }

            if (systemProjectInfo) {
                systemProjectInfo.hidden = externalToggle.checked || !projectSelect.value;
            }
        };

        const updateSystemProjectInfo = () => {
            if (!projectSelect || !systemProjectInfo || externalToggle?.checked) {
                return;
            }

            const option = projectSelect.selectedOptions[0];
            if (!option || !option.value) {
                systemProjectInfo.hidden = true;
                return;
            }

            systemProjectInfo.hidden = false;
            const getInfo = name => systemProjectInfo.querySelector(`[data-project-info="${name}"]`);
            const setText = (name, value, fallback = '-') => {
                const target = getInfo(name);
                if (target) target.textContent = value || fallback;
            };
            setText('location', option.dataset.projectLocation);
            setText('bedrooms', option.dataset.projectBedrooms);
            setText('bathrooms', option.dataset.projectBathrooms);
            setText('lot_area', option.dataset.projectLotArea ? `${option.dataset.projectLotArea} sqm` : '');
            setText('description', option.dataset.projectDescription, 'No description provided.');

            ['highlights', 'features'].forEach(name => {
                const list = systemProjectInfo.querySelector(`[data-project-info-list="${name}"]`);
                if (!list) return;
                let items = [];
                try {
                    items = JSON.parse(option.dataset[`project${name[0].toUpperCase()}${name.slice(1)}`] || '[]');
                } catch {
                    items = [];
                }
                list.innerHTML = items.length
                    ? items.map(item => `<li>${String(item).replace(/[&<>"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[character]))}</li>`).join('')
                    : '<li>-</li>';
            });
        };

        projectSelect?.addEventListener('change', updateSystemProjectInfo);

        if (externalToggle) {
            externalToggle.addEventListener('change', syncProjectRequirement);
            syncProjectRequirement();
            updateSystemProjectInfo();
        }

        landingPagePreview?.addEventListener('load', function () {
            landingPreviewLoading?.classList.add('is-ready');

            try {
                const previewDocument = landingPagePreview.contentDocument;
                previewDocument.querySelectorAll('[data-landing-hero-image]').forEach((image) => {
                    image.style.cursor = 'pointer';
                    image.title = 'Click to replace the hero image';
                    image.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        const heroModal = document.getElementById('editHeroModal');
                        if (heroModal && window.bootstrap) {
                            bootstrap.Modal.getOrCreateInstance(heroModal).show();
                        }
                    });
                });

                previewDocument.querySelectorAll('[data-gallery-image-id]').forEach((image) => {
                    image.style.cursor = 'pointer';
                    image.title = 'Click to edit this featured project';
                    image.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        const galleryId = image.dataset.galleryImageId;
                        const editModal = document.getElementById(`editGalleryModal${galleryId}`);
                        if (editModal && window.bootstrap) {
                            bootstrap.Modal.getOrCreateInstance(editModal).show();
                        }
                    });
                });
            } catch (error) {
                console.warn('Landing page preview editing is unavailable.', error);
            }
        });

        document.querySelectorAll('.edit-external-toggle').forEach(toggle => {
            const fields = document.getElementById(toggle.dataset.fieldsTarget);
            const project = document.querySelector(`[data-external-target="${toggle.dataset.fieldsTarget}"]`);
            const name = fields?.querySelector('[name="external_project_name"]');

            const syncEditFields = () => {
                const isExternal = toggle.checked;
                if (fields) {
                    fields.hidden = !isExternal;
                }
                if (project) {
                    project.required = !isExternal;
                    project.disabled = isExternal;
                }
                if (name) {
                    name.required = isExternal;
                }
            };

            toggle.addEventListener('change', syncEditFields);
            syncEditFields();
        });

        if (fileInput) {
            const target = fileInput.closest('.upload-field-wrap');
            if (target) {
                let label = target.querySelector('.file-name-display');
                if (!label) {
                    label = document.createElement('span');
                    label.className = 'file-name-display';
                    target.appendChild(label);
                }
                label.textContent = fileInput.files && fileInput.files.length ? fileInput.files[0].name : 'No file chosen';
            }

            fileInput.addEventListener('change', function () {
                const fileName = this.files && this.files.length ? this.files[0].name : 'No file chosen';
                const target = this.closest('.upload-field-wrap');
                if (!target) {
                    return;
                }

                let label = target.querySelector('.file-name-display');
                if (!label) {
                    label = document.createElement('span');
                    label.className = 'file-name-display';
                    target.appendChild(label);
                }

                label.textContent = fileName;
            });
        }

        const swalError = document.querySelector('[data-swal-error]');
        if (swalError) {
            Swal.fire({
                icon: 'error',
                title: 'Notice',
                text: swalError.dataset.swalError,
                confirmButtonColor: '#1d7b4c',
                confirmButtonText: 'OK'
            });
        }

        const swalSuccess = document.querySelector('[data-swal-success]');
        if (swalSuccess) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: swalSuccess.dataset.swalSuccess,
                confirmButtonColor: '#1d7b4c',
                confirmButtonText: 'OK'
            });
        }

        document.querySelectorAll('[data-move-gallery]').forEach(button => {
            button.addEventListener('click', () => {
                const row = button.closest('[data-gallery-row]');
                if (!row) {
                    return;
                }

                const sibling = button.dataset.moveGallery === 'up' ? row.previousElementSibling : row.nextElementSibling;

                if (!sibling || !sibling.matches('[data-gallery-row]')) {
                    return;
                }

                if (button.dataset.moveGallery === 'up') {
                    row.parentElement.insertBefore(row, sibling);
                } else {
                    row.parentElement.insertBefore(sibling, row);
                }
            });
        });
    });
</script>
@endpush