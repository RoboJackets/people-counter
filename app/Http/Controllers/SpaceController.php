<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\Space as SpaceResource;
use App\Models\Space;
use Illuminate\Routing\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SpaceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $spaces = QueryBuilder::for(Space::class)
            ->allowedFilters(
                [
                    AllowedFilter::exact('id'),
                    'name',
                    'max_occupancy',
                    AllowedFilter::exact('parent_id'),
                ]
            )
            ->allowedSorts('name')
            ->allowedIncludes(Space::$allowedIncludes)
            ->allowedAppends(['active_visit_count', 'active_child_visit_count'])
            ->get();

        return response()->json(SpaceResource::collection($spaces));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        return response()->json(['status' => 'error', 'error' => 'Not implemented'], 501);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Space  $space
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Space $space)
    {
        $space = QueryBuilder::for(Space::class)
            ->where('id', $space->id)
            ->allowedIncludes(Space::$allowedIncludes)
            ->allowedAppends(['active_visit_count', 'active_child_visit_count'])
            ->first();

        return response()->json(new SpaceResource($space));
    }

    // phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClass
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Space  $space
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Space $space)
    {
        return response()->json(['status' => 'error', 'error' => 'Not implemented'], 501);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Space  $space
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Space $space)
    {
        return response()->json(['status' => 'error', 'error' => 'Not implemented'], 501);
    }
}
