<?php

namespace App\Http\Controllers;

use App\Http\Resources\Space as SpaceResource;
use App\Space;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SpaceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection<SpaceResource>
     */
    public function index()
    {
        $spaces = QueryBuilder::for(Space::class)
            ->allowedFilters(
                [
                    AllowedFilter::exact('id'),
                    'name',
                    'max_occupancy'
                ]
            )
            ->allowedSorts('name')
            ->allowedIncludes(['parent', 'children'])
            ->get();

        return SpaceResource::collection($spaces);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Space  $space
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Space $space)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Space  $space
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Space $space)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Space  $space
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Space $space)
    {
        //
    }
}
