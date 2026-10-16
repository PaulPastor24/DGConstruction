<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_face_enrollments', function (Blueprint $table) {
            // Three real descriptors plus encryption can exceed MySQL TEXT's 65,535-byte limit.
            $table->longText('face_descriptors')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Keep the larger column so rollback cannot truncate existing encrypted templates.
    }
};
