<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('timeline_milestones', 'progress_percentage')) {
            Schema::table('timeline_milestones', function (Blueprint $table): void {
                $table->dropColumn('progress_percentage');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('timeline_milestones', 'progress_percentage')) {
            Schema::table('timeline_milestones', function (Blueprint $table): void {
                $table->decimal('progress_percentage', 5, 2)->default(0)->after('is_delayed');
            });
        }
    }
};
