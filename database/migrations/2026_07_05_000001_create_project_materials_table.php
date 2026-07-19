<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_materials', function (Blueprint $table) {
            $table->id();
            $table->integer('project_id');
            $table->bigInteger('material_id');
            $table->decimal('planned_quantity', 12, 2)->default(0);
            $table->decimal('used_quantity', 12, 2)->default(0);
            $table->string('unit')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('project_id')->on('projects')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('material_id')->references('id')->on('materials')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unique(['project_id', 'material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_materials');
    }
};
