<?php

namespace App\Events;

use App\Services\MessageSigningService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $signed_message;

    public function __construct(string $message, string $auth_token = null)
    {
        \Log::info("TestMessage EVENT CONSTRUCTOR - START");
        \Log::info("TestMessage constructor params:", ['message' => $message, 'auth_token' => $auth_token]);
        
        $signingService = app(MessageSigningService::class);
        \Log::info("MessageSigningService instantiated");
        
        $messageData = [
            'message' => $message,
            'auth_token' => $auth_token,
            'type' => 'test-message',
            'timestamp' => now()->toISOString()
        ];
        
        \Log::info("Message data prepared:", $messageData);
        
        $this->signed_message = $signingService->signMessage($messageData);
        
        \Log::info("Message signed successfully, signed_message length:", ['length' => strlen($this->signed_message)]);
        \Log::info("TestMessage EVENT CONSTRUCTOR - END");
    }

    public function broadcastOn()
    {
        \Log::info("TestMessage broadcastOn() called - returning 'display-updates' channel");
        return new Channel('display-updates');
    }

    public function broadcastAs()
    {
        \Log::info("TestMessage broadcastAs() called - returning 'secure-message' event name");
        return 'secure-message';
    }

    public function broadcastWith()
    {
        \Log::info("TestMessage broadcastWith() called - returning signed_message data");
        return [
            'signed_message' => $this->signed_message
        ];
    }
}