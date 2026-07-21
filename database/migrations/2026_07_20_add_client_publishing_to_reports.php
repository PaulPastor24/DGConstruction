<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accomplishment_reports', function (Blueprint $table) {
            $table->boolean('is_published_to_client')->default(false)->after('rejected_at');
            $table->longText('admin_report_text')->nullable()->after('is_published_to_client');
            $table->json('admin_site_images')->nullable()->after('admin_report_text');
            $table->text('admin_explanation')->nullable()->after('admin_site_images');
            $table->timestamp('published_at')->nullable()->after('admin_explanation');
        });
    }

    public function down(): void
    {
        Schema::table('accomplishment_reports', function (Blueprint $table) {
            $table->dropColumn([
                'is_published_to_client',
                'admin_report_text',
                'admin_site_images',
                'admin_explanation',
                'published_at',
            ]);
        });
    }
};
