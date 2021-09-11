<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Visit extends Model
{
    use SoftDeletes;
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
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected $casts = [
        'in_time' => 'datetime',
        'out_time' => 'datetime',
    ];

    /**
     * The rules to use for ranking results in Meilisearch.
     *
     * @var array<string>
     */
    public $ranking_rules = [
        'desc(updated_at_unix)',
    ];

    /**
     * The attributes that can be used for filtering in Meilisearch.
     *
     * @var array<string>
     */
    public $filterable_attributes = [
        'users_id',
        'spaces_id',
    ];

    /**
     * Get the user that owns the visit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gtid', 'gtid');
    }

    /**
     * Define the relationship between Visit and Space.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function spaces(): BelongsToMany
    {
        return $this->belongsToMany(Space::class);
    }

    /**
     * Active Visits (In, but no Out).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('in_time')->whereNull('out_time');
    }

    /**
     * Inactive Visits (In and Out).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->whereNotNull('in_time')->whereNotNull('out_time');
    }

    /**
     * Active Visits for a given user via GTID.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $gtid
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActiveForUser(Builder $query, int $gtid): Builder
    {
        return $query->whereNotNull('in_time')->whereNull('out_time')->where('gtid', $gtid);
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('user')->with('spaces');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string,int|string>
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        if (! array_key_exists('user', $array)) {
            $array['user'] = $this->user()->first()->toSearchableArray();
        }

        if (! array_key_exists('spaces', $array)) {
            $array['spaces'] = $this->spaces()->get();
        }

        $array['users_id'] = $this->user()->first()->id;

        $array['spaces_id'] = $this->spaces()->get()->modelKeys();

        $array['updated_at_unix'] = $this->updated_at->getTimestamp();

        return $array;
    }
}
