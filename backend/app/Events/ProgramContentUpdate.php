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
    public int $programId;

    public function __construct(array $contentData, int $programId)
    {
        \Log::info("ProgramContentUpdate EVENT CONSTRUCTOR - START");
        \Log::info("ProgramContentUpdate constructor params:", [
            'contentData' => $contentData,
            'programId' => $programId
        ]);
        \Log::info("Broadcasting to program channel: program-{$programId}");

        $this->programId = $programId;

        $signingService = app(MessageSigningService::class);
        \Log::info("MessageSigningService instantiated");

        $messageData = [
            'content' => $contentData,
            'program_id' => $programId,
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
        $channelName = 'program-' . $this->programId;
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