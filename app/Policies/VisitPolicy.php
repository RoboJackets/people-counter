<?php

namespace App\Policies;

use App\User;
use App\Visit;
use Illuminate\Auth\Access\HandlesAuthorization;

class VisitPolicy
{
    use HandlesAuthorization;

    /**
     * Override for Super Admin to authorize all actions automatically
     *
     * @param  $user \App\User|null $user
     * @param  $ability string
     * @return bool
     */
    public function before($user, $ability)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any visits.
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->can('read-visits');
    }

    /**
     * Determine whether the user can view the visit.
     *
     * @return mixed
     */
    public function view(User $user, Visit $visit)
    {
        if ($user->can('read-visits')) {
            return true;
        }

        return $user->can('read-visits-own') && $user->id === $visit->user->id;
    }

    /**
     * Determine whether the user can create visits.
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('create-visits');
    }

    /**
     * Determine whether the user can update the visit.
     *
     * @return mixed
     */
    public function update(User $user, Visit $visit)
    {
        if ($user->can('update-visits')) {
            return true;
        }

        return $user->can('update-visits-own') && $user->id === $visit->user->id;
    }

    /**
     * Determine whether the user can delete the visit.
     *
     * @return mixed
     */
    public function delete(User $user, Visit $visit)
    {
        if ($user->can('delete-visits')) {
            return true;
        }

        return $user->can('delete-visits-own') && $user->id === $visit->user->id;
    }
}
