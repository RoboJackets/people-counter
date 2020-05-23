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
     * @param \App\User|null $user
     * @param string $ability
     *
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
     * @param  \App\User  $user
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->can('manage-users');
    }

    /**
     * Determine whether the user can view the user.
     *
     * @param  \App\User|null $requesting_user
     * @param  \App\User  $target_user
     *
     * @return bool
     */
    public function view(?User $requesting_user, User $target_user)
    {
        if ($requesting_user->can('manage-users')) {
            return true;
        }

        return $requesting_user->id == $target_user->user_id;
    }

    /**
     * Determine whether the user can create users.
     *
     * @param  \App\User  $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->can('manage-users');
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param  \App\User  $requesting_user
     * @param  \App\User  $target_user
     *
     * @return bool
     */
    public function update(User $requesting_user, User $target_user)
    {
        if ($requesting_user->can('manage-users')) {
            return true;
        }

        return $requesting_user->id == $target_user->user_id;
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\User  $requesting_user
     * @param  \App\User  $target_user
     *
     * @return bool
     */
    public function delete(User $requesting_user, User $target_user)
    {
        return $requesting_user->can('manage-users');
    }
    
}
