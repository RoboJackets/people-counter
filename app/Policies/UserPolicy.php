<?php

declare(strict_types=1);

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Override for Super Admin to authorize all actions automatically
     *
     * @return bool|null
     */
    public function before(?\App\User $user)
    {
        if ($user instanceof User) {
            return $user->isSuperAdmin() ? true : null;
        }

        return null;
    }

    /**
     * Determine whether the user can view any users.
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
     * @return bool
     */
    public function view(User $requesting_user, User $target_user)
    {
        if ($requesting_user->can('manage-users')) {
            return true;
        }

        return $requesting_user->id === $target_user->id;
    }

    /**
     * Determine whether the user can create users.
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
     * @return bool
     */
    public function update(User $requesting_user, User $target_user)
    {
        if ($requesting_user->can('manage-users')) {
            return true;
        }

        return $requesting_user->id === $target_user->id;
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @return bool
     */
    public function delete(User $requesting_user)
    {
        return $requesting_user->can('manage-users');
    }
}
