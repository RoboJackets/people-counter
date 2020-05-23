<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    /**
     * Attributes that are not mass assignable
     */
    protected $guarded = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];


    /**
     * Get the user that owns the visit.
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'gtid', 'gtid');
    }
}
