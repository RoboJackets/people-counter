<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use Ampeco\Filters\DateRangeFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VisitDate extends DateRangeFilter
{
    /**
     * The displayable name of the filter.
     *
     * @var string
     */
    public $name = 'Visit Date';

    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array<string> $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value): Builder
    {
        $from = Carbon::parse($value[0])->startOfDay();
        $to = Carbon::parse($value[1])->endOfDay();

        return $query->whereBetween('in_time', [$from, $to]);
    }

    /**
     * Options for the filter (not actually used AFAIK)
     *
     * @return array<string>
     */
    public function options(Request $request): array
    {
        return [];
    }
}
