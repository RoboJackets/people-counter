<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireNullCoalesceEqualOperator
// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

namespace App\Traits;

use App\Models\User;
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
     * @param string|int $identifier
     * @param bool $is_frontend
     *
     * @return User|null
     */
    public function createOrUpdateUserFromBuzzAPI($identifier, bool $is_frontend = true)
    {
        if (null === config('buzzapi.app_password')) {
            throw new Exception('BuzzAPI Not Configured');
        }

        // Determine type of identifier
        $search_value = $identifier;
        if (is_numeric($identifier)) {
            $db_identifier = 'gtid';
            $buzzapi_identifier = 'gtid';
        } elseif (false !== strpos($identifier, '@')) {
            $db_identifier = 'email';
            $buzzapi_identifier = 'email';
        } else {
            $db_identifier = 'username';
            $buzzapi_identifier = 'uid';
        }

        // Check if user already exists
        $user = User::where($db_identifier, $search_value)->first();

        // Create a new user (first login only) if they don't already exist
        if (null === $user) {
            Log::debug('Creating new user from BuzzAPI for '.$buzzapi_identifier.' '.$identifier);
            $user = new User();

        $accountsResponse = BuzzAPI::select(
            'gtGTID',
            'mail',
            'sn',
            'givenName',
            'gtPrimaryGTAccountUsername',
            'uid',
            'eduPersonPrimaryAffiliation'
        )->from(Resources::GTED_ACCOUNTS)->where([$buzzapi_identifier => $search_value])->get();

            if (! $accountsResponse->isSuccessful()) {
                Log::error(
                    'GTED accounts search for '.$search_value.' failed',
                    [$accountsResponse->errorInfo()->message]
                );
                if ($is_frontend) {
                    SystemError::render(1 << 17);
                    exit;
                }

                return null;
            }
            $numResults = count($accountsResponse->json->api_result_data);
            if (0 === $numResults) {
                Log::notice('GTED accounts search was successful but gave no results for '.$search_value);
                if ($is_frontend) {
                    SystemError::render(1 << 18);
                    exit;
                }

                return null;
            }

            // If there's multiple results, find the one for their primary GT account or of the User we're searching for
            // If there's only one (we're searching by the uid of that account), just use that one.
            // phpcs:disable Squiz.WhiteSpace.OperatorSpacing.SpacingAfter
            $searchUid = 'username' === $db_identifier ?
                $search_value : $accountsResponse->first()->gtPrimaryGTAccountUsername;
            $account = collect($accountsResponse->json->api_result_data)->firstWhere('uid', $searchUid);

            if (! isset($account->gtGTID)) {
                Log::notice('No GTID returned from BuzzAPI for '.$search_value);
                if ($is_frontend) {
                    Unauthorized::render(1 << 19);
                    exit;
                }

                return null;
            }

        $user->username = $account->uid;
        $user->gtid = $account->gtGTID;
        $user->email = $account->mail;
        $user->first_name = $account->givenName;
        $user->last_name = $account->sn;
        $user->primary_affiliation = $account->eduPersonPrimaryAffiliation;
        $user->save();

            $msg = 'Created '.$user->first_name.' '.$user->last_name.' ('.$user->username.') via BuzzAPI';
            Log::info($msg);
        }

        return $user;
    }
}
