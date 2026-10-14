<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('worker_face_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('worker_id')->unique();
            $table->string('aws_face_id', 128)->unique();
            $table->unsignedBigInteger('enrolled_by');
            $table->timestamp('enrolled_at');
            $table->timestamp('notice_acknowledged_at');
            $table->string('privacy_notice_version', 40);
            $table->timestamps();
            // No photos, raw face templates, or video stored in MySQL.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_face_enrollments');
    }
};
