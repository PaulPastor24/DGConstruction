<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_gallery_images', function (Blueprint $table) {
            $table->unsignedInteger('project_id')->nullable()->change();
            $table->boolean('is_external')->default(false)->after('is_active');
            $table->string('external_project_name')->nullable()->after('is_external');
            $table->string('external_project_location')->nullable()->after('external_project_name');
            $table->text('external_project_description')->nullable()->after('external_project_location');
            $table->string('external_project_url')->nullable()->after('external_project_description');
        });
    }

    public function down(): void
    {
        Schema::table('landing_gallery_images', function (Blueprint $table) {
            $table->dropColumn([
                'is_external',
                'external_project_name',
                'external_project_location',
                'external_project_description',
                'external_project_url',
            ]);
        });
    }
};
