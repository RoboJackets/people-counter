<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\Punch;
use App\Http\Requests\StoreVisitPunch;
use App\Jobs\SendFormEmail;
use App\Jobs\SendSignOutReminderEmail;
use App\Models\Space;
use App\Models\User;
use App\Models\Visit;
use App\Traits\CreateOrUpdateUserFromBuzzAPI;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class VisitPunchController extends Controller
{
    use DispatchesJobs;
    use CreateOrUpdateUserFromBuzzAPI;

    /**
     * Record a new in/out punch for a Visit.
     *
     * @param StoreVisitPunch $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreVisitPunch $request)
    {
        $gtid = $request->input('gtid');
        $door = $request->input('door');

        Log::debug('Punch by '.$gtid.' at '.$door);

        // Fetch user to include name in response if we've seen them before
        $user = User::where('gtid', $gtid)->first();

        // Fetch from BuzzAPI if they've not been seen before
        if (null === $user) {
            Log::debug('Did not find existing user for punch by '.$gtid.', creating via BuzzAPI');
            try {
                $user = $this->createOrUpdateUserFromBuzzAPI($gtid);
            } catch (\Throwable $e) {
                Log::error(
                    'Error querying BuzzAPI to create new user for punch by '.$gtid,
                    [$e->getMessage()]
                );

                return response()->json([
                    'status' => 'error',
                    'error' => 'Unable to create new user due to BuzzAPI failure',
                ], 500);
            }
        }

        // Get full name from user model for use later
        // In case BuzzAPI had a problem, still process the punch but use a placeholder name
        $name = null === $user ? 'Unknown' : $user->full_name;

        // Check for default space for user
        $userSpaces = $user->spaces;
        if (0 === count($userSpaces)) {
            Log::info('Punch rejected - No default space(s) set for '.$gtid);
            return response()->json([
                'status' => 'error',
                'error' => 'No default space(s) set for user.',
            ], 422);
        }

        // Find active visit for GTID (if any)
        $active_visits = Visit::activeForUser($gtid)->with('spaces')->get();

        if (count($active_visits) > 1) {
            // Eek! User has multiple active visits. This shouldn't happen!
            Log::error('Multiple active visits found for '.$gtid);

            return response()->json(
                [
                    'status' => 'error',
                    'error' => 'Multiple active visits should never happen',
                ],
                500
            );
        }

        $transition = null;
        if (1 === count($active_visits)) {
            // Update existing visit to punch out
            $visit = $active_visits->first();
            $visit->out_time = Carbon::now();
            $visit->out_door = $door;
            $visit->save();

            Log::info('Punch out by '.$gtid.' at '.$door);

            //Notify all kiosks via websockets
            event(new Punch());

            // Check if kiosk/door where punched is part of a different space
            $punch_space_id = $request->input('space_id');
            $active_visit_space_ids = $active_visits->first()->spaces->pluck('id')->toArray();
            if (in_array($punch_space_id, $active_visit_space_ids)) {
                // Same space, no new punch in needed.
                SendFormEmail::dispatch($user, $visit)->delay(now()->addMinutes(20));
                return response()->json(['status' => 'success', 'punch' => 'out', 'name' => $name]);
            } else {
                // Different space, fall through to normal punch in handler
                $active_visit_space_names = Space::findMany($active_visit_space_ids)->pluck('name')->toArray();
                $transition['from'] = $active_visit_space_names;
                Log::info('Space transition for '.$gtid.' at '.$door);
            }

        }

        // Determine which space to punch in at
        $punchSpaces = [];
        if (! $request->has('space_id')) {
            Log::debug('request does not have space_id');
            $punchSpaces = $userSpaces->toArray();
        } else {
            Log::debug('request has space_id: '.$request->input('space_id'));
            $requestedSpace = Space::find($request->input('space_id'));

            foreach ($userSpaces as $userSpace) {
                // User default space is a child of kiosk-assigned space, or is the kiosk-assigned space
                // ASSUMPTION: Kiosk will never be assigned to a space with a parent, always the parent itself
                if ($requestedSpace->children->contains($userSpace) || $requestedSpace->id === $userSpace->id) {
                    $punchSpaces[] = $userSpace;
                    Log::debug('userspace '.$userSpace->id.' is child of or requested space');
                } else {
                    Log::debug('userspace '.$userSpace->id.' is NOT child of or requested space');
                }
            }
            // User has different default top-level space than the kiosk-assigned one
            // Punch into the kiosk-assigned space as a fallback
            if (0 === count($punchSpaces)) {
                Log::debug('no punch spaces');
                $punchSpaces[] = $requestedSpace;
            }
        }

        // Enforce max occupancy
        $overageSpaces = [];
        foreach ($punchSpaces as $space) {
            if ($space->active_visit_count + 1 > $space->max_occupancy) {
                $overageSpaces[] = $space->name;
            } elseif (null !== $space->parent_id &&
                $space->parent->active_child_visit_count + 1 > $space->parent->max_occupancy) {
                $overageSpaces[] = $space->parent->name;
            }
        }
        if (count($overageSpaces) > 0) {
            $msg = 'Maximum occupancy reached: '.implode(', ', $overageSpaces);
            Log::info('Rejected punch in by '.$gtid.' at '.$door.' because '.$msg);

            return response()->json(['status' => 'error', 'error' => $msg], 422);
        }

        // Create new visit and punch in
        $spaceIds = array_map(static function (Space $punchSpaces): int {
            return $punchSpaces->id;
        }, $punchSpaces);
        // Populate space names in message to return to frontend if transitioning between spaces
        if (is_array($transition)) {
            $transition['to'] = array_map(static function (Space $punchSpaces): string {
                return $punchSpaces->name;
            }, $punchSpaces);
            $from_list = implode(", ", $transition['from']);
            $to_list = implode(", ", $transition['to']);
            $msg = 'Remember to punch out when you leave a space!';
            $msg .= ' Visit transitioned from '.$from_list.' to '.$to_list.'.';
        } else {
            $msg = null;
        }
        $visit = new Visit();
        $visit->in_time = Carbon::now();
        $visit->in_door = $door;
        $visit->gtid = $gtid;
        $visit->save();
        $visit->spaces()->attach($spaceIds);

        $implodedSpaceIds = implode(',', $spaceIds);
        Log::info('Punch in by '.$gtid.' at '.$door.' for space(s) '.$implodedSpaceIds);

        //Notify all kiosks via websockets
        event(new Punch());

        SendSignOutReminderEmail::dispatch($user, $visit)->delay(now()->addHours(8));

        return response()->json([
            'status' => 'success',
            'punch' => 'in',
            'name' => $name,
            'message' => $msg
        ], 201);
    }
}
