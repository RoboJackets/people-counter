<?php

declare(strict_types=1);

namespace App\Nova;

use App\Nova\Fields\Hidden;
use Illuminate\Http\Request;
use Jeffbeltran\SanctumTokens\SanctumTokens;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\User::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'full_name';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'username', 'first_name', 'last_name', 'email', 'gtid',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('Username')
                ->sortable()
                ->rules('required', 'max:255')
                ->creationRules('unique:users,username')
                ->updateRules('unique:users,username,{{resourceId}}'),

            Text::make('First Name', 'first_name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Last Name', 'last_name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Hidden::make('GTID')
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-users-gtid');
                }),

            // Hidden fields can't be edited, so add this field on the forms so it can be edited for service accounts
            Text::make('GTID')
                ->onlyOnForms()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-users-gtid');
                })
                ->rules('required', 'integer', 'min:900000000', 'max:999999999')
                ->creationRules('unique:users,gtid')
                ->updateRules('unique:users,gtid,{{resourceId}}'),

            BelongsToMany::make('Spaces')
                ->fields(static function (): array {
                    return [
                        Boolean::make('Manager'),
                    ];
                }),

            HasMany::make('Visits'),

            MorphToMany::make('Roles', 'roles', \Vyuldashev\NovaPermission\Role::class)
                ->canSee(static function (Request $request): bool {
                    return $request->user()->hasRole('super-admin');
                }),

            MorphToMany::make('Permissions', 'permissions', \Vyuldashev\NovaPermission\Permission::class)
                ->canSee(static function (Request $request): bool {
                    return $request->user()->hasRole('super-admin');
                }),

            SanctumTokens::make()->canSee(static function (Request $request): bool {
                return $request->user()->hasRole('super-admin');
            }),
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
        return [];
    }

    public function authorizedToUpdateForSerialization(NovaRequest $request): bool
    {
        return $request->user()->can('manage-users');
    }
}
