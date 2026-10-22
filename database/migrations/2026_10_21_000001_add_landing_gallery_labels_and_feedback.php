<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (! Schema::hasColumn('landing_gallery_images', 'image_label')) {
            Schema::table('landing_gallery_images', fn (Blueprint $table) => $table->string('image_label')->nullable());
        }
        if (! Schema::hasColumn('landing_gallery_project_images', 'label')) {
            Schema::table('landing_gallery_project_images', fn (Blueprint $table) => $table->string('label')->nullable());
        }
        if (! Schema::hasColumn('landing_page_settings', 'customer_feedback')) {
            Schema::table('landing_page_settings', fn (Blueprint $table) => $table->json('customer_feedback')->nullable());
        }
    }
    public function down(): void {
        Schema::table('landing_gallery_images', fn (Blueprint $table) => $table->dropColumn('image_label'));
        Schema::table('landing_gallery_project_images', fn (Blueprint $table) => $table->dropColumn('label'));
        Schema::table('landing_page_settings', fn (Blueprint $table) => $table->dropColumn('customer_feedback'));
    }
};
