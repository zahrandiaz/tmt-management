<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Karung\Models\PurchaseReturn;

class PurchaseReturnPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('karung.manage_returns');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SalesReturn $salesReturn): bool
    {
        return $user->can('karung.manage_returns');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('karung.manage_returns');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SalesReturn $salesReturn): bool
    {
        return $user->can('karung.manage_returns');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SalesReturn $salesReturn): bool
    {
        return $user->can('karung.manage_returns');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SalesReturn $salesReturn): bool
    {
        return $user->can('karung.manage_returns');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SalesReturn $salesReturn): bool
    {
        return $user->can('karung.manage_returns');
    }
}