<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Space;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RoboJackets\ApiaryUser;

trait UpdateUserSpacesFromSUMS
{
    /**
     * Update user space attachments via SUMS API.
     */
    public function updateUserSpacesFromSUMS(User $user): ?Collection
    {
        $username = $user->username;
        $client = new Client([
            'base_uri' => 'https://'.config('sums.host'),
            'timeout' => 5.0,
        ]);
        try {
            $response = $client->request('GET', '/SUMSAPI/rest/SCC_BGMembership/GetMemberships', [
                'query' => [
                    'Key' => config('sums.api_key'),
                    'GTUsername' => $username,
                ],
            ]);
        } catch (RequestException $e) {
            $msg = 'RequestException while querying SUMS for billing groups for '.$username.': '.$e->getMessage();
            Log::error($msg);

            return null;
        } catch (\Throwable $e) {
            $msg = 'Exception while querying SUMS for billing groups for '.$username.': '.$e->getMessage();
            Log::error($msg);

            return null;
        }

        $contents = json_decode($response->getBody()->getContents());
        // phpcs:disable Generic.Formatting.SpaceBeforeCast.NoSpace
        if (0 === count((array) $contents)) {
            Log::info($username.' is not associated with any SCC billing groups in SUMS');

            ApiaryUser::configure(config('apiary.server'), config('apiary.token'));

            if (ApiaryUser::fetchUser($username) && ApiaryUser::exists() && ApiaryUser::isAccessActive()) {
                Log::info($username.' is a RoboJackets member according to Apiary');
                $user->spaces()->syncWithoutDetaching([Space::where('name', 'RoboJackets')->sole()->id]);
                $user->refresh();

                return $user->spaces;
            }

            Log::info($username.' is not a RoboJackets member');

            return null;
        }

        // SUMS Result Key => People Counter Space Name
        $spaceMap = [
            'isEcoCar' => 'EcoCAR',
            'isGTMotorSports' => 'GT Motorsports',
            'isGTOffRoad' => 'GT Off-Road',
            'isGTSolarRacing' => 'Solar Racing',
            'isHyTechRacing' => 'HyTech Racing',
            'isRoboJackets' => 'RoboJackets',
            'isWreckRacing' => 'Wreck Racing',
        ];
        $updated = false;
        foreach ($contents as $billingGroup => $member) {
            if (null === $member) {
                continue;
            }

            $space = Space::where('name', $spaceMap[$billingGroup])->sole();
            $user->spaces()->syncWithoutDetaching([$space->id]);
            Log::info('Attached '.$username.' to '.$space->name.' via SUMS');
            $updated = true;
        }
        if ($updated) {
            $user->refresh();

            return $user->spaces;
        }

        return null;
    }
}
