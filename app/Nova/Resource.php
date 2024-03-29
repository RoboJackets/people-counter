<?php

declare(strict_types=1);

namespace App\Nova;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource as NovaResource;
use Laravel\Scout\Builder;

abstract class Resource extends NovaResource
{
    /**
     * Build a Scout search query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Scout\Builder  $query
     * @return \Laravel\Scout\Builder
     */
    public static function scoutQuery(NovaRequest $request, $query): Builder
    {
        if (null !== $request->viaResource) {
            return $query->where($request->viaResource.'_id', $request->viaResourceId);
        }

        return $query;
    }
}
