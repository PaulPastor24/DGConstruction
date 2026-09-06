<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('construction_phases', function (Blueprint $table): void {
            if (!Schema::hasColumn('construction_phases', 'override_reason')) {
                $table->text('override_reason')->nullable()->after('admin_progress_override');
            }
            if (!Schema::hasColumn('construction_phases', 'override_applied_at')) {
                $table->timestamp('override_applied_at')->nullable()->after('override_reason');
            }
            if (!Schema::hasColumn('construction_phases', 'override_applied_by')) {
                // users.user_id is the Laravel big integer primary key.
                $table->unsignedBigInteger('override_applied_by')->nullable()->after('override_applied_at');
            }
        });

        if (!Schema::hasColumn('construction_phases', 'override_applied_by')) {
            return;
        }

        Schema::table('construction_phases', function (Blueprint $table): void {
            $table->foreign('override_applied_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('construction_phases', function (Blueprint $table): void {
            if (Schema::hasColumn('construction_phases', 'override_applied_by')) {
                $table->dropForeign(['override_applied_by']);
            }
            foreach (['override_applied_by', 'override_applied_at', 'override_reason'] as $column) {
                if (Schema::hasColumn('construction_phases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
