<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tool_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained('tools')->cascadeOnDelete();
            $table->unsignedInteger('worker_id');
            $table->foreign('worker_id')->references('worker_id')->on('workers')->cascadeOnDelete();
            $table->unsignedInteger('project_id')->nullable();
            $table->foreign('project_id')->references('project_id')->on('projects')->nullOnDelete();
            $table->date('expected_return_date')->nullable();
            $table->enum('condition_at_issue', ['good', 'fair', 'poor'])->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['borrowed', 'returned', 'lost'])->default('borrowed');
            $table->timestamp('borrowed_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->enum('condition_at_return', ['good', 'fair', 'poor'])->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tool_loans');
    }
};
