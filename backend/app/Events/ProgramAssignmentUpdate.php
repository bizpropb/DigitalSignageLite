<?php
namespace App\Events;

use App\Services\MessageSigningService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ProgramAssignmentUpdate implements ShouldBroadcastNow 
{
    public $signed_message;

    public function __construct(array $data) 
    {
        $signingService = app(MessageSigningService::class);
        $messageData = [
            'type' => 'program-assignment-update',
            'auth_token' => $data['auth_token'],
            'new_program' => $data['new_program'],
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
        return 'program-assignment-update'; 
    }

    public function broadcastWith() 
    { 
        return ['signed_message' => $this->signed_message]; 
    }
}