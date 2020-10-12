<?php

declare(strict_types=1);

namespace App\Policies;

use App\Space;
use App\User;
use App\Visit;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class SpacePolicy
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
     * Determine whether the user can view any spaces.
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the user.
     *
     * @return bool
     */
    public function view(User $user, Space $space)
    {
        return $user->can('manage-spaces') || $user->can('read-spaces');
    }

    /**
     * Determine whether the user can create users.
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->can('manage-spaces');
    }

    /**
     * Determine whether the user can update the user.
     *
     * @return bool
     */
    public function update(User $user, Space $space)
    {
        return $user->can('manage-spaces');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @return bool
     */
    public function delete(User $user)
    {
        return $user->can('manage-spaces');
    }

    /**
     * Determine whether the user can detach a visit to a space.
     * @param \App\User $user
     * @param \App\Space $space
     *
     * @return bool
     */
    public function attachAnyVisit(User $user, Space $space)
    {
        return $user->can('update-visits');
    }

    /**
     * Determine whether the user can detach a visit to a space.
     * @param \App\User $user
     * @param \App\Space $space
     *
     * @return bool
     */
    public function detachAnyVisit(User $user, Space $space)
    {
        return $user->can('update-visits');
    }

    /**
     * Determine whether the user can detach a user to a space.
     * @param \App\User $user
     * @param \App\Space $space
     *
     * @return bool
     */
    public function attachAnyUser(User $user, Space $space)
    {
        return $user->can('manage-spaces');
    }

    /**
     * Determine whether the user can detach a user to a space.
     * @param \App\User $user
     * @param \App\Space $space
     *
     * @return bool
     */
    public function detachAnyUser(User $user, Space $space)
    {
        return $user->can('manage-spaces');
    }
}
