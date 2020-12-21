<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Space;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Auth\Access\HandlesAuthorization;

class VisitPolicy
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
        return $user->can('read-visits');
    }

    public function view(User $user, Visit $visit): bool
    {
        if ($user->can('read-visits')) {
            return true;
        }

        return $user->can('read-visits-own') && $user->id === $visit->user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('create-visits');
    }

    public function update(User $user, Visit $visit): bool
    {
        if ($user->can('update-visits')) {
            return true;
        }

        return $user->can('update-visits-own') && $user->id === $visit->user->id;
    }

    public function delete(User $user, Visit $visit): bool
    {
        if ($user->can('delete-visits')) {
            return true;
        }

        return $user->can('delete-visits-own') && $user->id === $visit->user->id;
    }

    public function attachAnySpace(User $user, Visit $visit): bool
    {
        return $user->can('update-visits');
    }

    public function detachAnySpace(User $user, Visit $visit): bool
    {
        return $user->can('update-visits');
    }

    public function attachAnyUser(User $user, Visit $visit): bool
    {
        return $user->can('update-visits');
    }

    public function detachAnyUser(User $user, Visit $visit): bool
    {
        return $user->can('update-visits');
    }

    public function detachSpace(User $user, Visit $visit, Space $space): bool
    {
        return $this->detachAnySpace($user, $visit);
    }
}
