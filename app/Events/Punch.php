<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Punch implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Whether the punch was in or out
     *
     * @var string $type
     */
    public $direction;

    /**
     * The name of the event
     *
     * @var string $name
     */
    public $name;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $direction, string $name)
    {
        $this->direction = $direction;
        $this->name = $name;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<Illuminate\Broadcasting\Channel>|Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new Channel('punches');
    }
}
