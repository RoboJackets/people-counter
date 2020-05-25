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
     * Execute the job.
     *
     * @param string $identifier
     *
     * @return User
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function createOrUpdateUserFromBuzzAPI(string $identifier): User
    {
        if (null === config('buzzapi.app_password')) {
            throw new Exception('BuzzAPI Not Configured');
        }

        // Determine type of identifier
        if (is_numeric($identifier)) {
            $db_identifier = 'gtid';
            $buzzapi_identifier = 'gtid';
            $gted_identifier = 'gtGTID';
            $search_value = $identifier;
        } else {
            $db_identifier = 'username';
            $buzzapi_identifier = 'uid';
            $gted_identifier = 'uid';
            $search_value = $identifier;
        }

        // Check if user already exists
        $user = User::where($db_identifier, $search_value)->first();

        // Create a new user (first login only) if they don't already exist
        if (null === $user) {
            $user = new User();

            $accountsResponse = BuzzAPI::select(
                'gtGTID',
                'mail',
                'sn',
                'givenName',
                'gtPrimaryGTAccountUsername',
                'uid'
            )->from(Resources::GTED_ACCOUNTS)->where([$buzzapi_identifier => $search_value])->get();

            if (! $accountsResponse->isSuccessful()) {
                Log::error(
                    'GTED accounts search for ' . $search_value . ' failed',
                    [$accountsResponse->errorInfo()->message]
                );
                SystemError::render(0b1001);
                exit;
            }
            $numResults = count($accountsResponse->json->api_result_data);
            if (0 === $numResults) {
                Log::notice('GTED accounts search was successful but gave no results for ' . $search_value);
                SystemError::render(0b1010);
                exit;
            }

            // If there's multiple results, find the one for their primary GT account or of the User we're searching for
            // If there's only one (we're searching by the uid of that account), just use that one.
            $searchUid = ($db_identifier === 'username') ?
                $search_value : $accountsResponse->first()->gtPrimaryGTAccountUsername;
            $account = collect($accountsResponse->json->api_result_data)->firstWhere('uid', $searchUid);

            if (!isset($account->gtGTID)) {
                Log::notice('No GTID returned from BuzzAPI for ' . $search_value);
                Unauthorized::render(0b1011);
                exit;
            }

            $user->username = $account->uid;
            $user->gtid = $account->gtGTID;
            $user->email = $account->mail;
            $user->first_name = $account->givenName;
            $user->last_name = $account->sn;
            $user->save();

            $msg = 'Created ' . $user->first_name . ' ' . $user->last_name . ' (' . $user->username . ') via BuzzAPI';
            Log::info($msg);
        }

        return $user;
    }
}
