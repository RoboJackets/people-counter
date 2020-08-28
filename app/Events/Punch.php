<?php

namespace App\Events;

use App\Space;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Punch implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * List of spaces and those occupying them
     *
     * @var array $spaces
     */
    public $spaces;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        $spaces = Space::with(
            [
                'activeChildVisitsUsers' =>
                    function ($query) {
                        $query->select('first_name', 'last_name');
                    },
                'activeVisitsUsers' =>
                    function ($query) {
                        $query->select('first_name', 'last_name');
                    }
            ]
        )->get();

        if (count($spaces) > 0) {
            /** @suppress PhanPossiblyNonClassMethodCall */
            $this->spaces = $spaces->toArray();
        } else {
            Log::error('Punch event fired, but no spaces found with active visits');
        }

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
