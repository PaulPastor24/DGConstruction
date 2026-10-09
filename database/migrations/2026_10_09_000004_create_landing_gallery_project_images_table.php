<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_gallery_project_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_gallery_image_id')
                ->constrained('landing_gallery_images')
                ->cascadeOnDelete();
            $table->string('image_path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_gallery_project_images');
    }
};
