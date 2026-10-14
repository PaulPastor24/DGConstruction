<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('worker_face_attempts', function (Blueprint $table) {
            $table->id();
            $table->uuid('aws_session_id')->unique();
            $table->unsignedBigInteger('supervisor_id');
            $table->unsignedBigInteger('worker_id')->nullable(); // targeted enrollment only
            $table->unsignedBigInteger('matched_worker_id')->nullable();
            $table->string('purpose', 16); // enroll or attendance
            $table->string('status', 24)->default('pending');
            $table->decimal('liveness_score', 5, 2)->nullable();
            $table->decimal('match_score', 5, 2)->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->index(['supervisor_id', 'created_at']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_face_attempts');
    }
};
