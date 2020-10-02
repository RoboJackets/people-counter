<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

class VisitState extends BooleanFilter
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Visit State';

    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Illuminate\Database\Eloquent\Builder  $query
     * @param array $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return true === $value['active'] ? $query->active() : $query->inactive();
    }

    /**
     * Get the filter's available options.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string,string>
     */
    public function options(Request $request): array
    {
        return [
            'Active' => 'active',
            'Inactive' => 'inactive',
        ];
    }
}
