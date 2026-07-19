<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accomplishment_reports', function (Blueprint $table) {
            // Add approval workflow columns
            $table->bigInteger('reviewed_by')->nullable()->after('submitted_by');
            $table->bigInteger('approved_by')->nullable()->after('reviewed_by');
            $table->string('approval_status')->default('pending')->after('ai_status');
            $table->text('approval_remarks')->nullable()->after('approval_status');
            $table->timestamp('reviewed_at')->nullable()->after('approval_remarks');
            $table->timestamp('approved_at')->nullable()->after('reviewed_at');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');

            // Add foreign keys for approvers
            $table->foreign('reviewed_by')->references('user_id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('approved_by')->references('user_id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('accomplishment_reports', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'reviewed_by',
                'approved_by',
                'approval_status',
                'approval_remarks',
                'reviewed_at',
                'approved_at',
                'rejected_at',
            ]);
        });
    }
};
