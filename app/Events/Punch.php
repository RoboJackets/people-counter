<?php

declare(strict_types=1);

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
     * List of spaces and those occupying them.
     *
     * @var array<\App\Space>
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
                'activeChildVisitsUsers' => static function ($query): void {
                    $query->select('first_name', 'last_name');
                },
                'activeVisitsUsers' => static function ($query): void {
                    $query->select('first_name', 'last_name');
                },
            ]
        )->get();

        if (count($spaces) > 0) {
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
