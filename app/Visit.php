<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    /**
     * Attributes that are not mass assignable
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
    protected $dates = [
        'in_time',
        'out_time',
    ];

    /**
     * Get the user that owns the visit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\User', 'gtid', 'gtid');
    }

    /**
     * Active Visits (In, but no Out)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('in_time')->whereNull('out_time');
    }

    /**
     * Active Visits for a given user via GTID
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $gtid
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActiveForUser(Builder $query, int $gtid): Builder
    {
        return $query->whereNotNull('in_time')->whereNull('out_time')->where('gtid', $gtid);
    }
}
