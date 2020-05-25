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
        $users = User::whereHas('visits', function (Builder $query) {
            $query->active();
        })->get();

        // Return a list of user full names as an array
        // I'm confident there's a better way to do this
        // If you know of one, by all means please fix it
        $subset = $users->map(function ($user) {
            $collect = collect($user->toArray())
                ->only(['full_name'])
                ->all();
            return $collect['full_name'];
        });

        $this->people = (array) $subset;
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
