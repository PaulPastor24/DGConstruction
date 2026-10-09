<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_gallery_images', function (Blueprint $table) {
            $table->unsignedTinyInteger('external_bedrooms')->nullable()->after('external_project_url');
            $table->unsignedTinyInteger('external_bathrooms')->nullable()->after('external_bedrooms');
            $table->decimal('external_lot_area', 10, 2)->nullable()->after('external_bathrooms');
            $table->json('external_highlights')->nullable()->after('external_lot_area');
            $table->json('external_features')->nullable()->after('external_highlights');
        });
    }

    public function down(): void
    {
        Schema::table('landing_gallery_images', function (Blueprint $table) {
            $table->dropColumn([
                'external_bedrooms',
                'external_bathrooms',
                'external_lot_area',
                'external_highlights',
                'external_features',
            ]);
        });
    }
};
