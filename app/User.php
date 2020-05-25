<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Represents a single User
 *
 * @property int $id Database identifier for this model
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasRoles;
    use Notifiable;

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
     * Attributes that should be hidden for arrays
     *
     * @var array <string>
     */
    protected $hidden = [
        'gtid'
    ];

    /**
     * Get the visits for the user.
     */
    public function visits(): HasMany
    {
        return $this->hasMany('App\Visit');
    }

    /**
     * Returns if the User a super admin
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }
}
