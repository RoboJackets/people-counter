<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Notifications\SpaceManagerMorningReport;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSpaceManagerMorningReport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of attempts for this job.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Send to space managers whose spaces have active visits at the time of the mail being sent
        $managers = User::whereHas('managedSpaces', function($q) {
                $q->whereHas('activeVisits');
            })
            ->with('managedSpaces')
            ->with('managedSpaces.activeVisits')
            ->with('managedSpaces.activeVisits.user')
            ->get();

        if (count($managers) < 1) {
            Log::info(self::class.':Not sending morning report - either no space managers defined or no active visits');
            exit;
        }

        foreach ($managers as $manager) {
            foreach ($manager->managedSpaces as $space) {
                $manager->notify(new SpaceManagerMorningReport($space));
                $username = $manager->username;
                $spacename = $space->name;
                Log::info(self::class.": Successfully queued morning report email for $username / $spacename");
            }
        }
    }
}
