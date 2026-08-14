<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tool_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_loan_id')->constrained('tool_loans')->cascadeOnDelete();
            $table->foreignId('tool_id')->constrained('tools')->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained('workers', 'worker_id')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('reason', ['lost', 'damaged_beyond_repair', 'stolen']);
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'approved', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tool_deductions');
    }
};
