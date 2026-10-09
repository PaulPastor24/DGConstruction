<?php

namespace App\Http\Controllers;

use App\Models\LandingGalleryImage;
use App\Models\LandingGalleryProjectImage;
use App\Models\LandingPageSetting;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class LandingGalleryController extends Controller
{
    protected function landingGalleryHasColumn(string $column): bool
    {
        return Schema::hasTable('landing_gallery_images') && Schema::hasColumn('landing_gallery_images', $column);
    }

    public function index()
    {
        $galleryImages = collect();

        if (Schema::hasTable('landing_gallery_images')) {
            $relations = ['project'];
            if (Schema::hasTable('landing_gallery_project_images')) {
                $relations[] = 'projectGalleryImages';
            }
            $galleryImages = LandingGalleryImage::with($relations)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        }

        $projects = Project::query()
            ->whereNotNull('project_name')
            ->orderBy('project_name')
            ->get([
                'project_id',
                'project_name',
                'location',
                'description',
                'bedrooms',
                'bathrooms',
                'lot_area',
                'highlights',
                'features',
            ]);

        $fallbackFeaturedProjects = collect();
        if ($galleryImages->isEmpty()) {
            $fallbackFeaturedProjects = Project::query()
                ->whereIn('status', Project::statusVariants(Project::STATUS_COMPLETED))
                ->whereNotNull('project_image')
                ->whereNotNull('project_name')
                ->orderBy('project_name')
                ->get(['project_id', 'project_name', 'location', 'project_image', 'description']);
        }

        $landingPageSetting = Schema::hasTable('landing_page_settings')
            ? LandingPageSetting::query()->first()
            : null;

        return view('admin.gallery.index', compact('galleryImages', 'projects', 'fallbackFeaturedProjects', 'landingPageSetting'));
    }

    public function updateHero(Request $request)
    {
        abort_unless(Schema::hasTable('landing_page_settings'), 404);

        $validated = $request->validate([
            'hero_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $setting = LandingPageSetting::query()->firstOrCreate([]);
        $oldImagePath = $setting->hero_image_path;
        $newImagePath = $request->file('hero_image')->store('landing-page', 'public');

        $setting->update(['hero_image_path' => $newImagePath]);

        if ($oldImagePath && $oldImagePath !== $newImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return back()->with('success', 'Landing page hero image updated.');
    }

    public function store(Request $request)
    {
        $rules = [
            'project_id' => ['nullable', 'integer', 'exists:projects,project_id'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'project_gallery_images' => ['nullable', 'array', 'max:12'],
            'project_gallery_images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];

        if ($this->landingGalleryHasColumn('is_external')) {
            $rules['is_external'] = ['nullable', 'boolean'];
        }

        if ($this->landingGalleryHasColumn('external_project_name')) {
            $rules['external_project_name'] = ['nullable', 'string', 'max:255'];
        }

        if ($this->landingGalleryHasColumn('external_project_location')) {
            $rules['external_project_location'] = ['nullable', 'string', 'max:255'];
        }

        if ($this->landingGalleryHasColumn('external_project_description')) {
            $rules['external_project_description'] = ['nullable', 'string', 'max:2000'];
        }

        if ($this->landingGalleryHasColumn('external_project_url')) {
            $rules['external_project_url'] = ['nullable', 'url', 'max:2000'];
        }
        $rules += $this->externalDetailRules();

        $validated = $request->validate($rules);

        $hasExternalColumns = $this->landingGalleryHasColumn('is_external')
            && $this->landingGalleryHasColumn('external_project_name')
            && $this->landingGalleryHasColumn('external_project_location')
            && $this->landingGalleryHasColumn('external_project_description')
            && $this->landingGalleryHasColumn('external_project_url');

        $isExternal = $hasExternalColumns && (bool) ($validated['is_external'] ?? false);

        if (! $isExternal && empty($validated['project_id'])) {
            return back()->withInput()->withErrors([
                'project_id' => 'Please select a project or mark this as an external featured project.',
            ]);
        }

        if ($isExternal) {
            $validated['project_id'] = null;
            if ($this->landingGalleryHasColumn('external_project_name')
                && blank($validated['external_project_name'] ?? null)) {
                return back()->withInput()->withErrors([
                    'external_project_name' => 'Enter a name for the external featured project.',
                ]);
            }
        } else {
            $project = Project::findOrFail($validated['project_id']);
            if ($project->workflowStatus() !== Project::STATUS_COMPLETED) {
                return back()->withInput()->withErrors([
                    'project_id' => 'Only completed projects can be added to the landing page gallery.',
                ]);
            }
        }

        $galleryData = [
            'project_id' => $validated['project_id'] ?? null,
            'image_path' => $request->file('image')->store('landing-gallery', 'public'),
            'sort_order' => ((int) LandingGalleryImage::max('sort_order')) + 1,
            'is_active' => true,
        ];

        if ($this->landingGalleryHasColumn('is_external')) {
            $galleryData['is_external'] = $isExternal;
        }

        if ($this->landingGalleryHasColumn('external_project_name')) {
            $galleryData['external_project_name'] = $validated['external_project_name'] ?? null;
        }

        if ($this->landingGalleryHasColumn('external_project_location')) {
            $galleryData['external_project_location'] = $validated['external_project_location'] ?? null;
        }

        if ($this->landingGalleryHasColumn('external_project_description')) {
            $galleryData['external_project_description'] = $validated['external_project_description'] ?? null;
        }

        if ($this->landingGalleryHasColumn('external_project_url')) {
            $galleryData['external_project_url'] = $validated['external_project_url'] ?? null;
        }
        $galleryData += $this->externalDetailData($validated);

        $galleryImage = LandingGalleryImage::create($galleryData);
        $this->storeProjectGalleryImages($request, $galleryImage);

        return back()->with('success', $isExternal ? 'External featured project added to landing page.' : 'Landing page gallery image added.');
    }

    public function destroy(LandingGalleryImage $galleryImage)
    {
        Storage::disk('public')->delete($galleryImage->image_path);
        if (Schema::hasTable('landing_gallery_project_images')) {
            foreach ($galleryImage->projectGalleryImages as $projectImage) {
                Storage::disk('public')->delete($projectImage->image_path);
                $projectImage->delete();
            }
        }
        $galleryImage->delete();

        return back()->with('success', 'Landing page gallery image removed.');
    }

    public function update(Request $request, LandingGalleryImage $galleryImage)
    {
        $rules = [
            'project_id' => ['nullable', 'integer', 'exists:projects,project_id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'project_gallery_images' => ['nullable', 'array', 'max:12'],
            'project_gallery_images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];

        foreach ([
            'external_project_name' => ['nullable', 'string', 'max:255'],
            'external_project_location' => ['nullable', 'string', 'max:255'],
            'external_project_description' => ['nullable', 'string', 'max:2000'],
            'external_project_url' => ['nullable', 'url', 'max:2000'],
        ] as $field => $fieldRules) {
            if ($this->landingGalleryHasColumn($field)) {
                $rules[$field] = $fieldRules;
            }
            $rules += $this->externalDetailRules();
        }

        if ($this->landingGalleryHasColumn('is_external')) {
            $rules['is_external'] = ['nullable', 'boolean'];
        }

        $validated = $request->validate($rules);
        $isExternal = $this->landingGalleryHasColumn('is_external')
            && (bool) ($validated['is_external'] ?? false);

        if (! $isExternal && empty($validated['project_id'])) {
            return back()->withInput()->withErrors([
                'project_id' => 'Please select a project or mark this as an external featured project.',
            ]);
        }

        if ($isExternal) {
            $validated['project_id'] = null;
            if ($this->landingGalleryHasColumn('external_project_name')
                && blank($validated['external_project_name'] ?? null)) {
                return back()->withInput()->withErrors([
                    'external_project_name' => 'Enter a name for the external featured project.',
                ]);
            }
        } else {
            $project = Project::findOrFail($validated['project_id']);
            if ($project->workflowStatus() !== Project::STATUS_COMPLETED) {
                return back()->withInput()->withErrors([
                    'project_id' => 'Only completed projects can be shown on the landing page.',
                ]);
            }
        }

        $oldImagePath = $galleryImage->image_path;
        $galleryData = ['project_id' => $validated['project_id'] ?? null];

        if ($request->hasFile('image')) {
            $galleryData['image_path'] = $request->file('image')->store('landing-gallery', 'public');
        }

        if ($this->landingGalleryHasColumn('is_external')) {
            $galleryData['is_external'] = $isExternal;
        }

        foreach ([
            'external_project_name',
            'external_project_location',
            'external_project_description',
            'external_project_url',
        ] as $field) {
            if ($this->landingGalleryHasColumn($field)) {
                $galleryData[$field] = $validated[$field] ?? null;
            }
            $galleryData += $this->externalDetailData($validated);
        }

        $galleryImage->update($galleryData);
        $this->storeProjectGalleryImages($request, $galleryImage);

        if (isset($galleryData['image_path']) && $oldImagePath !== $galleryData['image_path']) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return back()->with('success', 'Landing page featured item updated.');
    }

    protected function storeProjectGalleryImages(Request $request, LandingGalleryImage $galleryImage): void
    {
        if (! Schema::hasTable('landing_gallery_project_images') || ! $request->hasFile('project_gallery_images')) {
            return;
        }

        $nextSortOrder = (int) $galleryImage->projectGalleryImages()->max('sort_order') + 1;
        foreach ($request->file('project_gallery_images', []) as $image) {
            LandingGalleryProjectImage::create([
                'landing_gallery_image_id' => $galleryImage->id,
                'image_path' => $image->store('landing-gallery/projects', 'public'),
                'sort_order' => $nextSortOrder++,
            ]);
        }
    }

    protected function externalDetailRules(): array
    {
        $rules = [];
        if ($this->landingGalleryHasColumn('external_bedrooms')) {
            $rules['external_bedrooms'] = ['nullable', 'integer', 'min:0', 'max:99'];
        }
        if ($this->landingGalleryHasColumn('external_bathrooms')) {
            $rules['external_bathrooms'] = ['nullable', 'integer', 'min:0', 'max:99'];
        }
        if ($this->landingGalleryHasColumn('external_lot_area')) {
            $rules['external_lot_area'] = ['nullable', 'numeric', 'min:0', 'max:99999999.99'];
        }
        foreach (['external_highlights', 'external_features'] as $field) {
            if ($this->landingGalleryHasColumn($field)) {
                $rules[$field] = ['nullable', 'string', 'max:5000'];
            }
        }

        return $rules;
    }

    protected function externalDetailData(array $validated): array
    {
        $data = [];
        foreach (['external_bedrooms', 'external_bathrooms', 'external_lot_area'] as $field) {
            if ($this->landingGalleryHasColumn($field)) {
                $data[$field] = $validated[$field] ?? null;
            }
        }
        foreach (['external_highlights', 'external_features'] as $field) {
            if ($this->landingGalleryHasColumn($field)) {
                $data[$field] = collect(preg_split('/\r\n|\r|\n/', (string) ($validated[$field] ?? '')))
                    ->map(fn ($item) => trim($item))
                    ->filter()
                    ->values()
                    ->all();
            }
        }

        return $data;
    }

    public function toggle(LandingGalleryImage $galleryImage)
    {
        $galleryImage->update(['is_active' => ! $galleryImage->is_active]);

        return back()->with('success', 'Gallery visibility updated.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:landing_gallery_images,id'],
        ]);

        foreach ($validated['order'] as $sortOrder => $galleryImageId) {
            LandingGalleryImage::whereKey($galleryImageId)->update(['sort_order' => $sortOrder]);
        }

        return back()->with('success', 'Gallery order updated.');
    }

    public function publicProject(Project $project)
    {
        abort_unless($project->workflowStatus() === Project::STATUS_COMPLETED, 404);

        $relationsToLoad = [];

        if (Schema::hasTable('construction_phases')) {
            $relationsToLoad[] = 'phases';
            if (Schema::hasTable('timeline_milestones')) {
                $relationsToLoad[] = 'phases.milestones';
            }
        }

        if (Schema::hasTable('landing_gallery_images')) {
            $relationsToLoad[] = 'landingGalleryImages';
        }

        $project->load($relationsToLoad);

        $galleryImages = collect();
        if (Schema::hasTable('landing_gallery_images') && $project->relationLoaded('landingGalleryImages')) {
            $galleryImages = $project->landingGalleryImages
                ->sortBy('sort_order')
                ->values()
                ->map(fn ($image) => asset('storage/' . ltrim($image->image_path, '/')));
        }

        $timeline = collect();
        if ($project->relationLoaded('phases')) {
            $timeline = $project->phases
                ->sortBy('phase_order')
                ->map(fn ($phase) => [
                    'name' => $phase->phase_name,
                    'status' => $phase->status,
                    'start_date' => $phase->planned_start_date?->format('M j, Y'),
                    'end_date' => $phase->planned_end_date?->format('M j, Y'),
                    'milestones' => $phase->relationLoaded('milestones')
                        ? $phase->milestones->map(fn ($milestone) => [
                            'name' => $milestone->milestone_name,
                            'start_date' => $milestone->start_date?->format('M j, Y'),
                            'end_date' => $milestone->end_date?->format('M j, Y'),
                            'is_completed' => $milestone->is_completed,
                            'is_delayed' => $milestone->is_delayed,
                        ])->values()
                        : collect([]),
                ])->values();
        }

        return response()->json([
            'name' => $project->project_name,
            'location' => $project->location,
            'completion_date' => $project->actual_end_date?->format('F j, Y'),
            'description' => $project->description,
            'status' => Project::statusLabel($project->status),
            'image' => $project->image_url,
            'gallery_images' => $galleryImages,
            'bedrooms' => $project->bedrooms,
            'bathrooms' => $project->bathrooms,
            'lot_area' => $project->lot_area ? number_format($project->lot_area, 2) . ' sqm' : null,
            'highlights' => $project->highlights ?? [],
            'features' => $project->features ?? [],
            'timeline' => $timeline,
        ]);
    }

    public function publicDemoProject(string $slug)
    {
        $demoProjects = [
            'modern-residential-home' => [
                'name' => 'Modern Residential Home',
                'location' => 'Quezon City',
                'completion_date' => 'December 2024',
                'description' => 'A modern 2-story residential home with contemporary design and sustainable materials.',
                'status' => 'Completed',
                'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=80',
                    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&q=80',
                    'https://images.unsplash.com/photo-1600585152220-f4c22656ad16?w=1200&q=80',
                ],
                'bedrooms' => 4,
                'bathrooms' => 3,
                'lot_area' => '250.00 sqm',
                'highlights' => [
                    'Modern architectural design',
                    'Sustainable building materials',
                    'Open floor plan',
                    'Smart home integration',
                ],
                'features' => [
                    ' Gourmet kitchen with island',
                    'Master bedroom with walk-in closet',
                    'Covered patio and deck',
                    'Energy-efficient HVAC system',
                ],
                'timeline' => [
                    [
                        'name' => 'Design & Planning',
                        'status' => 'completed',
                        'start_date' => 'Jun 2024',
                        'end_date' => 'Jul 2024',
                        'milestones' => [
                            ['name' => 'Concept Approval', 'start_date' => 'Jun 2024', 'end_date' => 'Jun 2024', 'is_completed' => true, 'is_delayed' => false],
                            ['name' => 'Final Plans', 'start_date' => 'Jul 2024', 'end_date' => 'Jul 2024', 'is_completed' => true, 'is_delayed' => false],
                        ],
                    ],
                    [
                        'name' => 'Construction',
                        'status' => 'completed',
                        'start_date' => 'Aug 2024',
                        'end_date' => 'Nov 2024',
                        'milestones' => [
                            ['name' => 'Foundation', 'start_date' => 'Aug 2024', 'end_date' => 'Sep 2024', 'is_completed' => true, 'is_delayed' => false],
                            ['name' => 'Framing', 'start_date' => 'Sep 2024', 'end_date' => 'Oct 2024', 'is_completed' => true, 'is_delayed' => false],
                            ['name' => 'Finishing', 'start_date' => 'Oct 2024', 'end_date' => 'Nov 2024', 'is_completed' => true, 'is_delayed' => false],
                        ],
                    ],
                ],
            ],
            'commercial-office-building' => [
                'name' => 'Commercial Office Building',
                'location' => 'Makati City',
                'completion_date' => 'August 2024',
                'description' => 'A 5-story commercial office building with modern amenities and efficient workspace design.',
                'status' => 'Completed',
                'image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1200&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1200&q=80',
                    'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1200&q=80',
                ],
                'bedrooms' => null,
                'bathrooms' => null,
                'lot_area' => '850.00 sqm',
                'highlights' => [
                    '5-story commercial structure',
                    'Energy-efficient curtain wall',
                    'Underground parking',
                    'LEED-certified design',
                ],
                'features' => [
                    'High-speed elevators',
                    'Conference rooms',
                    'Rooftop garden',
                    '24/7 security system',
                ],
                'timeline' => [
                    [
                        'name' => 'Design & Planning',
                        'status' => 'completed',
                        'start_date' => 'Jan 2024',
                        'end_date' => 'Mar 2024',
                        'milestones' => [
                            ['name' => 'Site Survey', 'start_date' => 'Jan 2024', 'end_date' => 'Jan 2024', 'is_completed' => true, 'is_delayed' => false],
                            ['name' => 'Permits', 'start_date' => 'Feb 2024', 'end_date' => 'Mar 2024', 'is_completed' => true, 'is_delayed' => true],
                        ],
                    ],
                    [
                        'name' => 'Construction',
                        'status' => 'completed',
                        'start_date' => 'Apr 2024',
                        'end_date' => 'Aug 2024',
                        'milestones' => [
                            ['name' => 'Excavation', 'start_date' => 'Apr 2024', 'end_date' => 'May 2024', 'is_completed' => true, 'is_delayed' => false],
                            ['name' => 'Structure', 'start_date' => 'May 2024', 'end_date' => 'Jul 2024', 'is_completed' => true, 'is_delayed' => false],
                        ],
                    ],
                ],
            ],
            'luxury-villa-renovation' => [
                'name' => 'Luxury Villa Renovation',
                'location' => 'Tagaytay',
                'completion_date' => 'March 2025',
                'description' => 'Complete renovation of a luxury villa featuring modern interiors and landscape design.',
                'status' => 'Completed',
                'image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&q=80',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&q=80',
                    'https://images.unsplash.com/photo-1600585152220-f4c22656ad16?w=1200&q=80',
                ],
                'bedrooms' => 5,
                'bathrooms' => 4,
                'lot_area' => '420.00 sqm',
                'highlights' => [
                    'Complete interior overhaul',
                    'Modern landscape design',
                    'Infinity pool',
                    'Smart home automation',
                ],
                'features' => [
                    'Home theater',
                    'Wine cellar',
                    'Outdoor kitchen',
                    'Panoramic glass walls',
                ],
                'timeline' => [
                    [
                        'name' => 'Design & Planning',
                        'status' => 'completed',
                        'start_date' => 'Oct 2024',
                        'end_date' => 'Nov 2024',
                        'milestones' => [
                            ['name' => 'Concept', 'start_date' => 'Oct 2024', 'end_date' => 'Oct 2024', 'is_completed' => true, 'is_delayed' => false],
                            ['name' => 'Approvals', 'start_date' => 'Nov 2024', 'end_date' => 'Nov 2024', 'is_completed' => true, 'is_delayed' => false],
                        ],
                    ],
                    [
                        'name' => 'Construction',
                        'status' => 'completed',
                        'start_date' => 'Dec 2024',
                        'end_date' => 'Mar 2025',
                        'milestones' => [
                            ['name' => 'Demolition', 'start_date' => 'Dec 2024', 'end_date' => 'Dec 2024', 'is_completed' => true, 'is_delayed' => false],
                            ['name' => 'Rebuild', 'start_date' => 'Jan 2025', 'end_date' => 'Feb 2025', 'is_completed' => true, 'is_delayed' => false],
                            ['name' => 'Finishing', 'start_date' => 'Feb 2025', 'end_date' => 'Mar 2025', 'is_completed' => true, 'is_delayed' => false],
                        ],
                    ],
                ],
            ],
        ];

        $project = $demoProjects[$slug] ?? null;

        abort_if(! $project, 404);

        return response()->json($project);
    }
}