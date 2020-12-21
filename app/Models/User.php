<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Represents a single User.
 *
 * @property int $id
 * @property string $full_name
 * @property int $gtid
 * @property string $email
 * @property string $first_name
 * @property string $last_name
 * @property string $username
 *
 * @property-read \Illuminate\Database\Eloquent\Collection $spaces
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;

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
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = ['full_name'];

    /**
     * Get the visits for the user.
     */
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'gtid', 'gtid');
    }

    /**
     * Define the relationship between User and Space.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function spaces(): BelongsToMany
    {
        return $this->belongsToMany(Space::class)->withPivot('manager');
    }

    /**
     * Defines a space manager relationship via the space_user pivot table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function managedSpaces(): BelongsToMany
    {
        return $this->belongsToMany(Space::class)->wherePivot('manager', 1);
    }

    /**
     * Returns if the User a super admin.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    /**
     * Get full name of User.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }
}