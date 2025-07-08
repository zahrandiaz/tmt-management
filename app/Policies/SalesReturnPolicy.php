<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model; // <-- UBAH INI

class SalesReturnPolicy
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
    // Ganti 'SalesReturn' menjadi 'Model'
    public function view(User $user, Model $salesReturn): bool
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
    // Ganti 'SalesReturn' menjadi 'Model'
    public function update(User $user, Model $salesReturn): bool
    {
        return $user->can('karung.manage_returns');
    }

    /**
     * Determine whether the user can delete the model.
     */
    // Ganti 'SalesReturn' menjadi 'Model'
    public function delete(User $user, Model $salesReturn): bool
    {
        return $user->can('karung.manage_returns');
    }

    /**
     * Determine whether the user can restore the model.
     */
    // Ganti 'SalesReturn' menjadi 'Model'
    public function restore(User $user, Model $salesReturn): bool
    {
        return $user->can('karung.manage_returns');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    // Ganti 'SalesReturn' menjadi 'Model'
    public function forceDelete(User $user, Model $salesReturn): bool
    {
        return $user->can('karung.manage_returns');
    }
}