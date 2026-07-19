<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_archives', function (Blueprint $table) {
            $table->id('project_archive_id');
            $table->integer('project_id');
            $table->string('project_name', 200);
            $table->text('project_location')->nullable();
            $table->integer('client_id')->nullable();
            $table->bigInteger('engineer_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('target_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->string('status')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index('project_id');
            $table->index('status');
            $table->index('archived_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_archives');
    }
};
