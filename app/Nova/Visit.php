<?php

declare(strict_types=1);

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Visit extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Visit::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'id',
    ];

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(Request $request)
    {
        return [
            BelongsTo::make('User')->searchable(),
            BelongsToMany::make('Spaces')->searchable(),
            DateTime::make('In Time'),
            Text::make('In Door'),
            DateTime::make('Out Time'),
            Text::make('Out Door'),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<\Laravel\Nova\Filters\Filter>
     */
    public function filters(Request $request)
    {
        return [
            new Filters\VisitState(),
            new Filters\VisitDate(),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<\Laravel\Nova\Lenses\Lens>
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(Request $request)
    {
        return [
            (new Actions\ExportVisits())
                ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
                ->withHeadings()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-visits');
                })
                ->canRun(static function (Request $request): bool {
                    return $request->user()->can('read-visits');
                }),
            (new Actions\EndVisit())
                ->canSee(static function (Request $request): bool {
                    return $request->user()->hasRole('super-admin');
                })
                ->canRun(static function (Request $request): bool {
                    return $request->user()->hasRole('super-admin');
                }),
            (new Actions\SendExposureNotification())
                ->standalone()
                ->onlyOnIndex()
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
        return $request->user()->can('update-visits') || $request->user()->isSuperAdmin();
    }
}
