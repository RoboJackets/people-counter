<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * Class Space.
 *
 * @property int $id
 * @property int $visits_count
 * @property int $parent_id
 * @property string $name
 *
 * @property-read \Illuminate\Database\Eloquent\Collection $children
 */
class Space extends Model
{
    use SoftDeletes;
    use HasRelationships;
    use Searchable;

    /**
     * Attributes that are not mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Allowed relationships to be dynamically included via request parameter.
     *
     * @var      array<string>
     * @suppress PhanReadOnlyPublicProperty
     */
    public static $allowedIncludes = [
        'parent', 'children', 'users', 'visits', 'activeVisitsUsers', 'activeChildVisitsUsers',
    ];

    /**
     * The attributes that should be searchable in Meilisearch.
     *
     * @var array<string>
     */
    public $searchable_attributes = [
        'name',
    ];

    /**
     * The attributes that can be used for filtering in Meilisearch.
     *
     * @var array<string>
     */
    public $filterable_attributes = [
        'users_id',
        'visits_id',
        'spaces_id',
    ];

    /**
     * The rules to use for ranking results in Meilisearch.
     *
     * @var array<string>
     */
    public $ranking_rules = [
        'desc(visits_count)',
    ];

    /**
     * Get the count of active visits in this space.
     *
     * @return int
     */
    public function getActiveVisitCountAttribute(): int
    {
        return $this->visits()->active()->count();
    }

    /**
     * Get the count of active visits of child spaces of this space.
     *
     * @return int
     */
    public function getActiveChildVisitCountAttribute(): int
    {
        return $this->children->sum('active_visit_count');
    }

    /**
     * Define relationship between Space and its parent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Define relationship between Space and its children.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Define the relationship between Space and User
     * This is for default attachment of spaces visits, not for users with a visit in a space.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('manager');
    }

    /**
     * Defines a space manager relationship via the space_user pivot table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->wherePivot('manager', 1);
    }

    /**
     * Define the relationship between Space and Visit.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function visits(): BelongsToMany
    {
        return $this->belongsToMany(Visit::class);
    }

    /**
     * Define the relationship between Space and (active) Visit.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function activeVisits(): BelongsToMany
    {
        return $this->belongsToMany(Visit::class)
            ->whereNotNull('in_time')->whereNull('out_time');
    }

    /**
     * Voodoo magic to get all users who have ever visited a given space.
     *
     * @return HasManyDeep
     */
    public function visitsUsers(): HasManyDeep
    {
        return $this->hasManyDeep(
            User::class,
            ['space_visit', Visit::class],
            ['space_id', 'id', 'gtid'],
            ['id', 'visit_id', 'gtid']
        );
    }

    /**
     * Voodoo magic to get users of active visits
     * Must manually specify the constraints here because of a feature/bug/whatever in the voodoo package
     * https://github.com/staudenmeir/eloquent-has-many-deep/issues/36.
     *
     * @return HasManyDeep
     */
    public function activeVisitsUsers(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->visits(), (new Visit())->user())
            ->whereNotNull('visits.in_time')->whereNull('visits.out_time');
    }

    /**
     * Voodoo magic to get visits of child spaces.
     *
     * @return HasManyDeep
     */
    public function childVisits(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->children(), (new self())->visits());
    }

    /**
     * Voodoo magic to get active visits of child spaces
     * Must manually specify the constraints here because of a feature/bug/whatever in the voodoo package
     * https://github.com/staudenmeir/eloquent-has-many-deep/issues/36.
     *
     * @return HasManyDeep
     */
    public function activeChildVisits(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->children(), (new self())->visits())
            ->whereNotNull('in_time')->whereNull('out_time');
    }

    /**
     * Voodoo magic to get users of visits of child spaces.
     *
     * @return HasManyDeep
     */
    public function childVisitsUsers(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->childVisits(), (new Visit())->user());
    }

    /**
     * Voodoo magic to get users of active visits of child spaces
     * Must manually specify the constraints here because of a feature/bug/whatever in the voodoo package
     * https://github.com/staudenmeir/eloquent-has-many-deep/issues/36.
     *
     * @return HasManyDeep
     */
    public function activeChildVisitsUsers(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->activeChildVisits(), (new Visit())->user())
            ->whereNotNull('visits.in_time')->whereNull('visits.out_time');
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->withCount('visits');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string,int|string>
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        if (! array_key_exists('visits_count', $array)) {
            $array['visits_count'] = $this->visits()->count();
        }

        $array['users_id'] = $this->users()->get()->modelKeys();

        $array['visits_id'] = $this->visits()->get()->modelKeys();

        $array['spaces_id'] = $this->parent_id;

        return $array;
    }
}
