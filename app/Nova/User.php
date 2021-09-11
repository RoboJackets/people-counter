<?php

declare(strict_types=1);

namespace App\Nova;

use App\Nova\Fields\Hidden;
use Illuminate\Database\Query\JoinClause;
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
    public static $model = \App\Models\User::class;

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
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = [
        'spaces',
        'visits',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
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

            Text::make('Primary Affiliation', 'primary_affiliation')
                ->onlyOnDetail()
                ->readonly()
                ->resolveUsing(static function (?string $affiliation): ?string {
                    return null === $affiliation || 'member' === $affiliation ? null : ucfirst($affiliation);
                }),

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

    public function authorizedToUpdateForSerialization(NovaRequest $request): bool
    {
        return $request->user()->can('manage-users') || $request->user()->isSuperAdmin();
    }

    private const SCC_MAIN = 'SCC - Main';
    private const MANAGER = ' Manager';

    /**
     * Get the search result subtitle for the resource.
     */
    public function subtitle(): ?string
    {
        if (in_array($this->primary_affiliation, ['faculty', 'staff', 'employee'], true)) {
            return ucfirst($this->primary_affiliation);
        }

        if ($this->visits()->count() > 0) {
            $visits_with_space_names = $this->visits()->selectRaw(
                'spaces.name, spaces.id, count(visits.id) as count_of_visits'
            )->leftJoin(
                'space_visit',
                'visits.id',
                '=',
                'space_visit.visit_id'
            )->leftJoin(
                'spaces',
                static function (JoinClause $join): void {
                    $join->on('spaces.id', '=', 'space_visit.space_id')
                         ->where('spaces.name', '!=', self::SCC_MAIN);
                }
            )->groupBy(
                'spaces.id'
            )->orderByDesc(
                'count_of_visits'
            )->get()->toArray();

            foreach ($visits_with_space_names as $visit_count) {
                if (null === $visit_count['name']) {
                    continue;
                }

                if (1 === $this->managedSpaces()->where('spaces.id', '=', $visit_count['id'])->count()) {
                    return $visit_count['name'].self::MANAGER;
                }

                return $visit_count['name'];
            }
        }

        if (0 < $this->spaces()->where('spaces.name', '!=', self::SCC_MAIN)->count()) {
            $space = $this->spaces()->where('spaces.name', '!=', self::SCC_MAIN)->first();

            if (1 === $this->managedSpaces()->where('spaces.id', '=', $space->id)->count()) {
                return $space->name.self::MANAGER;
            }

            return $space->name;
        }

        return null;
    }
}
