<?php

declare(strict_types=1);

namespace App\Policies;

use App\Space;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function before(?User $user): ?bool
    {
        if ($user instanceof User) {
            return $user->isSuperAdmin() ? true : null;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('manage-users') || $user->can('read-users');
    }

    public function view(User $requesting_user, User $target_user): bool
    {
        if ($requesting_user->can('manage-users') || $requesting_user->can('read-users')) {
            return true;
        }

        return $requesting_user->id === $target_user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('manage-users');
    }

    public function update(User $requesting_user, User $target_user): bool
    {
        return $requesting_user->can('manage-users');
    }

    public function delete(User $requesting_user): bool
    {
        return $requesting_user->can('manage-users');
    }

    public function attachAnySpace(User $requestingUser, User $targetUser): bool
    {
        return $requestingUser->can('manage-spaces');
    }

    public function detachAnySpace(User $requestingUser, User $targetUser): bool
    {
        return $requestingUser->can('manage-spaces');
    }

    public function detachSpace(User $requestingUser, User $targetUser, Space $space): bool
    {
        return $this->detachAnySpace($requestingUser, $targetUser);
    }
}
