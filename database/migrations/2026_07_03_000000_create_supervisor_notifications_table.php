<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisor_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('supervisor_id')->index();
            $table->string('type')->nullable()->index();
            $table->string('title');
            $table->text('message')->nullable();
            $table->json('data')->nullable();
            $table->bigInteger('related_id')->nullable()->index();
            $table->string('related_type')->nullable()->index();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('supervisor_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_notifications');
    }
};
