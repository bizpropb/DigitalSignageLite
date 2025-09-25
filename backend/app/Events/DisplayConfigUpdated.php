<?php

namespace App\Events;

use App\Models\Display;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DisplayConfigUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Display $display;

    public function __construct(Display $display)
    {
        $this->display = $display;
    }

    public function broadcastOn()
    {
        return new Channel("display-{$this->display->id}");
    }

    public function broadcastAs(): string
    {
        return 'display-config-updated';
    }

    public function broadcastWith(): array
    {
        return [
            'program_id' => $this->display->program_id
        ];
    }
}