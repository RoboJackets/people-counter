<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Space extends Model
{
    use SoftDeletes;

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
     * Get the count of active visits in this space
     *
     * @return int
     */
    public function getActivevisitcountAttribute(): int
    {
        return $this->visits()->active()->count();
    }

    /**
     * Define relationship between Space and its parent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo('App\Space', 'parent_id');
    }

    /**
     * Define relationship between Space and its children
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany('App\Space', 'parent_id');
    }

    /**
     * Define the relationship between Space and User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\User');
    }

    /**
     * Define the relationship between Space and Visit
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visits(): HasMany
    {
        return $this->hasMany('App\Visit');
    }
}
