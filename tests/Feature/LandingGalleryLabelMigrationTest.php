<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LandingGalleryLabelMigrationTest extends TestCase
{
    public function test_label_migration_can_resume_after_partial_completion(): void
    {
        Schema::create('landing_gallery_images', function ($table) {
            $table->id();
            $table->string('image_label')->nullable();
        });
        foreach ([
            '2026_10_09_000002_create_landing_page_settings_table.php',
            '2026_10_09_000004_create_landing_gallery_project_images_table.php',
        ] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        $migration = require database_path('migrations/2026_10_21_000001_add_landing_gallery_labels_and_feedback.php');
        $migration->up();
        $migration->up();

        $this->assertTrue(Schema::hasColumn('landing_gallery_images', 'image_label'));
        $this->assertTrue(Schema::hasColumn('landing_gallery_project_images', 'label'));
        $this->assertTrue(Schema::hasColumn('landing_page_settings', 'customer_feedback'));
    }
}
