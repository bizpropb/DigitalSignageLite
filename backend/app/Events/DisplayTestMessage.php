<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DisplayTestMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $displayId;
    public $message;
    public $displayName;
    public $timestamp;

    public function __construct(int $displayId, string $message, string $displayName)
    {
        $this->displayId = $displayId;
        $this->message = $message;
        $this->displayName = $displayName;
        $this->timestamp = now()->toISOString();
    }

    public function broadcastOn()
    {
        // Broadcast to a display-specific channel
        return new Channel("display.{$this->displayId}");
    }

    public function broadcastAs()
    {
        return 'display.test-message';
    }

    public function broadcastWith()
    {
        return [
            'display_id' => $this->displayId,
            'message' => $this->message,
            'display_name' => $this->displayName,
            'timestamp' => $this->timestamp,
            'type' => 'admin_test'
        ];
    }
}