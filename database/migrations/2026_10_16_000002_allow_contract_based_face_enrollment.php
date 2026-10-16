<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_face_enrollments', function (Blueprint $table) {
            $table->dateTime('notice_acknowledged_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Keep nullable: historical contract-based enrollments have no in-app acknowledgement.
    }
};
