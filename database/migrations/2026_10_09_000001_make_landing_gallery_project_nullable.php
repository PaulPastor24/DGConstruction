<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('landing_gallery_images')
            && Schema::hasColumn('landing_gallery_images', 'project_id')) {
            Schema::table('landing_gallery_images', function (Blueprint $table) {
                $table->unsignedInteger('project_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('landing_gallery_images')
            || ! Schema::hasColumn('landing_gallery_images', 'project_id')) {
            return;
        }

        Schema::table('landing_gallery_images', function (Blueprint $table) {
            $table->unsignedInteger('project_id')->nullable(false)->change();
        });
    }
};
