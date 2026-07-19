<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_usages', function (Blueprint $table) {
            $table->id();
            $table->integer('project_id');
            $table->integer('phase_id');
            $table->bigInteger('material_id');
            $table->decimal('quantity_used', 12, 2);
            $table->string('unit')->nullable();
            $table->date('usage_date');
            $table->text('remarks')->nullable();
            $table->bigInteger('recorded_by');
            $table->string('site_photo_path')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('project_id')->on('projects')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('phase_id')->references('phase_id')->on('construction_phases')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('material_id')->references('id')->on('materials')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('recorded_by')->references('user_id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_usages');
    }
};
