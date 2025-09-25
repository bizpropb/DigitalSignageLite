<?php

namespace App\Events;

use App\Services\MessageSigningService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentUpdate implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $signed_message;

    public function __construct(array $contentData)
    {
        \Log::info("ContentUpdate EVENT CONSTRUCTOR - START");
        \Log::info("ContentUpdate constructor params:", ['contentData' => $contentData]);
        
        $signingService = app(MessageSigningService::class);
        \Log::info("MessageSigningService instantiated");
        
        $messageData = [
            'content' => $contentData,
            'type' => 'content-update',
            'timestamp' => now()->toISOString()
        ];
        
        \Log::info("Message data prepared:", $messageData);
        
        $this->signed_message = $signingService->signMessage($messageData);
        
        \Log::info("Message signed successfully, signed_message length:", ['length' => strlen($this->signed_message)]);
        \Log::info("ContentUpdate EVENT CONSTRUCTOR - END");
    }

    public function broadcastOn()
    {
        \Log::info("ContentUpdate broadcastOn() called - returning 'live-display' channel");
        return new Channel('live-display');
    }

    public function broadcastAs()
    {
        \Log::info("ContentUpdate broadcastAs() called - returning 'content-update' event name");
        return 'content-update';
    }

    public function broadcastWith()
    {
        \Log::info("ContentUpdate broadcastWith() called - returning signed_message data");
        return [
            'signed_message' => $this->signed_message
        ];
    }
}