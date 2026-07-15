<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accomplishment_reports', function (Blueprint $table) {
            $table->decimal('accomplishment_percentage', 5, 2)->nullable()->after('report_text');
        });
    }

    public function down(): void
    {
        Schema::table('accomplishment_reports', function (Blueprint $table) {
            $table->dropColumn('accomplishment_percentage');
        });
    }
};
