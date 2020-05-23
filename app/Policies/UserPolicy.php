<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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
     * Determine whether the user can view any users.
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->can('manage-users');
    }

    /**
     * Determine whether the user can view the user.
     *
     * @return mixed
     */
    public function view(?User $requesting_user, User $target_user)
    {
        if ($requesting_user->can('manage-users')) {
            return true;
        }

        return $requesting_user->id === $target_user->user_id;
    }

    /**
     * Determine whether the user can create users.
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->can('manage-users');
    }

    /**
     * Determine whether the user can update the user.
     *
     * @return mixed
     */
    public function update(User $requesting_user, User $target_user)
    {
        if ($requesting_user->can('manage-users')) {
            return true;
        }

        return $requesting_user->id === $target_user->user_id;
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @return mixed
     */
    public function delete(User $requesting_user, User $target_user)
    {
        return $requesting_user->can('manage-users');
    }
}
