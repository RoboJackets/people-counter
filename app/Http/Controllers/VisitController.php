<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVisit;
use App\Http\Requests\UpdateVisit;
use App\Http\Resources\Visit as VisitResource;
use App\Visit;
use Illuminate\Routing\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class VisitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection<VisitResource>
     */
    public function index()
    {
        $visits = QueryBuilder::for(Visit::class)
            ->allowedFilters(
                [
                    'in_time', 'in_door', 'out_time', 'out_door',
                    AllowedFilter::exact('gtid'),
                    AllowedFilter::exact('id'),
                    AllowedFilter::scope('active'),
                    AllowedFilter::scope('active_for_user')
                ]
            )
            ->allowedSorts('in_time', 'out_time', 'in_door', 'out_door')
            ->allowedIncludes(['user'])
            ->get();

        return VisitResource::collection($visits);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Resources\Visit
     */
    public function store(StoreVisit $request)
    {
        $visit = Visit::create($request->all());
        return new VisitResource($visit);
    }

    /**
     * Display the specified resource.
     *
     * @return \App\Http\Resources\Visit
     */
    public function show(Visit $visit)
    {
        $visit = QueryBuilder::for(Visit::class)
            ->where('id', $visit->id)
            ->allowedIncludes(['user'])
            ->first();
        return new VisitResource($visit);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \App\Http\Resources\Visit|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateVisit $request, Visit $visit)
    {
        try {
            $visit->update($request->all());
            return new VisitResource($visit);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Visit $visit)
    {
        try {
            $visit->delete();
            return response()->json('success');
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    /**
     * Return useful counters for visits
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function count()
    {
        $hereCount = Visit::active()->count();
        return response()->json([
            'here' => $hereCount,
            'max' => env('MAX_PEOPLE'),
        ]);
    }
}
