<?php

declare(strict_types=1);

namespace App\Nova;

use App\Nova\Actions\ActivateKiosk;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Saumini\Count\RelationshipCount;

class Space extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Space::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'name',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255')
                ->creationRules('unique:spaces,name')
                ->updateRules('unique:spaces,name,{{resourceId}}'),

            Number::make('Max Occupancy')
                ->sortable()
                ->rules('required')
                ->min(1)
                ->max(100),

            BelongsToMany::make('Users'),

            RelationshipCount::make('User Count', 'users')->sortable(),

            BelongsToMany::make('Visits'),

            RelationshipCount::make('Visit Count', 'visits')->sortable(),

            BelongsTo::make('Parent Space', 'parent', self::class)
                ->nullable()
                ->showCreateRelationButton(),

            HasMany::make('Child Spaces', 'children', self::class)->nullable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(Request $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Filters\Filter>
     */
    public function filters(Request $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Lenses\Lens>
     */
    public function lenses(Request $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(Request $request): array
    {
        return [
            (new ActivateKiosk())
                ->confirmText('Are you sure you want to activate this browser as a kiosk?')
                ->confirmButtonText('Activate')
                ->cancelButtonText("Don't activate")
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->hasRole('super-admin');
                })
                ->canRun(static function (Request $request): bool {
                    return $request->user()->hasRole('super-admin');
                }),
        ];
    }

    public function authorizedToUpdateForSerialization(NovaRequest $request): bool
    {
        return $request->user()->can('manage-spaces');
    }
}
