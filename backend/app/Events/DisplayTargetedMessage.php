<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DisplayTargetedMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $authToken;

    public function __construct($message, $authToken)
    {
        $this->message = $message;
        $this->authToken = $authToken;
    }

    public function broadcastOn()
    {
        return new Channel("display-{$this->authToken}");
    }

    public function broadcastAs()
    {
        return 'test-message';
    }
}
