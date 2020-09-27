<?php

declare(strict_types=1);

// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.SpacingBefore

namespace App\Jobs;

use App\User;
use App\Visit;
use Exception;
use GuzzleHttp\Client;
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
     * @var \App\User
     */
    private $user;

    /**
     * The visit that will be sent the email.
     *
     * @var \App\Visit
     */
    private $visit;

    /**
     * Create a new job instance.
     *
     * @param \App\User $user The user
     * @param \App\Visit $visit The visit that triggered this notification
     */
    protected function __construct(User $user, Visit $visit)
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
                'Not sending an email to '.$this->user->username.' for visit '.$this->visit->id.' as there is a more '.
                'recent visit.'
            );

            return;
        }

        $client = new Client(
            [
                'base_uri' => config('apiary.server'),
                'headers' => [
                    'User-Agent' => 'People Counter on '.config('app.url'),
                    'Authorization' => 'Bearer '.config('apiary.token'),
                    'Accept' => 'application/json',
                ],
                'allow_redirects' => false,
            ]
        );

        $response = $client->post(
            '/api/v1/notification/manual',
            [
                'json' => [
                    'template_type' => 'database',
                    'template_id' => config('apiary.ppe_form_email_template_id'),
                    'emails' => [
                        $this->user->email,
                    ],
                ],
            ]
        );

        if (200 !== $response->getStatusCode()) {
            throw new Exception(
                'Apiary returned an unexpected HTTP response code '.$response->getStatusCode().', expected 200'
            );
        }

        $responseBody = $response->getBody()->getContents();

        $json = json_decode($responseBody);

        if ('success' !== $json->status) {
            throw new Exception(
                'Apiary returned an unexpected response '.$responseBody.', expected status: success'
            );
        }

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
