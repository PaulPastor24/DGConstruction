<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_face_enrollments', function (Blueprint $table) {
            $table->text('face_descriptors')->nullable();
            $table->string('model_version', 60)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('worker_face_enrollments', function (Blueprint $table) {
            $table->dropColumn(['face_descriptors', 'model_version']);
        });
    }
};
