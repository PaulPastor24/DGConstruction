<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('client_notifications')) {
            return;
        }

        Schema::create('client_notifications', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id')->index();
            $table->string('type')->nullable()->index();
            $table->string('title');
            $table->text('message')->nullable();
            $table->json('data')->nullable();
            $table->bigInteger('related_id')->nullable()->index();
            $table->string('related_type')->nullable()->index();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('client_id')->on('clients')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_notifications');
    }
};
