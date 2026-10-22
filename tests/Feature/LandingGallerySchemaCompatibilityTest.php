<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandingGallerySchemaCompatibilityTest extends TestCase
{
    public function test_create_and_update_work_before_and_after_label_migration(): void
    {
        $this->withoutMiddleware([
            \Illuminate\Auth\Middleware\Authenticate::class,
            \App\Http\Middleware\RoleMiddleware::class,
            \App\Http\Middleware\TrackUserPresence::class,
        ]);
        Storage::fake('public');
        Schema::create('projects', function ($table) {
            $table->increments('project_id');
        });
        foreach ([
            '2026_09_15_000001_create_landing_gallery_images_table.php',
            '2026_09_25_000001_add_external_project_fields_to_landing_gallery_images_table.php',
            '2026_10_09_000001_make_landing_gallery_project_nullable.php',
            '2026_10_09_000004_create_landing_gallery_project_images_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }

        foreach ([false, true] as $withLabels) {
            if ($withLabels) {
                Schema::table('landing_gallery_images', fn ($table) => $table->string('image_label')->nullable());
                Schema::table('landing_gallery_project_images', fn ($table) => $table->string('label')->nullable());
            }
            $data = [
                'is_external' => '1',
                'external_project_name' => 'Featured house',
                'image_label' => 'Front view',
                'image' => $this->image(),
                'project_gallery_images' => [$this->image()],
                'project_gallery_labels' => 'Kitchen',
            ];
            $this->post(route('admin.landing-gallery.store'), $data)
                ->assertRedirect()->assertSessionHasNoErrors();
            $gallery = \App\Models\LandingGalleryImage::latest('id')->firstOrFail();
            Storage::disk('public')->assertExists($gallery->image_path);
            $this->assertSame('Featured house', $gallery->external_project_name);
            $this->assertNull($gallery->project_id);
            $this->assertCount(1, $gallery->projectGalleryImages);

            unset($data['image'], $data['project_gallery_images']);
            $data['external_project_name'] = 'Updated house';
            $data['existing_image_labels'] = [$gallery->projectGalleryImages->first()->id => 'Living room'];
            $this->put(route('admin.landing-gallery.update', $gallery), $data)
                ->assertRedirect()->assertSessionHasNoErrors();
            $this->assertSame('Updated house', $gallery->fresh()->external_project_name);
            if ($withLabels) {
                $this->assertSame('Front view', $gallery->fresh()->image_label);
                $this->assertSame('Living room', $gallery->projectGalleryImages()->first()->label);
            }
        }
    }

    private function image(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('house.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='
        ));
    }
}
