<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVisitPunch;
use App\User;
use App\Visit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class VisitPunchController extends Controller
{
    /**
     * Record a new in/out punch for a Visit
     *
     * @param StoreVisitPunch $request
     *
     * @return JsonResponse
     */
    public function store(StoreVisitPunch $request)
    {
        $gtid = $request->input('gtid');
        $door = $request->input('door');

        // Fetch user to include name in response if we've seen them before
        $user = User::where('gtid', $gtid)->first();
        if (null === $user) {
            $name = "";
        } else {
            $name = $user->first_name . " " . $user->last_name;
        }

        // Find active visit for GTID (if any)
        $active_visits = Visit::activeForUser($gtid)->get();

        if (count($active_visits) > 1) {
            // Eek! User has multiple active visits. This shouldn't happen!
            return response()->json(['status' => 'error', 'error' => 'Multiple active visits should never happen'], 500);
        } elseif (count($active_visits) == 1) {
            // Update existing visit to punch out
            $visit = $active_visits->first();
            $visit->out_time = Carbon::now();;
            $visit->out_door = $door;
            $visit->save();

            return response()->json(['status' => 'success', 'punch' => 'out', 'name' => $name, 'visit' => $visit]);
        } else {
            // Create new visit and punch in
            $visit = new Visit();
            $visit->in_time = Carbon::now();
            $visit->in_door = $door;
            $visit->gtid = $gtid;
            $visit->save();

            return response()->json(['status' => 'success', 'punch' => 'in', 'name' => $name, 'visit' => $visit], 201);
        }
    }
}
