<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedTinyInteger('bedrooms')->nullable()->after('description');
            $table->unsignedTinyInteger('bathrooms')->nullable()->after('bedrooms');
            $table->decimal('lot_area', 10, 2)->nullable()->after('bathrooms');
            $table->json('highlights')->nullable()->after('lot_area');
            $table->json('features')->nullable()->after('highlights');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'bedrooms',
                'bathrooms',
                'lot_area',
                'highlights',
                'features',
            ]);
        });
    }
};
