<?php

namespace App\Policies;

use App\Models\ManagedApplication;
use App\Models\User;

class ManagedApplicationPolicy
{
    /**
     * Determine whether the user can view any managed applications.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the managed application.
     */
    public function view(User $user, ManagedApplication $managedApplication): bool
    {
        return $user->id === $managedApplication->user_id;
    }

    /**
     * Determine whether the user can create managed applications.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the managed application.
     */
    public function update(User $user, ManagedApplication $managedApplication): bool
    {
        return $user->id === $managedApplication->user_id;
    }

    /**
     * Determine whether the user can delete the managed application.
     */
    public function delete(User $user, ManagedApplication $managedApplication): bool
    {
        return $user->id === $managedApplication->user_id;
    }

    /**
     * Determine whether the user can restore the managed application.
     */
    public function restore(User $user, ManagedApplication $managedApplication): bool
    {
        return $user->id === $managedApplication->user_id;
    }

    /**
     * Determine whether the user can permanently delete the managed application.
     */
    public function forceDelete(User $user, ManagedApplication $managedApplication): bool
    {
        return $user->id === $managedApplication->user_id;
    }

    /**
     * Determine whether the user can connect to the managed application.
     */
    public function connect(User $user, ManagedApplication $managedApplication): bool
    {
        return $user->id === $managedApplication->user_id 
            && !$managedApplication->isRevoked();
    }

    /**
     * Determine whether the user can disconnect the managed application.
     */
    public function disconnect(User $user, ManagedApplication $managedApplication): bool
    {
        return $user->id === $managedApplication->user_id;
    }

    /**
     * Determine whether the user can revoke the managed application.
     */
    public function revoke(User $user, ManagedApplication $managedApplication): bool
    {
        return $user->id === $managedApplication->user_id;
    }
}
