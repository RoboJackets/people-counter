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
     * Get the user that owns the visit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\User', 'gtid', 'gtid');
    }
}
