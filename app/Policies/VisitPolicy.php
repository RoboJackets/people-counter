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
     * @param $user \App\User|null $user
     * @param $ability string
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
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->can('read-visits');
    }

    /**
     * Determine whether the user can view the visit.
     *
     * @param  \App\User  $user
     * @param  \App\Visit  $visit
     * @return mixed
     */
    public function view(User $user, Visit $visit)
    {
        if ($user->can('read-visits')) {
            return true;
        } elseif ($user->can('read-visits-own') && $user->id == $visit->user->id) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can create visits.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('create-visits');
    }

    /**
     * Determine whether the user can update the visit.
     *
     * @param  \App\User  $user
     * @param  \App\Visit  $visit
     * @return mixed
     */
    public function update(User $user, Visit $visit)
    {
        if ($user->can('update-visits')) {
            return true;
        } elseif ($user->can('update-visits-own') && $user->id == $visit->user->id) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can delete the visit.
     *
     * @param  \App\User  $user
     * @param  \App\Visit  $visit
     * @return mixed
     */
    public function delete(User $user, Visit $visit)
    {
        if ($user->can('delete-visits')) {
            return true;
        } elseif ($user->can('delete-visits-own') && $user->id == $visit->user->id) {
            return true;
        } else {
            return false;
        }
    }
}
