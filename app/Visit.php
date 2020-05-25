<?php

namespace App;

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
     * @var array
     */
    protected $dates = [
        'in_time',
        'out_time'
    ];

    /**
     * Get the user that owns the visit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\User', 'gtid', 'gtid');
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('in_time')->whereNull('out_time');
    }

    /**
     * @param $query
     * @param int $gtid
     *
     * @return mixed
     */
    public function scopeActiveForUser($query, $gtid)
    {
        return $query->whereNotNull('in_time')->whereNull('out_time')->where('gtid', $gtid);
    }
}
