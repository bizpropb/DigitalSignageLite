<?php

namespace App\Events;

use App\Services\MessageSigningService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $signed_message;

    public function __construct(string $message, string $auth_token = null)
    {
        $signingService = app(MessageSigningService::class);
        
        $messageData = [
            'message' => $message,
            'auth_token' => $auth_token,
            'type' => 'test-message',
            'timestamp' => now()->toISOString()
        ];
        
        $this->signed_message = $signingService->signMessage($messageData);
    }

    public function broadcastOn()
    {
        return new Channel('display-updates');
    }

    public function broadcastAs()
    {
        return 'secure-message';
    }
}