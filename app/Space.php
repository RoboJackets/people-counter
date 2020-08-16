<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
}
