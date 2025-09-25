<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DisplayTestMessage implements ShouldBroadcastNow
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

        // GROK FIX: Add logging to constructor
        \Log::info('DisplayTestMessage constructed with ID:', ['id' => $this->displayId]);
    }

    public function broadcastOn()
    {
        // Broadcast to a display-specific channel
        $channel = "display.{$this->displayId}";

        // GROK FIX: Add logging to broadcastOn
        \Log::info('Broadcasting on channel:', ['channel' => $channel]);

        return new Channel($channel);
    }

    public function broadcastAs()
    {
        $eventName = 'display.test-message';

        // GROK FIX: Add logging to broadcastAs
        \Log::info('DisplayTestMessage broadcastAs:', ['event_name' => $eventName]);

        return $eventName;
    }

    public function broadcastWith()
    {
        $data = [
            'display_id' => $this->displayId,
            'message' => $this->message,
            'display_name' => $this->displayName,
            'timestamp' => $this->timestamp,
            'type' => 'admin_test'
        ];

        // GROK FIX: Add logging to broadcastWith
        \Log::info('DisplayTestMessage broadcastWith:', ['data' => $data]);

        return $data;
    }
}