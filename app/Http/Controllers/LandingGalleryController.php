<?php

namespace App\Http\Controllers;

use App\Models\LandingGalleryImage;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandingGalleryController extends Controller
{
    public function index()
    {
        $galleryImages = LandingGalleryImage::with('project')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $projects = Project::query()
            ->whereIn('status', Project::statusVariants(Project::STATUS_COMPLETED))
            ->orderBy('project_name')
            ->get(['project_id', 'project_name']);

        return view('admin.gallery.index', compact('galleryImages', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,project_id'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $project = Project::findOrFail($validated['project_id']);
        if ($project->workflowStatus() !== Project::STATUS_COMPLETED) {
            return back()->withInput()->withErrors(['project_id' => 'Only completed projects can be added to the landing page gallery.']);
        }

        $sortOrder = ((int) LandingGalleryImage::max('sort_order')) + 1;
        LandingGalleryImage::create([
            'project_id' => $validated['project_id'],
            'image_path' => $request->file('image')->store('landing-gallery', 'public'),
            'sort_order' => $sortOrder,
            'is_active' => true,
        ]);

        return back()->with('success', 'Landing page gallery image added.');
    }

    public function destroy(LandingGalleryImage $galleryImage)
    {
        Storage::disk('public')->delete($galleryImage->image_path);
        $galleryImage->delete();

        return back()->with('success', 'Landing page gallery image removed.');
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
        abort_unless(
            $project->workflowStatus() === Project::STATUS_COMPLETED
                && LandingGalleryImage::where('project_id', $project->project_id)
                    ->where('is_active', true)
                    ->exists(),
            404
        );

        return response()->json([
            'name' => $project->project_name,
            'location' => $project->location,
            'completion_date' => $project->actual_end_date?->format('F j, Y'),
            'description' => $project->description,
            'status' => Project::statusLabel($project->status),
            'image' => $project->image_url,
        ]);
    }
}