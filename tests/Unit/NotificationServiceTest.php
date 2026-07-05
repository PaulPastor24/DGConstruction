<?php

namespace Tests\Unit;

use App\Models\ClientNotification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('client_notifications');
        Schema::create('client_notifications', function ($table) {
            $table->id();
            $table->unsignedInteger('client_id');
            $table->string('type')->nullable();
            $table->string('title');
            $table->text('message')->nullable();
            $table->json('data')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('related_type')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_it_creates_a_client_notification_for_a_client(): void
    {
        $notification = NotificationService::notifyClient(42, [
            'type' => 'phase',
            'title' => 'Progress Updated',
            'message' => 'The phase was updated.',
            'data' => ['phase_id' => 7],
            'related_id' => 7,
            'related_type' => 'phase',
        ]);

        $this->assertInstanceOf(ClientNotification::class, $notification);
        $this->assertEquals(42, $notification->client_id);
        $this->assertEquals('Progress Updated', $notification->title);
        $this->assertFalse($notification->is_read);
    }
}
