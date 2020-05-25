<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVisit;
use App\Http\Requests\UpdateVisit;
use App\Http\Resources\Visit as VisitResource;
use App\Visit;
use Illuminate\Routing\Controller;

class VisitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection<VisitResource>
     */
    public function index()
    {
        return VisitResource::collection(Visit::all());
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
}
