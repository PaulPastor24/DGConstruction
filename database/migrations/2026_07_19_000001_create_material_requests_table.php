<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->foreignId('project_id')->references('project_id')->on('projects')->cascadeOnDelete();
            $table->foreignId('material_id')->references('id')->on('materials')->cascadeOnDelete();
            $table->foreignId('requested_by')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->references('user_id')->on('users')->nullOnDelete();
            $table->string('status', 50)->default('pending');
            $table->decimal('requested_quantity', 15, 2);
            $table->decimal('approved_quantity', 15, 2)->nullable();
            $table->string('unit', 50)->nullable();
            $table->text('remarks')->nullable();
            $table->text('rejection_remarks')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['requested_by', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_requests');
    }
};
