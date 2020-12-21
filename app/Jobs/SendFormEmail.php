<?php

declare(strict_types=1);

// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.SpacingBefore,Generic.Strings.UnnecessaryStringConcat.Found

namespace App\Jobs;

use App\Models\User;
use App\Models\Visit;
use App\Notifications\PPEForm;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFormEmail implements ShouldQueue
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
     * The user that will be sent the email.
     *
     * @var \App\Models\User
     */
    private $user;

    /**
     * The visit that will be sent the email.
     *
     * @var \App\Models\Visit
     */
    private $visit;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\User $user The user
     * @param \App\Models\Visit $visit The visit that triggered this notification
     */
    public function __construct(User $user, Visit $visit)
    {
        $this->user = $user;
        $this->visit = $visit;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        // Any more recent visits? If there are, don't do anything and let the last of the day trigger the email.
        $visits = $this->user->visits()->where('in_time', '>', $this->visit->out_time)->count();

        if (0 !== $visits) {
            Log::info(
                self::class.': Not sending an email to '.$this->user->username.' for visit '.$this->visit->id.
                 'as there is a more recent visit.'
            );

            return;
        }

        $this->user->notify(new PPEForm());

        Log::info(self::class.': Successfully queued PPE form email for '.$this->user->username);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<string>
     */
    public function tags(): array
    {
        return ['user:'.$this->user->username];
    }
}
