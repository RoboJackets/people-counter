<?php

declare(strict_types=1);

namespace App\Policies;

use App\Space;
use App\User;
use App\Visit;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class VisitPolicy
{
    use HandlesAuthorization;

    /**
     * Override for Super Admin to authorize all actions automatically.
     *
     * @return bool|null
     */
    public function before(?User $user)
    {
        if ($user instanceof User) {
            return $user->isSuperAdmin() ? true : null;
        }

        return null;
    }

    /**
     * Determine whether the user can view any visits.
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->can('read-visits');
    }

    /**
     * Determine whether the user can view the visit.
     *
     * @return bool
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
     * @return bool
     */
    public function create(User $user)
    {
        return $user->can('create-visits');
    }

    /**
     * Determine whether the user can update the visit.
     *
     * @return bool
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
     * @return bool
     */
    public function delete(User $user, Visit $visit)
    {
        if ($user->can('delete-visits')) {
            return true;
        }

        return $user->can('delete-visits-own') && $user->id === $visit->user->id;
    }

    /**
     * Determine whether the user can detach a space to a visit.
     * @param \App\User $user
     * @param \App\Visit $visit
     *
     * @return bool
     */
    public function attachAnySpace(User $user, Visit $visit)
    {
        return $user->can('update-visits');
    }

    /**
     * Determine whether the user can detach a space to a visit.
     * @param \App\User $user
     * @param \App\Visit $visit
     *
     * @return bool
     */
    public function detachAnySpace(User $user, Visit $visit)
    {
        return $user->can('update-visits');
    }

    /**
     * Determine whether the user can detach a user to a visit.
     * @param \App\User $user
     * @param \App\Visit $visit
     *
     * @return bool
     */
    public function attachAntUser(User $user, Visit $visit)
    {
        return $user->can('update-visits');
    }

    /**
     * Determine whether the user can detach a user to a visit.
     * @param \App\User $user
     * @param \App\Visit $visit
     *
     * @return bool
     */
    public function detachAnyUser(User $user, Visit $visit)
    {
        return $user->can('update-visits');
    }
}
