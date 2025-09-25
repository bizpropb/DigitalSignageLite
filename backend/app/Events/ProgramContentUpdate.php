<?php

namespace App\Events;

use App\Services\MessageSigningService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProgramContentUpdate implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $signed_message;
    public int $displayId;

    public function __construct(array $contentData, int $displayId)
    {
        \Log::info("ProgramContentUpdate EVENT CONSTRUCTOR - START");
        \Log::info("ProgramContentUpdate constructor params:", [
            'contentData' => $contentData,
            'displayId' => $displayId
        ]);
        \Log::info("Broadcasting to display channel: display-{$displayId}");

        $this->displayId = $displayId;
        
        $signingService = app(MessageSigningService::class);
        \Log::info("MessageSigningService instantiated");
        
        $messageData = [
            'content' => $contentData,
            'display_id' => $displayId,
            'type' => 'content-update',
            'timestamp' => now()->toISOString()
        ];
        
        \Log::info("Message data prepared:", $messageData);
        
        $this->signed_message = $signingService->signMessage($messageData);
        
        \Log::info("Message signed successfully, signed_message length:", ['length' => strlen($this->signed_message)]);
        \Log::info("ProgramContentUpdate EVENT CONSTRUCTOR - END");
    }

    public function broadcastOn()
    {
        $channelName = 'display-' . $this->displayId;
        \Log::info("ProgramContentUpdate broadcastOn() called - returning '{$channelName}' channel");
        return new Channel($channelName);
    }

    public function broadcastAs()
    {
        \Log::info("ProgramContentUpdate broadcastAs() called - returning 'content-update' event name");
        return 'content-update';
    }

    public function broadcastWith()
    {
        \Log::info("ProgramContentUpdate broadcastWith() called - returning signed_message data");
        return [
            'signed_message' => $this->signed_message
        ];
    }
}