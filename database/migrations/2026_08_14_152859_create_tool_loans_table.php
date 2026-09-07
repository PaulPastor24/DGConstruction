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
            $table->foreignId('worker_id')->constrained('workers', 'worker_id')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects', 'project_id')->nullOnDelete();
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
