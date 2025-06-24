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
    // [MODIFIKASI] Ganti tipe dari SalesReturn ke PurchaseReturn
    public function view(User $user, PurchaseReturn $purchaseReturn): bool
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
    // [MODIFIKASI] Ganti tipe dari SalesReturn ke PurchaseReturn
    public function update(User $user, PurchaseReturn $purchaseReturn): bool
    {
        // Untuk saat ini kita belum implementasi update, tapi kita perbaiki untuk masa depan
        return $user->can('karung.manage_returns');
    }

    /**
     * Determine whether the user can delete the model.
     */
    // [MODIFIKASI] Ganti tipe dari SalesReturn ke PurchaseReturn
    public function delete(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->can('karung.manage_returns');
    }

    /**
     * Determine whether the user can restore the model.
     */
    // [MODIFIKASI] Ganti tipe dari SalesReturn ke PurchaseReturn
    public function restore(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->can('karung.manage_returns');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    // [MODIFIKASI] Ganti tipe dari SalesReturn ke PurchaseReturn
    public function forceDelete(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->can('karung.manage_returns');
    }
}