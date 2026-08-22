<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'actual_start_date' => fn (Blueprint $table) => $table->date('actual_start_date')->nullable(),
            'actual_end_date' => fn (Blueprint $table) => $table->date('actual_end_date')->nullable(),
            'delay_reason' => fn (Blueprint $table) => $table->string('delay_reason')->nullable(),
            'delay_notes' => fn (Blueprint $table) => $table->text('delay_notes')->nullable(),
            'depends_on_phase_id' => fn (Blueprint $table) => $table->unsignedInteger('depends_on_phase_id')->nullable(),
            'notes' => fn (Blueprint $table) => $table->text('notes')->nullable(),
        ];

        foreach ($columns as $column => $definition) {
            if (!Schema::hasColumn('construction_phases', $column)) {
                Schema::table('construction_phases', $definition);
            }
        }

        if (!Schema::hasColumn('construction_phases', 'depends_on_phase_id')) {
            return;
        }

        Schema::table('construction_phases', function (Blueprint $table) {
            $table->foreign('depends_on_phase_id')
                ->references('phase_id')
                ->on('construction_phases')
                ->nullOnDelete();
            $table->index(['project_id', 'phase_order']);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('construction_phases')) {
            Schema::table('construction_phases', function (Blueprint $table) {
                $table->dropForeign(['depends_on_phase_id']);
                $table->dropIndex(['project_id', 'phase_order']);
            });
        }

        foreach (['actual_start_date', 'actual_end_date', 'delay_reason', 'delay_notes', 'depends_on_phase_id', 'notes'] as $column) {
            if (Schema::hasColumn('construction_phases', $column)) {
                Schema::table('construction_phases', fn (Blueprint $table) => $table->dropColumn($column));
            }
        }
    }
};
