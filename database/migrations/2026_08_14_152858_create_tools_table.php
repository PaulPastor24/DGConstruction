<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('tool_code')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->enum('type', ['tool', 'equipment'])->default('tool');
            $table->string('unit')->nullable();
            $table->enum('condition', ['good', 'fair', 'poor'])->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['available', 'in_use', 'lost', 'retired'])->default('available');
            $table->unsignedBigInteger('current_borrower_worker_id')->nullable();
            $table->timestamps();

            $table->foreign('current_borrower_worker_id')
                ->references('worker_id')
                ->on('workers')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tools');
    }
};
