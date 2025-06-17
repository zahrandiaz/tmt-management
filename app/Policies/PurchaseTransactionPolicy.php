<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Karung\Models\PurchaseTransaction;
use Illuminate\Auth\Access\Response;

class PurchaseTransactionPolicy
{
    // ... (method viewAny, view, create, update, delete, cancel tidak berubah) ...

    public function viewAny(User $user): bool
    {
        return $user->can('karung.view_purchases');
    }

    public function view(User $user, PurchaseTransaction $purchaseTransaction): bool
    {
        return $user->can('karung.view_purchases');
    }

    public function create(User $user): bool
    {
        return $user->can('karung.create_purchases');
    }

    public function update(User $user, PurchaseTransaction $purchaseTransaction): bool
    {
        return $user->can('karung.edit_purchases') && $purchaseTransaction->status === 'Completed';
    }

    public function delete(User $user, PurchaseTransaction $purchaseTransaction): bool
    {
        return $user->can('karung.delete_purchases') && $purchaseTransaction->status === 'Completed';
    }

    public function cancel(User $user, PurchaseTransaction $purchaseTransaction): bool
    {
        return $user->can('karung.cancel_purchases') && $purchaseTransaction->status === 'Completed';
    }

    /**
     * [BARU] Determine whether the user can restore the model.
     * Hanya Super Admin TMT yang bisa mengembalikan data dari 'sampah'.
     */
    public function restore(User $user, PurchaseTransaction $purchaseTransaction): bool
    {
        return $user->hasRole('Super Admin TMT');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PurchaseTransaction $purchaseTransaction): bool
    {
        return false;
    }
}