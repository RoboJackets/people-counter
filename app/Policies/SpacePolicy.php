<?php

declare(strict_types=1);

namespace App\Policies;

use App\Space;
use App\User;
use App\Visit;
use Illuminate\Auth\Access\HandlesAuthorization;

class SpacePolicy
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
        return true;
    }

    public function view(User $user, Space $space): bool
    {
        return $user->can('manage-spaces') || $user->can('read-spaces');
    }

    public function create(User $user): bool
    {
        return $user->can('manage-spaces');
    }

    public function update(User $user, Space $space): bool
    {
        return $user->can('manage-spaces');
    }

    public function delete(User $user): bool
    {
        return $user->can('manage-spaces');
    }

    public function attachAnyVisit(User $user, Space $space): bool
    {
        return $user->can('update-visits');
    }

    public function detachAnyVisit(User $user, Space $space): bool
    {
        return $user->can('update-visits');
    }

    public function attachAnyUser(User $user, Space $space): bool
    {
        return $user->can('manage-spaces');
    }

    public function detachAnyUser(User $user, Space $space): bool
    {
        return $user->can('manage-spaces');
    }

    public function detachUser(User $requestingUser, Space $space, User $targetUser): bool
    {
        return $this->detachAnyUser($requestingUser, $space);
    }

    public function detachVisit(User $requestingUser, Space $space, Visit $visit): bool
    {
        return $this->detachAnyVisit($requestingUser, $space);
    }
}
