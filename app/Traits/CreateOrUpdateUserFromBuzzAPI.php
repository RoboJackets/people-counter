<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireNullCoalesceEqualOperator
// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

namespace App\Traits;

use App\User;
use Exception;
use Illuminate\Support\Facades\Log;
use OITNetworkServices\BuzzAPI;
use OITNetworkServices\BuzzAPI\Resources;
use RoboJackets\ErrorPages\SystemError;
use RoboJackets\ErrorPages\Unauthorized;

trait CreateOrUpdateUserFromBuzzAPI
{
    /**
     * Create a new instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @param string $username
     *
     * @return User
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function createOrUpdateUserFromBuzzAPI(string $username): User
    {
        if (null === config('buzzapi.app_password')) {
            throw new Exception('BuzzAPI Not Configured');
        }

        // Check if user already exists
        $user = User::where('username', $username)->first();
        $userIsNew = null === $user;

        // Create a new user (first login only) if they don't already exist
        if ($userIsNew) {
            $user = new User();

            $accountsResponse = BuzzAPI::select(
                'gtGTID',
                'mail',
                'sn',
                'givenName',
                'eduPersonPrimaryAffiliation',
                'gtPrimaryGTAccountUsername',
                'gtAccountEntitlement',
                'uid'
            )->from(Resources::GTED_ACCOUNTS)->where(['uid' => $username])->get();

            if (! $accountsResponse->isSuccessful()) {
                Log::error(
                    'GTED accounts search for ' . $username . ' failed',
                    [$accountsResponse->errorInfo()->message]);
                SystemError::render(0b1001);
                exit;
            }
            $numResults = count($accountsResponse->json->api_result_data);
            if (0 === $numResults) {
                Log::notice('GTED accounts search was successful but gave no results for ' . $username);
                SystemError::render(0b1010);
                exit;
            }

            // If there's multiple results, find the one for their primary GT account or of the User we're searching for
            // If there's only one (we're searching by the uid of that account), just use that one.
            $searchUid = $username ?? $accountsResponse->first()->gtPrimaryGTAccountUsername;
            $account = collect($accountsResponse->json->api_result_data)->firstWhere('uid', $searchUid);

            if (!isset($account->gtGTID)) {
                Log::notice('No GTID returned from BuzzAPI for ' . $username);
                Unauthorized::render(0b1011);
                exit;
            }


            $user->username = $account->uid;
            $user->gtid = $account->gtGTID;
            $user->email = $account->mail;
            $user->first_name = $account->givenName;
            $user->last_name = $account->sn;
            $user->save();
        }

        return $user;
    }
}
