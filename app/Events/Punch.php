<?php

namespace App\Events;

use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Punch implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * List of people currently in the space
     *
     * @var string $direction
     */
    public $people;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Get all users with an active visit
        $users = User::whereHas('visits', function (Builder $query) {
            $query->active();
        })->orderBy('first_name')->get();

        // We want just the names for this
        $names = $users->pluck('full_name');

        $this->people = $names->toArray();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<\Illuminate\Broadcasting\Channel>|\Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new Channel('punches');
    }
}
