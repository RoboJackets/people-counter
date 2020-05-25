<?php

namespace App\Http\Controllers;

use App\Events\Punch;
use App\Http\Requests\StoreVisitPunch;
use App\Traits\CreateOrUpdateUserFromBuzzAPI;
use App\User;
use App\Visit;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class VisitPunchController extends Controller
{
    use DispatchesJobs, CreateOrUpdateUserFromBuzzAPI;

    /**
     * Record a new in/out punch for a Visit
     *
     * @param StoreVisitPunch $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreVisitPunch $request)
    {
        $gtid = $request->input('gtid');
        $door = $request->input('door');

        // Fetch user to include name in response if we've seen them before
        $user = User::where('gtid', $gtid)->first();

        // Fetch from BuzzAPI if they've not been seen before
        if (!$user) {
            try {
                $user = $this->createOrUpdateUserFromBuzzAPI($gtid);
            } catch (\Exception $e) {
                Log::error('Error querying BuzzAPI to create new user for punch by ' . $gtid,
                    [$e->getMessage()]);
            }
        }

        // Get full name from user model for use later
        // In case BuzzAPI had a problem, still process the punch but use a placeholder name
        $name = (is_null($user)) ? "Unknown" : $user->full_name;

        // Find active visit for GTID (if any)
        $active_visits = Visit::activeForUser($gtid)->get();

        if (count($active_visits) > 1) {
            // Eek! User has multiple active visits. This shouldn't happen!
            Log::error('Multiple active visits found for ' . $gtid);
            return response()->json(
                [
                    'status' => 'error',
                    'error' => 'Multiple active visits should never happen',
                ],
                500
            );
        }

        if (1 === count($active_visits)) {
            // Update existing visit to punch out
            $visit = $active_visits->first();
            $visit->out_time = Carbon::now();
            $visit->out_door = $door;
            $visit->save();

            Log::info('Punch out by ' . $gtid . ' at ' . $door);

            //Notify all kiosks via websockets
            event(new Punch());

            return response()->json(['status' => 'success', 'punch' => 'out', 'name' => $name]);
        }

        // Create new visit and punch in
        $visit = new Visit();
        $visit->in_time = Carbon::now();
        $visit->in_door = $door;
        $visit->gtid = $gtid;
        $visit->save();

        Log::info('Punch in by ' . $gtid . ' at ' . $door);

        //Notify all kiosks via websockets
        event(new Punch());

        return response()->json(['status' => 'success', 'punch' => 'in', 'name' => $name], 201);
    }
}
